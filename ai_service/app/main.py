import os
import io
import json
import uuid
import logging
import numpy as np
from PIL import Image
from fastapi import FastAPI, UploadFile, File, Form, Header, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import mysql.connector

# Import the robust pipeline modules
from app.ocr_engine import OCREngine
from app.nlp_parser import NLPParser
from app.risk_predictor import RiskPredictor
from app.config import settings

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("MainAPI")

app = FastAPI(title="Nutri-Planner AI Microservice - 100% Offline RAG")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize singletons for the pipeline
ocr_engine = OCREngine()
nlp_parser = NLPParser()
risk_predictor = RiskPredictor()

# Load Small Local RAG Embedding Model for semantic meal retrieval
try:
    logger.info("Loading Local Embedding Model...")
    embedder = SentenceTransformer('all-MiniLM-L6-v2')
except Exception as e:
    logger.error(f"Failed to load embedding model: {e}")
    embedder = None

def get_db_connection():
    return mysql.connector.connect(
        host=settings.DB_HOST,
        user=settings.DB_USER,
        password=settings.DB_PASS,
        database=settings.DB_NAME
    )

def fetch_recipes_from_db():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("SELECT * FROM recipes")
    recipes = cursor.fetchall()
    
    for r in recipes:
        # Fetch ingredients
        cursor.execute("SELECT * FROM ingredients WHERE recipe_id = %s", (r['id'],))
        r['ingredients'] = cursor.fetchall()
        
        # Fetch nutrition
        cursor.execute("SELECT * FROM nutrition_facts WHERE recipe_id = %s", (r['id'],))
        n = cursor.fetchone()
        r['nutrition'] = n if n else {}
        
        r['tags'] = json.loads(r['tags']) if r['tags'] else []
        
        if embedder:
            text = f"{r['name']} {' '.join(r['tags'])} {r['instructions']}"
            r['embedding'] = embedder.encode([text])[0]
            
    cursor.close()
    conn.close()
    return recipes

class AdvancedMealGenerator:
    @staticmethod
    def calculate_bmr(weight_kg, height_cm, age, gender):
        # Mifflin-St Jeor Equation
        if gender.lower() == 'male':
            return (10 * weight_kg) + (6.25 * height_cm) - (5 * age) + 5
        else:
            return (10 * weight_kg) + (6.25 * height_cm) - (5 * age) - 161
            
    @staticmethod
    def calculate_health_compatibility(recipe, health_risks):
        score = 100
        conditions = [r['condition'].lower() for r in health_risks]
        
        reasons = []
        
        # Example logic for compatibility
        if any("hypertension" in c or "blood pressure" in c for c in conditions):
            if recipe['nutrition'].get('base_sodium', 0) > 400:
                score -= 15
            else:
                reasons.append("Low Sodium")
                
        if any("diabetes" in c for c in conditions):
            if "diabetes-friendly" in recipe['tags'] or "low-sugar" in recipe['tags']:
                reasons.append("Controlled Carbs")
            elif recipe['nutrition'].get('base_carbs', 0) > 60:
                score -= 20
                
        if any("kidney" in c for c in conditions):
            if recipe['nutrition'].get('base_potassium', 0) < 500 and recipe['nutrition'].get('base_protein', 0) < 25:
                reasons.append("Kidney Friendly")
            else:
                score -= 10
                
        if not reasons:
            reasons.append("Balanced Macros")
            
        return max(0, min(100, score)), reasons

    @staticmethod
    def personalize_meal(recipe, bmr, target_calories, health_risks, weight_kg):
        conditions = [r['condition'].lower() for r in health_risks]
        
        # Determine base multiplier based on daily calorie target compared to standard 2000 kcal diet
        # We assume the base recipe is portioned for a 2000 kcal diet where a meal is ~500 kcal
        base_meal_target = target_calories * 0.25  # 25% of daily calories for a main meal
        
        multiplier = base_meal_target / float(recipe['nutrition'].get('base_calories', 500) or 500)
        
        # Keep multiplier within reasonable bounds
        multiplier = max(0.5, min(1.5, multiplier))
        
        disease_notes = []
        instructions = recipe.get("instructions", "")
        
        # 1. Hypertension Modification
        if any("hypertension" in c or "blood pressure" in c for c in conditions):
            instructions = instructions.replace(" salt ", " ")
            instructions = instructions.replace("salt", "")
            disease_notes.append("Preparation modified for Blood Pressure:\nOmitted salt. Season heavily with herbs and spices instead.")
            
        # 2. Diabetes Modification
        if any("diabetes" in c for c in conditions):
            instructions = instructions.replace("sugar", "diabetic-friendly sweetener")
            instructions = instructions.replace("honey", "diabetic-friendly sweetener")
            instructions = instructions.replace("syrup", "diabetic-friendly sweetener")
            disease_notes.append("Preparation modified for Diabetes:\nSugary ingredients replaced with diabetic-friendly alternatives.")
            # Scale down carbs slightly
            multiplier *= 0.9
            
        # 3. Kidney Risk Modification
        kidney_risk = any("kidney" in c for c in conditions)
        if kidney_risk:
            disease_notes.append("Preparation modified for Kidney Health:\nProtein portions adjusted and high-potassium ingredients reduced.")
            
        # Scale Ingredients
        personalized_ingredients = []
        for ing in recipe.get('ingredients', []):
            ing_mult = multiplier
            
            # Disease specific ingredient scaling
            if kidney_risk:
                # Naive heuristic: meats and high potassium foods
                name_low = ing['name'].lower()
                if any(x in name_low for x in ['chicken', 'salmon', 'beef', 'egg', 'banana', 'potato', 'tomato']):
                    ing_mult *= 0.5
            
            if any("diabetes" in c for c in conditions):
                if any(x in ing['name'].lower() for x in ['rice', 'bread', 'pasta', 'potato']):
                    ing_mult *= 0.7
                    
            if any("obesity" in c or "weight loss" in c for c in conditions) or target_calories < bmr:
                if any(x in ing['name'].lower() for x in ['oil', 'butter', 'cheese']):
                    ing_mult *= 0.6
            
            final_qty = round(float(ing['base_quantity']) * ing_mult, 1)
            personalized_ingredients.append({
                "name": ing['name'],
                "quantity": final_qty,
                "unit": ing['unit']
            })
            
        # Recalculate Nutrition based on global multiplier (simplified approximation)
        nutrition = recipe.get('nutrition', {})
        personalized_nutrition = {
            "calories": round(float(nutrition.get('base_calories', 0)) * multiplier),
            "protein": round(float(nutrition.get('base_protein', 0)) * multiplier, 1),
            "carbs": round(float(nutrition.get('base_carbs', 0)) * multiplier, 1),
            "fat": round(float(nutrition.get('base_fat', 0)) * multiplier, 1),
            "fiber": round(float(nutrition.get('base_fiber', 0)) * multiplier, 1),
            "sodium": round(float(nutrition.get('base_sodium', 0)) * multiplier),
            "potassium": round(float(nutrition.get('base_potassium', 0)) * multiplier)
        }
        
        # Extract explicitly listed steps if there are numbers, or just split by punctuation
        raw_steps = instructions.split('.')
        cleaned_steps = []
        for step in raw_steps:
            s = step.replace('\n', '').strip()
            if s:
                cleaned_steps.append(s + ".")
                
        # Calculate Compatibility Score
        score, reasons = AdvancedMealGenerator.calculate_health_compatibility(recipe, health_risks)
        
        return {
            "name": recipe['name'],
            "health_compatibility_score": score,
            "reasons": reasons,
            "personalized_ingredients": personalized_ingredients,
            "nutrition": personalized_nutrition,
            "instructions": cleaned_steps,
            "disease_notes": disease_notes,
            "image_url": recipe.get('image_url')
        }

@app.get("/")
def read_root():
    return {"status": "healthy", "service": "Nutri-Planner AI Microservice - Advanced RAG"}

@app.post("/api/v1/analyze-report")
async def analyze_report(
    file: UploadFile = File(...), 
    target_calories: int = Form(2000), 
    user_id: int = Form(1),
    allergies: str = Form(""),
    diet_type: str = Form("anything"),
    favorites: str = Form(""),
    age: int = Form(30),
    gender: str = Form("male"),
    weight_kg: float = Form(70.0),
    height_cm: float = Form(170.0),
    x_api_key: str = Header(None)
):
    try:
        # Load recipes dynamically from DB per request (or could cache it)
        recipes_db = fetch_recipes_from_db()
        
        # Calculate User BMR
        bmr = AdvancedMealGenerator.calculate_bmr(weight_kg, height_cm, age, gender)
        
        # 1. Save uploaded file temporarily for OCR Engine
        temp_filename = f"temp_{uuid.uuid4().hex}_{file.filename}"
        temp_filepath = os.path.join(settings.UPLOAD_DIR, temp_filename)
        
        with open(temp_filepath, "wb") as f:
            f.write(await file.read())

        # 2. Extract Raw Text using robust OCREngine
        logger.info(f"Extracting text from {temp_filename}...")
        raw_text = ocr_engine.extract_text(temp_filepath)
        
        # Clean up temp file
        if os.path.exists(temp_filepath):
            os.remove(temp_filepath)

        # 3. Parse Clinical Biomarkers using NLPParser
        logger.info("Parsing clinical biomarkers via NLP...")
        parsed_biomarkers = nlp_parser.parse_report_text(raw_text)

        # 4. Predict Health Risks & Generate Rules using RiskPredictor
        logger.info("Analyzing health risks...")
        assessment = risk_predictor.analyze_biomarkers(parsed_biomarkers)
        
        overall_risk_score = assessment["overall_risk_score"]
        health_risks = assessment["health_risks"]
        biomarkers = assessment["biomarkers"]
        dietary_rules = assessment["dietary_rules"]

        if not biomarkers:
            biomarkers['General'] = {'value': 0, 'unit': '-', 'reference_range': '-', 'status': 'NORMAL', 'display_name': 'No Biomarkers Found'}

        # 5. Local Semantic RAG Meal Retrieval
        final_meals = {}
        
        if embedder and recipes_db:
            allergy_keywords = [a.strip().lower() for a in allergies.split(',')] if allergies else []
            avoid_foods = [a.strip().lower() for a in dietary_rules.get("avoid_foods", [])]
            combined_avoid = allergy_keywords + avoid_foods
            
            safe_recipes = []
            for r in recipes_db:
                is_safe = True
                for ak in combined_avoid:
                    if ak and (ak in r['name'].lower() or any(ak in tag.lower() for tag in r['tags'])):
                        is_safe = False
                        break
                if is_safe:
                    safe_recipes.append(r)
                    
            medical_context = [risk["condition"] for risk in health_risks]
            query_str = f"Diet: {diet_type}. Favorites: {favorites}. Medical Focus: {' '.join(medical_context)}"
            query_emb = embedder.encode([query_str])[0]
            
            slots = ['breakfast', 'lunch', 'dinner', 'snack']
            for slot in slots:
                slot_recipes = [r for r in safe_recipes if r['slot'] == slot]
                if slot_recipes:
                    embs = np.array([r['embedding'] for r in slot_recipes])
                    similarities = cosine_similarity([query_emb], embs)[0]
                    best_idx = np.argmax(similarities)
                    best_recipe = slot_recipes[best_idx]
                    
                    # Apply Dynamic Personalization Engine
                    personalized_meal = AdvancedMealGenerator.personalize_meal(
                        best_recipe, bmr, target_calories, health_risks, weight_kg
                    )
                    
                    final_meals[slot] = personalized_meal
                else:
                    final_meals[slot] = {"name": f"Safe {slot.capitalize()}", "health_compatibility_score": 50, "personalized_ingredients": [], "nutrition": {"calories": 300, "protein": 20, "carbs": 30, "fat": 10}, "instructions": "Standard safe meal with no active modifications.", "reasons": []}

        else:
            final_meals = {"error": "Embedder or DB failed."}

        meal_plan = {
            "dietary_summary": [f"Targeting {diet_type} diet", "Personalized via Offline Clinical AI Pipeline", f"Scaled to {target_calories} kcal"],
            "meals": final_meals
        }

        return {
            "status": "success",
            "raw_text": raw_text[:1000] if raw_text else "No text extracted.", 
            "overall_risk_score": overall_risk_score,
            "biomarkers": biomarkers,
            "health_risks": health_risks,
            "meal_plan": meal_plan
        }

    except Exception as e:
        logger.error(f"Error analyzing report: {e}")
        raise HTTPException(status_code=500, detail=str(e))
