import mysql.connector
import os
from app.config import settings

def update_images():
    conn = mysql.connector.connect(
        host=settings.DB_HOST,
        user=settings.DB_USER,
        password=settings.DB_PASS,
        database=settings.DB_NAME
    )
    cursor = conn.cursor()

    # Highly accurate Unsplash/Pexels URLs for the specific meals
    image_map = {
        1: "https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=800&q=80", # Chicken Breast & Quinoa (Chicken Salad)
        2: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80", # Avocado Toast
        3: "https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?auto=format&fit=crop&w=800&q=80", # Lentil Soup
        4: "https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80", # Salmon & Sweet Potato
        5: "https://images.unsplash.com/photo-1623341214825-9f4f963727da?auto=format&fit=crop&w=800&q=80", # Greek Yogurt Bowl
        6: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800&q=80", # Mediterranean Salad
        7: "https://images.unsplash.com/photo-1511690656952-34342bb7c2f2?auto=format&fit=crop&w=800&q=80", # Chicken Soup
        8: "https://images.unsplash.com/photo-1603894584373-5ac82b6ae398?auto=format&fit=crop&w=800&q=80", # Turkey Wrap
        9: "https://images.unsplash.com/photo-1505253758473-96b7015fcd40?auto=format&fit=crop&w=800&q=80", # Tofu Stir Fry
        10: "https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80", # Egg Salad
        11: "https://images.unsplash.com/photo-1543339308-43e59d6b73a6?auto=format&fit=crop&w=800&q=80", # Grilled Veggie Plate
    }

    for meal_id, url in image_map.items():
        cursor.execute("UPDATE recipes SET image_url = %s WHERE id = %s", (url, meal_id))
        print(f"Updated recipe {meal_id}")
        
    conn.commit()
    cursor.close()
    conn.close()

if __name__ == "__main__":
    update_images()
