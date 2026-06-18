import os
import sys
import json
import mysql.connector

# Ensure we can import app modules if needed
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
from app.config import settings

def migrate_recipes():
    try:
        conn = mysql.connector.connect(
            host=settings.DB_HOST,
            user=settings.DB_USER,
            password=settings.DB_PASS,
            database=settings.DB_NAME
        )
        cursor = conn.cursor()
    except Exception as e:
        print(f"Failed to connect to database: {e}")
        return

    # Clear existing tables for a clean migration
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")
    cursor.execute("TRUNCATE TABLE ingredients;")
    cursor.execute("TRUNCATE TABLE nutrition_facts;")
    cursor.execute("TRUNCATE TABLE recipes;")
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")

    recipes_data = [
        {
            "name": "Oatmeal with Berries and Chia", "slot": "breakfast", "tags": ["vegetarian", "vegan", "high-fiber", "low-sugar", "diabetes-friendly", "heart-healthy", "kidney-friendly"],
            "instructions": "Boil oats in water/milk. Top with fresh berries and a tablespoon of chia seeds.",
            "image_url": "https://images.unsplash.com/photo-1517673132405-a56a62b18caf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Rolled Oats", "base_quantity": 50, "unit": "g", "is_allergen": False},
                {"name": "Water or Almond Milk", "base_quantity": 250, "unit": "ml", "is_allergen": False},
                {"name": "Mixed Berries", "base_quantity": 80, "unit": "g", "is_allergen": False},
                {"name": "Chia Seeds", "base_quantity": 15, "unit": "g", "is_allergen": False}
            ],
            "nutrition": {"calories": 350, "protein": 12, "carbs": 50, "fat": 8, "fiber": 10, "sodium": 50, "potassium": 450}
        },
        {
            "name": "Greek Yogurt Parfait", "slot": "breakfast", "tags": ["vegetarian", "high-protein", "calcium", "low-carb"],
            "instructions": "Layer unsweetened Greek yogurt with sliced almonds and a drizzle of honey.",
            "image_url": "https://images.unsplash.com/photo-1488477181946-6428a0291777?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Greek Yogurt (Plain)", "base_quantity": 200, "unit": "g", "is_allergen": True},
                {"name": "Sliced Almonds", "base_quantity": 30, "unit": "g", "is_allergen": True},
                {"name": "Honey", "base_quantity": 15, "unit": "ml", "is_allergen": False}
            ],
            "nutrition": {"calories": 280, "protein": 22, "carbs": 30, "fat": 5, "fiber": 3, "sodium": 80, "potassium": 300}
        },
        {
            "name": "Egg White Spinach Omelet", "slot": "breakfast", "tags": ["keto", "paleo", "high-protein", "low-carb", "diabetes-friendly"],
            "instructions": "Whisk egg whites and one whole egg. Cook with fresh spinach and a pinch of black pepper.",
            "image_url": "https://images.unsplash.com/photo-1510693064619-a9eb8cc3d4c7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Egg Whites", "base_quantity": 150, "unit": "g", "is_allergen": True},
                {"name": "Whole Egg", "base_quantity": 50, "unit": "g", "is_allergen": True},
                {"name": "Fresh Spinach", "base_quantity": 60, "unit": "g", "is_allergen": False},
                {"name": "Olive Oil", "base_quantity": 5, "unit": "ml", "is_allergen": False}
            ],
            "nutrition": {"calories": 250, "protein": 25, "carbs": 5, "fat": 12, "fiber": 2, "sodium": 350, "potassium": 420}
        },
        {
            "name": "Grilled Chicken Salad", "slot": "lunch", "tags": ["paleo", "keto", "high-protein", "low-carb", "diabetes-friendly", "heart-healthy"],
            "instructions": "Grill chicken breast. Serve over mixed greens, cherry tomatoes, cucumbers, and olive oil vinaigrette.",
            "image_url": "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Chicken Breast", "base_quantity": 150, "unit": "g", "is_allergen": False},
                {"name": "Mixed Greens", "base_quantity": 100, "unit": "g", "is_allergen": False},
                {"name": "Cherry Tomatoes", "base_quantity": 50, "unit": "g", "is_allergen": False},
                {"name": "Olive Oil", "base_quantity": 15, "unit": "ml", "is_allergen": False}
            ],
            "nutrition": {"calories": 400, "protein": 40, "carbs": 15, "fat": 18, "fiber": 6, "sodium": 150, "potassium": 650}
        },
        {
            "name": "Quinoa Bowl with Roasted Tofu", "slot": "lunch", "tags": ["vegan", "vegetarian", "high-fiber", "heart-healthy"],
            "instructions": "Roast tofu cubes. Serve over cooked quinoa with steamed broccoli and a light soy dressing.",
            "image_url": "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Firm Tofu", "base_quantity": 120, "unit": "g", "is_allergen": True},
                {"name": "Quinoa (cooked)", "base_quantity": 150, "unit": "g", "is_allergen": False},
                {"name": "Broccoli", "base_quantity": 80, "unit": "g", "is_allergen": False},
                {"name": "Soy Sauce (Low Sodium)", "base_quantity": 15, "unit": "ml", "is_allergen": True}
            ],
            "nutrition": {"calories": 450, "protein": 20, "carbs": 55, "fat": 15, "fiber": 8, "sodium": 400, "potassium": 580}
        },
        {
            "name": "Salmon and Asparagus", "slot": "dinner", "tags": ["keto", "paleo", "high-protein", "omega-3", "heart-healthy", "low-carb"],
            "instructions": "Bake salmon fillet at 400F for 15 mins. Roast asparagus with a touch of olive oil and lemon.",
            "image_url": "https://images.unsplash.com/photo-1467003909585-2f8a72700288?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Salmon Fillet", "base_quantity": 150, "unit": "g", "is_allergen": True},
                {"name": "Asparagus", "base_quantity": 120, "unit": "g", "is_allergen": False},
                {"name": "Olive Oil", "base_quantity": 10, "unit": "ml", "is_allergen": False},
                {"name": "Lemon Juice", "base_quantity": 10, "unit": "ml", "is_allergen": False}
            ],
            "nutrition": {"calories": 500, "protein": 45, "carbs": 10, "fat": 25, "fiber": 5, "sodium": 120, "potassium": 850}
        },
        {
            "name": "Lentil Soup", "slot": "dinner", "tags": ["vegan", "vegetarian", "high-fiber", "diabetes-friendly", "heart-healthy", "kidney-friendly"],
            "instructions": "Simmer lentils with carrots, celery, onions, and vegetable broth until tender.",
            "image_url": "https://images.unsplash.com/photo-1547592180-85f173990554?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Dry Lentils", "base_quantity": 70, "unit": "g", "is_allergen": False},
                {"name": "Carrots", "base_quantity": 50, "unit": "g", "is_allergen": False},
                {"name": "Vegetable Broth (Low Sodium)", "base_quantity": 300, "unit": "ml", "is_allergen": False},
                {"name": "Onion", "base_quantity": 40, "unit": "g", "is_allergen": False}
            ],
            "nutrition": {"calories": 380, "protein": 18, "carbs": 60, "fat": 5, "fiber": 15, "sodium": 300, "potassium": 700}
        },
        {
            "name": "Handful of Mixed Nuts", "slot": "snack", "tags": ["vegan", "vegetarian", "keto", "paleo", "heart-healthy", "omega-3"],
            "instructions": "A small handful of unsalted almonds and walnuts.",
            "image_url": "https://images.unsplash.com/photo-1536585141940-1ec3b5bd0089?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Almonds", "base_quantity": 15, "unit": "g", "is_allergen": True},
                {"name": "Walnuts", "base_quantity": 15, "unit": "g", "is_allergen": True}
            ],
            "nutrition": {"calories": 200, "protein": 6, "carbs": 8, "fat": 18, "fiber": 3, "sodium": 0, "potassium": 150}
        },
        {
            "name": "Apple with Almond Butter", "slot": "snack", "tags": ["vegan", "vegetarian", "high-fiber", "kidney-friendly"],
            "instructions": "Slice one medium apple and serve with 1 tablespoon of almond butter.",
            "image_url": "https://images.unsplash.com/photo-1568249051862-2435e07663e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Medium Apple", "base_quantity": 150, "unit": "g", "is_allergen": False},
                {"name": "Almond Butter", "base_quantity": 16, "unit": "g", "is_allergen": True}
            ],
            "nutrition": {"calories": 220, "protein": 4, "carbs": 25, "fat": 12, "fiber": 5, "sodium": 15, "potassium": 300}
        },
        {
            "name": "Shrimp Stir-Fry", "slot": "dinner", "tags": ["high-protein", "shellfish", "heart-healthy"],
            "instructions": "Stir-fry shrimp with bell peppers, snap peas, and a light ginger-soy sauce. Serve with brown rice.",
            "image_url": "https://images.unsplash.com/photo-1540420773420-3366772f4999?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Shrimp (Peeled)", "base_quantity": 150, "unit": "g", "is_allergen": True},
                {"name": "Bell Peppers", "base_quantity": 80, "unit": "g", "is_allergen": False},
                {"name": "Brown Rice (cooked)", "base_quantity": 100, "unit": "g", "is_allergen": False},
                {"name": "Soy Sauce", "base_quantity": 10, "unit": "ml", "is_allergen": True}
            ],
            "nutrition": {"calories": 420, "protein": 35, "carbs": 40, "fat": 12, "fiber": 6, "sodium": 500, "potassium": 450}
        },
        {
            "name": "Peanut Butter Banana Smoothie", "slot": "breakfast", "tags": ["vegetarian", "peanuts", "quick-breakfast"],
            "instructions": "Blend 1 banana, 2 tbsp peanut butter, milk of choice, and ice.",
            "image_url": "https://images.unsplash.com/photo-1553530666-ba11a7da3888?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80",
            "ingredients": [
                {"name": "Banana", "base_quantity": 120, "unit": "g", "is_allergen": False},
                {"name": "Peanut Butter", "base_quantity": 32, "unit": "g", "is_allergen": True},
                {"name": "Milk", "base_quantity": 250, "unit": "ml", "is_allergen": True}
            ],
            "nutrition": {"calories": 380, "protein": 15, "carbs": 45, "fat": 16, "fiber": 6, "sodium": 150, "potassium": 650}
        }
    ]

    for r in recipes_data:
        # Insert recipe
        cursor.execute("""
            INSERT INTO recipes (name, slot, tags, instructions, image_url)
            VALUES (%s, %s, %s, %s, %s)
        """, (r["name"], r["slot"], json.dumps(r["tags"]), r["instructions"], r["image_url"]))
        recipe_id = cursor.lastrowid

        # Insert ingredients
        for ing in r["ingredients"]:
            cursor.execute("""
                INSERT INTO ingredients (recipe_id, name, base_quantity, unit, is_allergen)
                VALUES (%s, %s, %s, %s, %s)
            """, (recipe_id, ing["name"], ing["base_quantity"], ing["unit"], ing["is_allergen"]))

        # Insert nutrition
        n = r["nutrition"]
        cursor.execute("""
            INSERT INTO nutrition_facts (recipe_id, base_calories, base_protein, base_carbs, base_fat, base_fiber, base_sodium, base_potassium)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """, (recipe_id, n["calories"], n["protein"], n["carbs"], n["fat"], n["fiber"], n["sodium"], n["potassium"]))

    conn.commit()
    cursor.close()
    conn.close()
    print("Database seeded successfully with dynamic recipes, ingredients, and nutritional facts!")

if __name__ == "__main__":
    migrate_recipes()
