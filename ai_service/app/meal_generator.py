import logging
import random
import requests
import json
import pymysql
from google import genai
from google.genai import types
from typing import Dict, Any, List, Optional
from app.config import settings

logger = logging.getLogger("MealGenerator")

class MealGenerator:
    def __init__(self):
        if settings.GEMINI_API_KEY and settings.GEMINI_API_KEY != "YOUR_SPOONACULAR_API_KEY":
            try:
                self.gemini_client = genai.Client(api_key=settings.GEMINI_API_KEY)
                self.use_llm = True
            except Exception as e:
                logger.error(f"Failed to configure Gemini API: {e}")
                self.use_llm = False
        else:
            self.use_llm = False
            
        # A curated clinical database of targeted meals for medical conditions (Fallback)
        self.local_recipes = {
            "breakfast": [
                {
                    "name": "Steel-Cut Oats with Walnuts & Blueberries",
                    "calories": 320, "protein": 10.0, "carbs": 45.0, "fat": 12.0, "fiber": 8.0,
                    "sodium": 10.0, "iron": 3.5, "saturated_fat": 1.0,
                    "tags": ["cholesterol", "hypertension", "general", "diabetes"],
                    "instructions": "Simmer steel-cut oats in water. Top with walnuts, fresh blueberries, and a dash of cinnamon."
                }
            ],
            "lunch": [
                {
                    "name": "Quinoa, Black Bean & Avocado Salad",
                    "calories": 420, "protein": 14.0, "carbs": 55.0, "fat": 16.0, "fiber": 12.0,
                    "sodium": 95.0, "iron": 4.2, "saturated_fat": 2.0,
                    "tags": ["cholesterol", "hypertension", "diabetes", "general"],
                    "instructions": "Toss cooked quinoa, rinsed low-sodium black beans, diced avocado, cilantro, and lime juice dressing."
                }
            ],
            "dinner": [
                {
                    "name": "Garlic Herb Baked Salmon with Asparagus",
                    "calories": 510, "protein": 38.0, "carbs": 10.0, "fat": 28.0, "fiber": 4.0,
                    "sodium": 140.0, "iron": 3.0, "saturated_fat": 4.5,
                    "tags": ["cholesterol", "hypertension", "vitamin_d", "diabetes", "general"],
                    "instructions": "Bake fresh salmon fillet with minced garlic, dill, and olive oil. Serve with roasted asparagus spears and half a sweet potato."
                }
            ],
            "snack": [
                {
                    "name": "Raw Almonds & Dried Apricots",
                    "calories": 160, "protein": 5.0, "carbs": 18.0, "fat": 9.0, "fiber": 4.0,
                    "sodium": 0.0, "iron": 1.5, "saturated_fat": 0.8,
                    "tags": ["cholesterol", "anemia", "general"],
                    "instructions": "Portion 12 raw almonds and 4 unsulfured dried apricots."
                }
            ]
        }

    def _fetch_user_context_from_db(self, user_id: int) -> str:
        """Retrieves user profile and historical health risks from the DB to be used as RAG context."""
        try:
            conn = pymysql.connect(
                host=settings.DB_HOST,
                user=settings.DB_USER,
                password=settings.DB_PASS,
                database=settings.DB_NAME,
                cursorclass=pymysql.cursors.DictCursor
            )
            context = []
            with conn.cursor() as cursor:
                # 1. Get physical profile
                cursor.execute("SELECT * FROM user_profiles WHERE user_id=%s", (user_id,))
                profile = cursor.fetchone()
                if profile:
                    context.append(f"- User Profile: Age {profile.get('age')}, Gender: {profile.get('gender')}, Weight: {profile.get('weight_kg')}kg, Health Goal: {profile.get('goal')}, Diet Type: {profile.get('diet_type')}")
                
                # 2. Get past medical conditions
                cursor.execute("SELECT risk_condition, severity FROM user_health_risks WHERE user_id=%s ORDER BY created_at DESC LIMIT 5", (user_id,))
                risks = cursor.fetchall()
                if risks:
                    risk_strs = [f"{r['risk_condition']} ({r['severity']})" for r in risks]
                    context.append("- Past Medical Risks History: " + ", ".join(risk_strs))
            conn.close()
            return "\n".join(context)
        except Exception as e:
            logger.error(f"Failed to fetch user DB context for RAG: {e}")
            return "No historical context available."

    def _generate_rag_meal_plan(self, user_context: str, health_data: Dict[str, Any], target_calories: int) -> Optional[Dict[str, Any]]:
        """Invokes Gemini LLM to generate the meal plan based on DB RAG context and current lab report."""
        try:
            dietary_rules = health_data.get("dietary_rules", {})
            avoid = ", ".join(dietary_rules.get("avoid_foods", []))
            recommend = ", ".join(dietary_rules.get("recommend_foods", []))
            active_risks = [r["condition"] for r in health_data.get("health_risks", [])]
            
            prompt = f"""
            You are an advanced Clinical Nutrition AI. 
            Generate a personalized 4-meal plan (breakfast, lunch, dinner, snack) for this user.
            
            USER MEDICAL CONTEXT (Retrieved from Database):
            {user_context}
            
            CURRENT DIAGNOSED RISKS (From Latest Lab Report):
            {', '.join(active_risks) if active_risks else 'None'}
            
            DIETARY CONSTRAINTS:
            - Target Total Calories: ~{target_calories} kcal
            - Foods to STRICTLY Avoid: {avoid if avoid else 'None'}
            - Recommended Foods to Include: {recommend if recommend else 'None'}
            
            You must output ONLY valid JSON.
            The JSON structure MUST be EXACTLY:
            {{
                "breakfast": {{"name": "...", "calories": 400, "protein": 20.0, "carbs": 40.0, "fat": 15.0, "instructions": "..."}},
                "lunch": {{"name": "...", "calories": 500, "protein": 30.0, "carbs": 50.0, "fat": 20.0, "instructions": "..."}},
                "dinner": {{"name": "...", "calories": 600, "protein": 40.0, "carbs": 40.0, "fat": 25.0, "instructions": "..."}},
                "snack": {{"name": "...", "calories": 200, "protein": 10.0, "carbs": 20.0, "fat": 5.0, "instructions": "..."}}
            }}
            """
            
            config = types.GenerateContentConfig(
                response_mime_type="application/json",
            )
            response = self.gemini_client.models.generate_content(
                model='gemini-2.5-flash',
                contents=prompt,
                config=config
            )
            
            # Clean response text (though mime_type config mostly handles this)
            text = response.text.strip()
            if text.startswith("```json"):
                text = text[7:]
            if text.startswith("```"):
                text = text[3:]
            if text.endswith("```"):
                text = text[:-3]
                
            meals_data = json.loads(text.strip())
            
            if not all(slot in meals_data for slot in ["breakfast", "lunch", "dinner", "snack"]):
                logger.error("LLM JSON missing required meal slots.")
                return None
                
            total_calories = sum(m["calories"] for m in meals_data.values())
            total_protein = sum(m["protein"] for m in meals_data.values())
            total_carbs = sum(m["carbs"] for m in meals_data.values())
            total_fat = sum(m["fat"] for m in meals_data.values())
            
            logger.info("Successfully generated RAG-based LLM meal plan.")
            return {
                "meals": meals_data,
                "totals": {
                    "calories": round(total_calories),
                    "protein_g": round(total_protein, 1),
                    "carbs_g": round(total_carbs, 1),
                    "fat_g": round(total_fat, 1),
                    "sodium_mg": 0
                },
                "dietary_summary": {
                    "active_targets": dietary_rules.get("nutrient_targets", {}),
                    "restricted_ingredients": dietary_rules.get("avoid_foods", []),
                    "recommended_ingredients": dietary_rules.get("recommend_foods", [])
                }
            }
        except Exception as e:
            logger.error(f"RAG LLM Meal Generation failed: {e}")
            return None

    def generate_meal_plan(self, health_data: Dict[str, Any], target_calories: int = 2000, user_id: Optional[int] = None) -> Dict[str, Any]:
        """
        Generates a 4-meal plan based on parsed health risks and dietary rules.
        Uses LLM RAG if configured, else falls back to local curated meals.
        """
        dietary_rules = health_data.get("dietary_rules", {})
        
        # 1. Attempt RAG LLM Generation
        if self.use_llm:
            user_context = ""
            if user_id:
                user_context = self._fetch_user_context_from_db(user_id)
            
            llm_plan = self._generate_rag_meal_plan(user_context, health_data, target_calories)
            if llm_plan:
                return llm_plan
        
        # 2. Fallback to Local Curated Meals (if LLM fails or is not configured)
        logger.warning("Falling back to local curated meals.")
        plan = {}
        total_plan_calories = 0
        total_plan_protein = 0
        total_plan_carbs = 0
        total_plan_fat = 0
        total_plan_sodium = 0
        
        for slot in ["breakfast", "lunch", "dinner", "snack"]:
            meal = self.local_recipes.get(slot, [{}])[0]
            if not meal:
                continue
            plan[slot] = meal
            total_plan_calories += meal.get("calories", 0)
            total_plan_protein += meal.get("protein", 0.0)
            total_plan_carbs += meal.get("carbs", 0.0)
            total_plan_fat += meal.get("fat", 0.0)
            total_plan_sodium += meal.get("sodium", 0.0)

        return {
            "meals": plan,
            "totals": {
                "calories": round(total_plan_calories),
                "protein_g": round(total_plan_protein, 1),
                "carbs_g": round(total_plan_carbs, 1),
                "fat_g": round(total_plan_fat, 1),
                "sodium_mg": round(total_plan_sodium, 1)
            },
            "dietary_summary": {
                "active_targets": dietary_rules.get("nutrient_targets", {}),
                "restricted_ingredients": dietary_rules.get("avoid_foods", []),
                "recommended_ingredients": dietary_rules.get("recommend_foods", [])
            }
        }
