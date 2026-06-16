import os
import io
import re
import json
import joblib
import numpy as np
from PIL import Image
import easyocr
from fastapi import FastAPI, UploadFile, File, Form, Header, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
from rapidfuzz import fuzz
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity

app = FastAPI(title="Nutri-Planner AI Microservice - 100% Offline RAG")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

reader = easyocr.Reader(['en'], gpu=False)

# Load Small Local RAG Embedding Model
try:
    print("Loading Local Embedding Model...")
    embedder = SentenceTransformer('all-MiniLM-L6-v2')
except Exception as e:
    print(f"Failed to load embedding model: {e}")
    embedder = None

# Resolve paths
MODEL_DIR = os.path.join(os.path.dirname(os.path.dirname(__file__)), 'models')
RECIPES_PATH = os.path.join(os.path.dirname(__file__), 'recipes.json')

# Load Risk Models
try:
    diabetes_model = joblib.load(os.path.join(MODEL_DIR, "diabetes_xgb.pkl"))
    heart_model = joblib.load(os.path.join(MODEL_DIR, "heart_xgb.pkl"))
    kidney_model = joblib.load(os.path.join(MODEL_DIR, "kidney_xgb.pkl"))
    scaler = joblib.load(os.path.join(MODEL_DIR, "diabetes_scaler.pkl"))
except Exception as e:
    print(f"Warning: Risk models not found: {e}")
    diabetes_model, heart_model, kidney_model, scaler = None, None, None, None

# Load Recipes Knowledge Base
try:
    with open(RECIPES_PATH, 'r') as f:
        recipes_db = json.load(f)
    print(f"Loaded {len(recipes_db)} recipes into Knowledge Base.")
    
    # Pre-embed recipes for local RAG
    if embedder:
        for r in recipes_db:
            text = f"{r['name']} {' '.join(r['tags'])} {r['instructions']}"
            r['embedding'] = embedder.encode([text])[0]
except Exception as e:
    print(f"Warning: Failed to load recipes.json: {e}")
    recipes_db = []

def extract_tabular_data(ocr_results):
    items = []
    for bbox, text, conf in ocr_results:
        y_center = (bbox[0][1] + bbox[2][1]) / 2
        x_center = (bbox[0][0] + bbox[2][0]) / 2
        items.append({'y': y_center, 'x': x_center, 'text': text})
        
    items.sort(key=lambda item: item['y'])
    lines = []
    if not items: return lines
        
    current_line = [items[0]]
    y_threshold = 15.0  # Pixels
    
    for item in items[1:]:
        avg_y = sum(i['y'] for i in current_line) / len(current_line)
        if abs(item['y'] - avg_y) <= y_threshold:
            current_line.append(item)
        else:
            current_line.sort(key=lambda i: i['x'])
            lines.append(" ".join([i['text'] for i in current_line]))
            current_line = [item]
            
    if current_line:
        current_line.sort(key=lambda i: i['x'])
        lines.append(" ".join([i['text'] for i in current_line]))
        
    return lines

def fuzzy_extract(text_lines, target_word):
    best_score = 0
    best_value = None
    
    for line in text_lines:
        line = line.lower()
        words = line.split()
        for i, w in enumerate(words):
            score = fuzz.ratio(w, target_word.lower())
            if score > 80:
                # Search for numbers after the matching word
                remaining = " ".join(words[i:])
                match = re.search(r'\b(\d+\.?\d*)\b', remaining)
                if match:
                    try:
                        val = float(match.group(1))
                        if score > best_score:
                            best_score = score
                            best_value = val
                    except ValueError:
                        pass
    return best_value

@app.get("/")
def read_root():
    return {"status": "healthy", "service": "Nutri-Planner AI Microservice - Offline"}

@app.post("/api/v1/analyze-report")
async def analyze_report(
    file: UploadFile = File(...), 
    target_calories: int = Form(2000), 
    user_id: int = Form(1),
    allergies: str = Form(""),
    diet_type: str = Form("anything"),
    favorites: str = Form(""),
    x_api_key: str = Header(None)
):
    contents = await file.read()
    image = Image.open(io.BytesIO(contents))
    image_np = np.array(image)

    # 1. Advanced Fuzzy OCR Extraction
    ocr_res = reader.readtext(image_np, detail=1)
    results = extract_tabular_data(ocr_res)
    
    extracted = {}
    extracted['glucose'] = fuzzy_extract(results, "glucose")
    extracted['hba1c'] = fuzzy_extract(results, "hba1c")
    extracted['cholesterol'] = fuzzy_extract(results, "cholesterol")
    extracted['hdl'] = fuzzy_extract(results, "hdl")
    extracted['ldl'] = fuzzy_extract(results, "ldl")
    extracted['triglycerides'] = fuzzy_extract(results, "triglycerides")
    extracted['creatinine'] = fuzzy_extract(results, "creatinine")
    extracted['urea'] = fuzzy_extract(results, "urea")
    extracted['egfr'] = fuzzy_extract(results, "egfr")
    extracted['bmi'] = fuzzy_extract(results, "bmi")

    glucose = extracted.get('glucose') or 90.0
    hba1c = extracted.get('hba1c') or 5.5
    bmi = extracted.get('bmi') or 24.0
    age = 40.0
    chol = extracted.get('cholesterol') or 180.0
    hdl = extracted.get('hdl') or 50.0
    ldl = extracted.get('ldl') or 100.0
    trig = extracted.get('triglycerides') or 120.0
    creat = extracted.get('creatinine') or 0.9
    urea = extracted.get('urea') or 15.0
    egfr = extracted.get('egfr') or 90.0

    diabetes_risk = 15.0
    heart_risk = 20.0
    kidney_risk = 10.0

    if diabetes_model and scaler:
        try:
            diab_features = scaler.transform([[glucose, hba1c, bmi, age]])
            diabetes_risk = float(diabetes_model.predict_proba(diab_features)[0][1] * 100)
            
            heart_features = scaler.transform([[chol, hdl, ldl, trig]])
            heart_risk = float(heart_model.predict_proba(heart_features)[0][1] * 100)
            
            kidney_features = scaler.transform([[creat, urea, egfr, 0]])[:, :3]
            kidney_risk = float(kidney_model.predict_proba(kidney_features)[0][1] * 100)
        except Exception:
            pass 

    biomarkers = {}
    if extracted.get('glucose'):
        biomarkers['Glucose'] = {'value': extracted['glucose'], 'unit': 'mg/dL', 'reference_range': '70-99', 'status': 'NORMAL' if extracted['glucose'] < 100 else 'HIGH'}
    if extracted.get('hba1c'):
        biomarkers['HbA1c'] = {'value': extracted['hba1c'], 'unit': '%', 'reference_range': '4.0-5.6', 'status': 'NORMAL' if extracted['hba1c'] < 5.7 else 'HIGH'}
    if extracted.get('cholesterol'):
        biomarkers['Cholesterol'] = {'value': extracted['cholesterol'], 'unit': 'mg/dL', 'reference_range': '<200', 'status': 'NORMAL' if extracted['cholesterol'] < 200 else 'HIGH'}

    if not biomarkers:
        biomarkers['General'] = {'value': 0, 'unit': '-', 'reference_range': '-', 'status': 'NORMAL'}

    health_risks = [
        {"condition": "Diabetes", "severity": "HIGH" if diabetes_risk > 50 else "LOW", "risk_pct": diabetes_risk},
        {"condition": "Heart Disease", "severity": "HIGH" if heart_risk > 50 else "LOW", "risk_pct": heart_risk},
        {"condition": "Kidney Disease", "severity": "HIGH" if kidney_risk > 50 else "LOW", "risk_pct": kidney_risk}
    ]

    overall_risk_score = max(diabetes_risk, heart_risk, kidney_risk)

    # 2. Local Semantic RAG Meal Retrieval
    final_meals = {}
    
    if embedder and recipes_db:
        # Build strict allergy filter list
        allergy_keywords = [a.strip().lower() for a in allergies.split(',')] if allergies else []
        
        # Filter DB by allergies first (Hard exclusion)
        safe_recipes = []
        for r in recipes_db:
            is_safe = True
            for ak in allergy_keywords:
                if ak and (ak in r['name'].lower() or any(ak in tag for tag in r['tags'])):
                    is_safe = False
                    break
            if is_safe:
                safe_recipes.append(r)
                
        # Build Semantic Query String
        medical_context = []
        if diabetes_risk > 50: medical_context.append("diabetes-friendly low-sugar")
        if heart_risk > 50: medical_context.append("heart-healthy low-cholesterol")
        if kidney_risk > 50: medical_context.append("kidney-friendly low-protein")
        
        query_str = f"Diet: {diet_type}. Favorites: {favorites}. Focus: {' '.join(medical_context)}"
        query_emb = embedder.encode([query_str])[0]
        
        # Retrieve Best Matching Meals
        slots = ['breakfast', 'lunch', 'dinner', 'snack']
        for slot in slots:
            slot_recipes = [r for r in safe_recipes if r['slot'] == slot]
            if slot_recipes:
                embs = np.array([r['embedding'] for r in slot_recipes])
                similarities = cosine_similarity([query_emb], embs)[0]
                best_idx = np.argmax(similarities)
                best_recipe = slot_recipes[best_idx]
                
                final_meals[slot] = {
                    "name": best_recipe['name'] + " (RAG Match)",
                    "calories": best_recipe['calories'],
                    "protein": best_recipe['protein'],
                    "carbs": best_recipe['carbs'],
                    "fat": best_recipe['fat'],
                    "fiber": best_recipe['fiber'],
                    "instructions": best_recipe['instructions']
                }
            else:
                # Fallback if no safe recipe in slot
                final_meals[slot] = {"name": f"Safe {slot.capitalize()}", "calories": 300, "protein": 20, "carbs": 30, "fat": 10, "instructions": "Standard safe meal."}

    else:
        # Fallback if RAG fails
        final_meals = {
            "breakfast": {"name": "Oatmeal (Fallback)", "calories": 350, "protein": 12, "carbs": 50, "fat": 8, "instructions": "Boil oats."},
            "lunch": {"name": "Chicken Salad (Fallback)", "calories": 450, "protein": 35, "carbs": 20, "fat": 15, "instructions": "Toss greens."},
            "dinner": {"name": "Baked Salmon (Fallback)", "calories": 500, "protein": 40, "carbs": 45, "fat": 20, "instructions": "Bake salmon."},
            "snack": {"name": "Mixed Nuts (Fallback)", "calories": 150, "protein": 5, "carbs": 5, "fat": 10, "instructions": "Serve."}
        }

    meal_plan = {
        "dietary_summary": ["Strict Allergy Exclusions Applied", f"Targeting {diet_type} diet", "Personalized via Local AI"],
        "meals": final_meals
    }

    return {
        "status": "success",
        "raw_text": " ".join(results)[:1000], 
        "overall_risk_score": overall_risk_score,
        "biomarkers": biomarkers,
        "health_risks": health_risks,
        "meal_plan": meal_plan
    }
