<?php
// Offline mock food datasets for USDA and Spoonacular to prevent API failures
class LocalFoodDB {
  private static $recipes = [
    [
      'id' => 101,
      'title' => 'Greek Yogurt Parfait with Mixed Berries',
      'calories' => 320,
      'protein_g' => 22,
      'carbs_g' => 38,
      'fat_g' => 8,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1481391243146-5e913a0c0e5a?w=600&q=80',
      'ingredients' => ['1 cup Greek yogurt', '1/2 cup mixed berries', '1/4 cup granola', '1 tbsp honey'],
      'instructions' => 'Layer Greek yogurt, fresh berries, and crunchy granola in a glass.\nDrizzle with honey.\nServe immediately chilled.',
      'tags' => ['breakfast', 'vegetarian', 'high protein']
    ],
    [
      'id' => 102,
      'title' => 'Masala Oats with Spinach & Soft-Boiled Egg',
      'calories' => 380,
      'protein_g' => 18,
      'carbs_g' => 42,
      'fat_g' => 12,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=600&q=80',
      'ingredients' => ['1/2 cup rolled oats', '1 cup spinach', '1 large egg', '1/2 tsp turmeric', 'Salt to taste'],
      'instructions' => 'Cook oats with spinach, turmeric, chili powder, and salt.\nBoil egg for 6 minutes, peel, slice, and place on top.',
      'tags' => ['breakfast', 'vegetarian']
    ],
    [
      'id' => 103,
      'title' => 'Avocado Toast with Poached Eggs',
      'calories' => 350,
      'protein_g' => 16,
      'carbs_g' => 24,
      'fat_g' => 22,
      'fiber_g' => 8,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=600&q=80',
      'ingredients' => ['2 slices whole wheat bread', '1/2 ripe avocado', '2 large eggs', 'Pinch of red pepper flakes'],
      'instructions' => 'Toast whole wheat bread.\nMash avocado with lime juice, salt, pepper, and spread on toast.\nTop with poached eggs.',
      'tags' => ['breakfast', 'vegetarian']
    ],
    [
      'id' => 104,
      'title' => 'Vegan Chia Seed Pudding',
      'calories' => 290,
      'protein_g' => 10,
      'carbs_g' => 34,
      'fat_g' => 14,
      'fiber_g' => 10,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1555505019-8c3f1c4aba5f?w=600&q=80',
      'ingredients' => ['3 tbsp chia seeds', '1 cup almond milk', '1/2 sliced banana', '1 tbsp maple syrup'],
      'instructions' => 'Mix chia seeds, almond milk, and maple syrup.\nLet sit in fridge for 2 hours or overnight.\nTop with banana slices.',
      'tags' => ['breakfast', 'vegan', 'vegetarian', 'gluten-free']
    ],
    [
      'id' => 105,
      'title' => 'Peanut Butter & Banana Toast',
      'calories' => 310,
      'protein_g' => 11,
      'carbs_g' => 39,
      'fat_g' => 15,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1620953158022-77727e02e1b9?w=600&q=80',
      'ingredients' => ['2 slices whole wheat bread', '2 tbsp natural peanut butter', '1 banana, sliced', '1 tsp chia seeds'],
      'instructions' => 'Toast whole wheat bread.\nSpread 2 tbsp peanut butter.\nTop with sliced banana and chia seeds.',
      'tags' => ['breakfast', 'vegan', 'vegetarian']
    ],
    [
      'id' => 106,
      'title' => 'Scrambled Egg Whites with Spinach',
      'calories' => 190,
      'protein_g' => 24,
      'carbs_g' => 6,
      'fat_g' => 8,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1515002246390-7bf7e8f87b54?w=600&q=80',
      'ingredients' => ['1 cup liquid egg whites', '1 cup baby spinach', '1/2 cup sliced mushrooms', '1 tsp olive oil'],
      'instructions' => 'Whisk egg whites with salt and pepper.\nSauté sliced mushrooms and baby spinach in a pan.\nPour in egg whites and scramble.',
      'tags' => ['breakfast', 'vegetarian', 'high protein', 'keto']
    ],
    [
      'id' => 107,
      'title' => 'Protein Pancakes with Blueberries',
      'calories' => 410,
      'protein_g' => 25,
      'carbs_g' => 48,
      'fat_g' => 9,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1528207776546-3221975e533c?w=600&q=80',
      'ingredients' => ['1/2 cup whole wheat flour', '1 scoop vanilla protein powder', '1 egg', '1/4 cup blueberries'],
      'instructions' => 'Mix whole wheat flour, protein powder, milk, and egg.\nCook on hot griddle until bubbles form, flip.\nServe with fresh blueberries.',
      'tags' => ['breakfast', 'vegetarian', 'high protein']
    ],
    [
      'id' => 108,
      'title' => 'Middle Eastern Shakshuka with Feta',
      'calories' => 380,
      'protein_g' => 18,
      'carbs_g' => 28,
      'fat_g' => 22,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1590412200988-a436970781fa?w=600&q=80',
      'ingredients' => ['2 large eggs', '1 cup crushed tomatoes', '1/2 bell pepper, diced', '1/4 cup feta cheese'],
      'instructions' => 'Sauté onions and peppers.\nPour in crushed tomatoes and simmer.\nCrack eggs directly into the sauce and cover until cooked.\nGarnish with feta.',
      'tags' => ['breakfast', 'vegetarian', 'low carb']
    ],
    [
      'id' => 109,
      'title' => 'Vegan Mango Smoothie Bowl',
      'calories' => 320,
      'protein_g' => 10,
      'carbs_g' => 62,
      'fat_g' => 8,
      'fiber_g' => 9,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1494390248081-4e521a5940db?w=600&q=80',
      'ingredients' => ['1 cup frozen mango', '1/2 cup almond milk', '1 tbsp chia seeds', '1/4 cup coconut flakes'],
      'instructions' => 'Blend frozen mango and almond milk until creamy.\nPour into a bowl.\nTop with chia seeds and coconut flakes.',
      'tags' => ['breakfast', 'vegan', 'vegetarian']
    ],
    [
      'id' => 110,
      'title' => 'Keto Bacon & Egg Cups',
      'calories' => 340,
      'protein_g' => 22,
      'carbs_g' => 2,
      'fat_g' => 28,
      'fiber_g' => 0,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=600&q=80',
      'ingredients' => ['3 strips bacon', '3 large eggs', 'Salt and pepper', '1 tbsp chopped chives'],
      'instructions' => 'Line muffin tins with bacon strips.\nCrack an egg into each bacon cup.\nBake at 375F for 15 minutes.\nGarnish with chives.',
      'tags' => ['breakfast', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 111,
      'title' => 'Tofu Scramble with Bell Peppers',
      'calories' => 240,
      'protein_g' => 18,
      'carbs_g' => 12,
      'fat_g' => 14,
      'fiber_g' => 4,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',
      'ingredients' => ['1 block firm tofu', '1/2 red bell pepper, diced', '1/4 tsp turmeric', '1 tbsp nutritional yeast'],
      'instructions' => 'Crumble tofu into a pan with olive oil.\nAdd diced bell peppers, turmeric, salt, and nutritional yeast.\nSauté for 5-7 minutes.',
      'tags' => ['breakfast', 'vegan', 'vegetarian', 'keto']
    ],
    [
      'id' => 112,
      'title' => 'Smoked Salmon & Cream Cheese Bagel',
      'calories' => 450,
      'protein_g' => 22,
      'carbs_g' => 48,
      'fat_g' => 18,
      'fiber_g' => 3,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1513442542250-854d436a73f2?w=600&q=80',
      'ingredients' => ['1 whole wheat bagel', '2 oz smoked salmon', '2 tbsp cream cheese', 'Dill and capers'],
      'instructions' => 'Toast the bagel.\nSpread a thick layer of cream cheese.\nLayer smoked salmon and top with capers and fresh dill.',
      'tags' => ['breakfast']
    ],
    [
      'id' => 113,
      'title' => 'Sweet Potato Hash with Fried Egg',
      'calories' => 380,
      'protein_g' => 14,
      'carbs_g' => 42,
      'fat_g' => 18,
      'fiber_g' => 7,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1596646194726-5b4fc7c22bfd?w=600&q=80',
      'ingredients' => ['1 medium sweet potato, diced', '1/2 onion, chopped', '2 large eggs', '1 tbsp olive oil'],
      'instructions' => 'Sauté diced sweet potato and onion in olive oil until soft and browned.\nFry two eggs in a separate pan.\nServe eggs over the hash.',
      'tags' => ['breakfast', 'vegetarian', 'gluten-free']
    ],
    [
      'id' => 114,
      'title' => 'Overnight Apple Cinnamon Oats',
      'calories' => 310,
      'protein_g' => 9,
      'carbs_g' => 58,
      'fat_g' => 5,
      'fiber_g' => 8,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=600&q=80',
      'ingredients' => ['1/2 cup rolled oats', '1/2 cup almond milk', '1/2 diced apple', '1 tsp cinnamon'],
      'instructions' => 'Mix oats, almond milk, diced apple, and cinnamon in a jar.\nRefrigerate overnight.\nEat cold or warm up in the morning.',
      'tags' => ['breakfast', 'vegan', 'vegetarian']
    ],
    [
      'id' => 115,
      'title' => 'Breakfast Burrito with Black Beans',
      'calories' => 460,
      'protein_g' => 20,
      'carbs_g' => 54,
      'fat_g' => 18,
      'fiber_g' => 9,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
      'ingredients' => ['1 large whole wheat tortilla', '2 scrambled eggs', '1/4 cup black beans', '2 tbsp salsa'],
      'instructions' => 'Scramble the eggs.\nWarm the tortilla.\nWrap eggs, black beans, salsa, and a sprinkle of cheese tightly in the tortilla.',
      'tags' => ['breakfast', 'vegetarian']
    ],
    [
      'id' => 116,
      'title' => 'Grilled Chicken Caesar Salad',
      'calories' => 440,
      'protein_g' => 42,
      'carbs_g' => 28,
      'fat_g' => 16,
      'fiber_g' => 4,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['6 oz chicken breast', '2 cups romaine lettuce', '2 tbsp Caesar dressing', '1/4 cup croutons'],
      'instructions' => 'Grill chicken breast until cooked through.\nToss crisp romaine lettuce with croutons, parmesan, and Caesar dressing.\nTop with sliced chicken.',
      'tags' => ['lunch', 'high protein']
    ],
    [
      'id' => 117,
      'title' => 'Quinoa Bowl with Roasted Vegetables',
      'calories' => 420,
      'protein_g' => 15,
      'carbs_g' => 58,
      'fat_g' => 12,
      'fiber_g' => 10,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1543339308-43e59d6b73a6?w=600&q=80',
      'ingredients' => ['1/2 cup cooked quinoa', '1/2 cup roasted zucchini', '1/4 cup chickpeas', '1 tbsp tahini'],
      'instructions' => 'Cook quinoa.\nRoast zucchini, bell peppers, and chickpeas with olive oil.\nCombine in bowl and drizzle with tahini.',
      'tags' => ['lunch', 'vegan', 'vegetarian', 'gluten-free']
    ],
    [
      'id' => 118,
      'title' => 'Sesame Seared Tuna Salad',
      'calories' => 390,
      'protein_g' => 36,
      'carbs_g' => 14,
      'fat_g' => 18,
      'fiber_g' => 3,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80',
      'ingredients' => ['5 oz ahi tuna steak', '1 tbsp sesame seeds', '2 cups mixed greens', '1 tbsp ginger dressing'],
      'instructions' => 'Coat tuna with sesame seeds and sear for 1 min each side.\nSlice and serve over mixed salad greens with ginger-soy dressing.',
      'tags' => ['lunch', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 119,
      'title' => 'Spicy Turkey & Hummus Wrap',
      'calories' => 450,
      'protein_g' => 32,
      'carbs_g' => 36,
      'fat_g' => 14,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
      'ingredients' => ['1 spinach tortilla', '4 slices turkey breast', '2 tbsp spicy hummus', '1/2 cup spinach leaves'],
      'instructions' => 'Spread spicy hummus on a spinach tortilla.\nAdd sliced turkey breast, spinach, tomatoes, and roll tightly.',
      'tags' => ['lunch', 'high protein']
    ],
    [
      'id' => 120,
      'title' => 'Mediterranean Chickpea Salad',
      'calories' => 360,
      'protein_g' => 14,
      'carbs_g' => 44,
      'fat_g' => 15,
      'fiber_g' => 9,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['1 cup canned chickpeas', '1/2 cucumber, diced', '1/4 cup feta cheese', '1 tbsp olive oil'],
      'instructions' => 'Toss drained chickpeas, chopped cucumber, cherry tomatoes, olives, and crumbled feta cheese.\nDrizzle with olive oil vinaigrette.',
      'tags' => ['lunch', 'vegetarian', 'gluten-free']
    ],
    [
      'id' => 121,
      'title' => 'Grilled Paneer Wrap with Mint Chutney',
      'calories' => 510,
      'protein_g' => 22,
      'carbs_g' => 48,
      'fat_g' => 20,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
      'ingredients' => ['100g paneer, cubed', '1 whole wheat wrap', '2 tbsp mint chutney', '1/2 sliced onion'],
      'instructions' => 'Grill paneer slices with tikka spices.\nSpread mint chutney on a whole wheat wrap.\nAdd salad greens and wrap paneer.',
      'tags' => ['lunch', 'vegetarian']
    ],
    [
      'id' => 122,
      'title' => 'Turkey & Avocado Sandwich',
      'calories' => 410,
      'protein_g' => 28,
      'carbs_g' => 36,
      'fat_g' => 17,
      'fiber_g' => 7,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?w=600&q=80',
      'ingredients' => ['2 slices whole wheat bread', '4 slices turkey breast', '1/2 avocado, sliced', 'Mustard'],
      'instructions' => 'Spread mustard on whole wheat bread.\nLayer sliced turkey breast, ripe avocado slices, lettuce, and tomato.',
      'tags' => ['lunch', 'high protein']
    ],
    [
      'id' => 123,
      'title' => 'Vegan Lentil & Vegetable Soup',
      'calories' => 380,
      'protein_g' => 20,
      'carbs_g' => 54,
      'fat_g' => 4,
      'fiber_g' => 11,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80',
      'ingredients' => ['1/2 cup brown lentils', '1 chopped carrot', '1 celery stalk', '2 cups vegetable broth'],
      'instructions' => 'Simmer brown lentils, chopped carrots, celery, onions, tomatoes, and herbs in vegetable broth.\nServe hot with a whole wheat roll.',
      'tags' => ['lunch', 'vegan', 'vegetarian']
    ],
    [
      'id' => 124,
      'title' => 'Crispy Tofu Buddha Bowl',
      'calories' => 520,
      'protein_g' => 22,
      'carbs_g' => 58,
      'fat_g' => 24,
      'fiber_g' => 12,
      'servings' => 1,
      'readyInMinutes' => 30,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['1/2 block extra-firm tofu', '1/2 cup brown rice', '1/2 cup steamed broccoli', '2 tbsp peanut dressing'],
      'instructions' => 'Bake extra-firm tofu cubes until crispy.\nCook brown rice.\nArrange tofu and broccoli in a bowl.\nDrizzle with a savory peanut-ginger dressing.',
      'tags' => ['lunch', 'vegan', 'vegetarian']
    ],
    [
      'id' => 125,
      'title' => 'Keto Chicken Bacon Ranch Salad',
      'calories' => 480,
      'protein_g' => 38,
      'carbs_g' => 8,
      'fat_g' => 32,
      'fiber_g' => 3,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['5 oz grilled chicken', '2 strips cooked bacon', '2 cups mixed greens', '2 tbsp ranch dressing'],
      'instructions' => 'Chop grilled chicken and crispy bacon.\nToss with mixed greens, cherry tomatoes, and cucumber.\nDress generously with ranch dressing.',
      'tags' => ['lunch', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 126,
      'title' => 'Vegan Black Bean & Corn Salad',
      'calories' => 350,
      'protein_g' => 14,
      'carbs_g' => 55,
      'fat_g' => 8,
      'fiber_g' => 15,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['1/2 cup black beans', '1/2 cup sweet corn', '1/4 diced red onion', 'Lime juice'],
      'instructions' => 'Rinse black beans and corn.\nToss with diced red onion, cilantro, and fresh lime juice.\nServe chilled.',
      'tags' => ['lunch', 'vegan', 'vegetarian', 'gluten-free']
    ],
    [
      'id' => 127,
      'title' => 'Spicy Shrimp Tacos',
      'calories' => 410,
      'protein_g' => 28,
      'carbs_g' => 42,
      'fat_g' => 14,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1565557613262-d27a1f59235e?w=600&q=80',
      'ingredients' => ['5 oz shrimp', '2 corn tortillas', '1/4 cup shredded cabbage', '1 tbsp spicy mayo'],
      'instructions' => 'Sauté shrimp in chili powder and garlic.\nWarm corn tortillas.\nFill with shrimp, shredded cabbage, and drizzle with spicy mayo.',
      'tags' => ['lunch', 'high protein']
    ],
    [
      'id' => 128,
      'title' => 'Egg Salad Lettuce Wraps',
      'calories' => 320,
      'protein_g' => 18,
      'carbs_g' => 6,
      'fat_g' => 24,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['3 hard-boiled eggs', '1 tbsp mayo', '1 tsp mustard', '3 large lettuce leaves'],
      'instructions' => 'Chop hard-boiled eggs and mix with mayo, mustard, salt, and pepper.\nSpoon egg salad into fresh lettuce leaves and wrap.',
      'tags' => ['lunch', 'keto', 'low carb', 'vegetarian']
    ],
    [
      'id' => 129,
      'title' => 'Zucchini Noodles with Pesto & Tomatoes',
      'calories' => 290,
      'protein_g' => 8,
      'carbs_g' => 18,
      'fat_g' => 22,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80',
      'ingredients' => ['2 medium zucchinis, spiralized', '2 tbsp basil pesto', '1/2 cup cherry tomatoes'],
      'instructions' => 'Spiralize zucchinis into noodles.\nLightly sauté noodles for 2 minutes.\nToss with basil pesto and halved cherry tomatoes.',
      'tags' => ['lunch', 'vegetarian', 'keto', 'low carb']
    ],
    [
      'id' => 130,
      'title' => 'Roast Beef & Provolone Wrap',
      'calories' => 490,
      'protein_g' => 35,
      'carbs_g' => 38,
      'fat_g' => 22,
      'fiber_g' => 3,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80',
      'ingredients' => ['1 whole wheat wrap', '4 slices deli roast beef', '1 slice provolone cheese', '1 tbsp horseradish mayo'],
      'instructions' => 'Lay out the wrap.\nSpread horseradish mayo.\nLayer roast beef, provolone, and mixed greens, then roll tightly.',
      'tags' => ['lunch', 'high protein']
    ],
    [
      'id' => 131,
      'title' => 'Classic Beef Steak with Roasted Asparagus',
      'calories' => 610,
      'protein_g' => 45,
      'carbs_g' => 12,
      'fat_g' => 38,
      'fiber_g' => 4,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=600&q=80',
      'ingredients' => ['8 oz sirloin steak', '1 bunch asparagus', '1 tbsp butter', 'Garlic powder'],
      'instructions' => 'Season steak with salt, pepper, and garlic powder.\nSear in a hot cast-iron skillet with butter.\nRoast asparagus in the oven at 400°F with olive oil.',
      'tags' => ['dinner', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 132,
      'title' => 'Vegan Dal Tadka with Brown Rice',
      'calories' => 560,
      'protein_g' => 24,
      'carbs_g' => 78,
      'fat_g' => 12,
      'fiber_g' => 14,
      'servings' => 1,
      'readyInMinutes' => 30,
      'image' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=600&q=80',
      'ingredients' => ['1/2 cup yellow lentils', '1/2 cup brown rice', '1/2 onion, chopped', '1 tsp cumin seeds'],
      'instructions' => 'Cook yellow lentils with onions, tomatoes, and spices.\nTemper (tadka) with cumin, garlic, and oil.\nServe with brown rice.',
      'tags' => ['dinner', 'vegan', 'vegetarian', 'indian']
    ],
    [
      'id' => 133,
      'title' => 'Baked Lemon Herb Salmon',
      'calories' => 580,
      'protein_g' => 40,
      'carbs_g' => 42,
      'fat_g' => 24,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 30,
      'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&q=80',
      'ingredients' => ['6 oz salmon fillet', '1 sweet potato', '1 lemon, sliced', 'Fresh dill'],
      'instructions' => 'Bake salmon fillet with lemon slices, dill, and olive oil.\nSteam sweet potatoes and mash with a touch of milk, salt, and pepper.',
      'tags' => ['dinner', 'gluten-free', 'high protein']
    ],
    [
      'id' => 134,
      'title' => 'Whole Wheat Pasta with Tofu & Pesto',
      'calories' => 540,
      'protein_g' => 26,
      'carbs_g' => 64,
      'fat_g' => 16,
      'fiber_g' => 8,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80',
      'ingredients' => ['2 oz whole wheat pasta', '1/3 block firm tofu', '2 tbsp basil pesto'],
      'instructions' => 'Boil whole wheat pasta.\nPan-fry cubed tofu until golden.\nToss pasta, tofu, and basil pesto together.',
      'tags' => ['dinner', 'vegetarian']
    ],
    [
      'id' => 135,
      'title' => 'Shrimp & Chicken Jambalaya',
      'calories' => 490,
      'protein_g' => 38,
      'carbs_g' => 18,
      'fat_g' => 22,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 35,
      'image' => 'https://images.unsplash.com/photo-1565557613262-d27a1f59235e?w=600&q=80',
      'ingredients' => ['4 oz chicken breast', '4 oz shrimp', '1/2 cup cauliflower rice', 'Cajun seasoning'],
      'instructions' => 'Sauté chicken, shrimp, bell peppers, onions, celery, and Cajun seasoning.\nAdd cauliflower rice and stir until heated through.',
      'tags' => ['dinner', 'low carb', 'high protein']
    ],
    [
      'id' => 136,
      'title' => 'Teriyaki Chicken with Jasmine Rice',
      'calories' => 530,
      'protein_g' => 38,
      'carbs_g' => 62,
      'fat_g' => 11,
      'fiber_g' => 4,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=600&q=80',
      'ingredients' => ['6 oz chicken breast', '2 tbsp teriyaki sauce', '1/2 cup jasmine rice', '1 cup broccoli'],
      'instructions' => 'Sauté cubed chicken breast in teriyaki sauce.\nServe over steamed jasmine rice and broccoli florets.',
      'tags' => ['dinner', 'high protein']
    ],
    [
      'id' => 137,
      'title' => 'Baked Cod with Quinoa',
      'calories' => 460,
      'protein_g' => 34,
      'carbs_g' => 48,
      'fat_g' => 12,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80',
      'ingredients' => ['6 oz cod fillet', '1/2 cup cooked quinoa', '1 zucchini, sliced', 'Paprika'],
      'instructions' => 'Bake wild caught cod with olive oil, garlic, and paprika.\nServe with cooked quinoa and roasted zucchini slices.',
      'tags' => ['dinner', 'gluten-free', 'high protein']
    ],
    [
      'id' => 138,
      'title' => 'Vegan Coconut Tofu Curry',
      'calories' => 520,
      'protein_g' => 18,
      'carbs_g' => 68,
      'fat_g' => 19,
      'fiber_g' => 8,
      'servings' => 1,
      'readyInMinutes' => 30,
      'image' => 'https://images.unsplash.com/photo-1565557613262-d27a1f59235e?w=600&q=80',
      'ingredients' => ['1/2 block firm tofu', '1/4 cup coconut milk', '1 tbsp curry paste', '1/2 cup basmati rice'],
      'instructions' => 'Sauté tofu cubes, bell peppers, and peas in a light coconut curry sauce.\nServe with hot steamed basmati rice.',
      'tags' => ['dinner', 'vegan', 'vegetarian']
    ],
    [
      'id' => 139,
      'title' => 'Keto Garlic Butter Pork Chops',
      'calories' => 550,
      'protein_g' => 42,
      'carbs_g' => 6,
      'fat_g' => 38,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 20,
      'image' => 'https://images.unsplash.com/photo-1432139555190-58524dae6a55?w=600&q=80',
      'ingredients' => ['1 bone-in pork chop', '1 tbsp garlic butter', '1 cup green beans'],
      'instructions' => 'Season pork chop with salt and pepper.\nSear in a hot pan, then baste with garlic butter until cooked through.\nServe with steamed green beans.',
      'tags' => ['dinner', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 140,
      'title' => 'Spaghetti Bolognese with Lean Beef',
      'calories' => 590,
      'protein_g' => 36,
      'carbs_g' => 72,
      'fat_g' => 16,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 35,
      'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80',
      'ingredients' => ['4 oz lean ground beef', '2 oz whole wheat spaghetti', '1/2 cup marinara sauce', 'Parmesan cheese'],
      'instructions' => 'Brown ground beef in a skillet.\nAdd marinara sauce and simmer.\nServe over cooked spaghetti and top with parmesan.',
      'tags' => ['dinner', 'high protein']
    ],
    [
      'id' => 141,
      'title' => 'Vegan Stuffed Bell Peppers',
      'calories' => 410,
      'protein_g' => 15,
      'carbs_g' => 65,
      'fat_g' => 10,
      'fiber_g' => 12,
      'servings' => 1,
      'readyInMinutes' => 40,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['2 bell peppers', '1/2 cup black beans', '1/2 cup brown rice', '1/4 cup salsa'],
      'instructions' => 'Cut tops off peppers and remove seeds.\nMix black beans, rice, and salsa. Stuff into peppers.\nBake at 375°F for 30 minutes.',
      'tags' => ['dinner', 'vegan', 'vegetarian']
    ],
    [
      'id' => 142,
      'title' => 'Keto Chicken Parmesan',
      'calories' => 520,
      'protein_g' => 55,
      'carbs_g' => 12,
      'fat_g' => 26,
      'fiber_g' => 4,
      'servings' => 1,
      'readyInMinutes' => 30,
      'image' => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=600&q=80',
      'ingredients' => ['6 oz chicken breast', '1/4 cup almond flour', '1/4 cup marinara sauce', '1/4 cup mozzarella'],
      'instructions' => 'Coat chicken in almond flour and pan-fry.\nTop with marinara and mozzarella, then broil until cheese melts.\nServe with a side salad.',
      'tags' => ['dinner', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 143,
      'title' => 'Roasted Vegetable Lasagna',
      'calories' => 480,
      'protein_g' => 22,
      'carbs_g' => 55,
      'fat_g' => 18,
      'fiber_g' => 8,
      'servings' => 1,
      'readyInMinutes' => 45,
      'image' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600&q=80',
      'ingredients' => ['Lasagna noodles', 'Ricotta cheese', 'Roasted zucchini & eggplant', 'Marinara sauce'],
      'instructions' => 'Layer noodles, ricotta, roasted veggies, and marinara in a dish.\nBake at 375°F for 35 minutes.\nLet cool slightly before serving.',
      'tags' => ['dinner', 'vegetarian']
    ],
    [
      'id' => 144,
      'title' => 'Turkey Meatballs with Zucchini Noodles',
      'calories' => 430,
      'protein_g' => 38,
      'carbs_g' => 22,
      'fat_g' => 20,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=600&q=80',
      'ingredients' => ['4 oz ground turkey', '1 zucchini, spiralized', '1/2 cup tomato sauce', 'Garlic and herbs'],
      'instructions' => 'Form turkey into meatballs and pan-fry.\nSimmer in tomato sauce.\nServe hot over raw or lightly sautéed zucchini noodles.',
      'tags' => ['dinner', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 145,
      'title' => 'Vegan Chickpea & Spinach Stew',
      'calories' => 390,
      'protein_g' => 16,
      'carbs_g' => 62,
      'fat_g' => 8,
      'fiber_g' => 14,
      'servings' => 1,
      'readyInMinutes' => 25,
      'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80',
      'ingredients' => ['1 cup chickpeas', '2 cups fresh spinach', '1/2 cup diced tomatoes', '1 tsp cumin'],
      'instructions' => 'Sauté onions and cumin.\nAdd chickpeas and diced tomatoes, simmering for 15 minutes.\nStir in fresh spinach until wilted and serve.',
      'tags' => ['dinner', 'vegan', 'vegetarian']
    ],
    [
      'id' => 146,
      'title' => 'Mixed Berry Protein Shake',
      'calories' => 220,
      'protein_g' => 26,
      'carbs_g' => 18,
      'fat_g' => 3,
      'fiber_g' => 4,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=600&q=80',
      'ingredients' => ['1 scoop vanilla whey protein', '1/2 cup mixed berries', '1 cup almond milk'],
      'instructions' => 'Blend vanilla protein powder, mixed berries, and unsweetened almond milk until smooth.',
      'tags' => ['snack', 'high protein', 'vegetarian']
    ],
    [
      'id' => 147,
      'title' => 'Vegan Banana Almond Smoothie',
      'calories' => 290,
      'protein_g' => 8,
      'carbs_g' => 42,
      'fat_g' => 12,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1528498033373-3c6c08e17129?w=600&q=80',
      'ingredients' => ['1 banana', '1 tbsp almond butter', '1 cup oat milk', 'Ice'],
      'instructions' => 'Blend banana, almond butter, oat milk, and ice until thick.',
      'tags' => ['snack', 'vegan', 'vegetarian']
    ],
    [
      'id' => 148,
      'title' => 'Hummus & Cucumber Slices',
      'calories' => 120,
      'protein_g' => 4,
      'carbs_g' => 12,
      'fat_g' => 6,
      'fiber_g' => 3,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1625944230945-1b7dd12ce240?w=600&q=80',
      'ingredients' => ['3 tbsp hummus', '1/2 cucumber, sliced'],
      'instructions' => 'Serve classic creamy hummus with crunchy fresh cucumber rounds.',
      'tags' => ['snack', 'vegan', 'vegetarian', 'keto', 'low carb']
    ],
    [
      'id' => 149,
      'title' => 'Cottage Cheese with Strawberries',
      'calories' => 160,
      'protein_g' => 14,
      'carbs_g' => 18,
      'fat_g' => 4,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1555505019-8c3f1c4aba5f?w=600&q=80',
      'ingredients' => ['1/2 cup cottage cheese', '1/2 cup strawberries', '1 tsp honey'],
      'instructions' => 'Scoop low-fat cottage cheese.\nTop with fresh strawberry slices and a drizzle of honey.',
      'tags' => ['snack', 'vegetarian', 'high protein']
    ],
    [
      'id' => 150,
      'title' => 'Rice Cakes with Peanut Butter',
      'calories' => 190,
      'protein_g' => 6,
      'carbs_g' => 22,
      'fat_g' => 9,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1620953158022-77727e02e1b9?w=600&q=80',
      'ingredients' => ['2 brown rice cakes', '1 tbsp natural peanut butter', 'Drizzle of honey'],
      'instructions' => 'Spread smooth natural peanut butter over puffed brown rice cakes.\nDrizzle light honey.',
      'tags' => ['snack', 'vegetarian']
    ],
    [
      'id' => 151,
      'title' => 'Dark Chocolate & Almonds',
      'calories' => 210,
      'protein_g' => 5,
      'carbs_g' => 14,
      'fat_g' => 16,
      'fiber_g' => 3,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=600&q=80',
      'ingredients' => ['2 squares 85% dark chocolate', '1 oz raw almonds'],
      'instructions' => 'Enjoy dark chocolate alongside a small handful of raw unsalted almonds.',
      'tags' => ['snack', 'vegan', 'vegetarian', 'keto']
    ],
    [
      'id' => 152,
      'title' => 'Edamame Beans with Sea Salt',
      'calories' => 150,
      'protein_g' => 14,
      'carbs_g' => 12,
      'fat_g' => 6,
      'fiber_g' => 6,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&q=80',
      'ingredients' => ['1 cup edamame pods', 'Pinch of coarse sea salt'],
      'instructions' => 'Steam edamame pods in the microwave or stovetop.\nToss generously with coarse sea salt.',
      'tags' => ['snack', 'vegan', 'vegetarian', 'high protein', 'low carb']
    ],
    [
      'id' => 153,
      'title' => 'Greek Yogurt with Walnuts',
      'calories' => 240,
      'protein_g' => 16,
      'carbs_g' => 10,
      'fat_g' => 16,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1481391243146-5e913a0c0e5a?w=600&q=80',
      'ingredients' => ['1 cup plain Greek yogurt', '1 oz chopped walnuts'],
      'instructions' => 'Spoon plain Greek yogurt into a bowl.\nTop with crushed walnuts for crunch and healthy fats.',
      'tags' => ['snack', 'vegetarian', 'high protein', 'keto']
    ],
    [
      'id' => 154,
      'title' => 'Hard-Boiled Eggs with Everything Bagel Seasoning',
      'calories' => 140,
      'protein_g' => 12,
      'carbs_g' => 1,
      'fat_g' => 10,
      'fiber_g' => 0,
      'servings' => 1,
      'readyInMinutes' => 10,
      'image' => 'https://images.unsplash.com/photo-1587486913049-53fc88980cfc?w=600&q=80',
      'ingredients' => ['2 large eggs', '1 tsp Everything Bagel seasoning'],
      'instructions' => 'Boil eggs for 8-10 minutes.\nPeel, slice in half, and sprinkle heavily with Everything Bagel seasoning.',
      'tags' => ['snack', 'vegetarian', 'keto', 'low carb', 'high protein']
    ],
    [
      'id' => 155,
      'title' => 'Apple Slices with Almond Butter',
      'calories' => 220,
      'protein_g' => 4,
      'carbs_g' => 28,
      'fat_g' => 12,
      'fiber_g' => 5,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6fac6?w=600&q=80',
      'ingredients' => ['1 medium apple', '1.5 tbsp almond butter'],
      'instructions' => 'Core and slice the apple.\nDip slices into almond butter.',
      'tags' => ['snack', 'vegan', 'vegetarian']
    ],
    [
      'id' => 156,
      'title' => 'Celery & Cream Cheese Boats',
      'calories' => 130,
      'protein_g' => 3,
      'carbs_g' => 4,
      'fat_g' => 11,
      'fiber_g' => 1,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1604543519968-3e5f1f1d1fb8?w=600&q=80',
      'ingredients' => ['3 celery stalks', '2 tbsp cream cheese'],
      'instructions' => 'Wash and cut celery stalks.\nFill the celery sticks with cream cheese.',
      'tags' => ['snack', 'vegetarian', 'keto', 'low carb']
    ],
    [
      'id' => 157,
      'title' => 'Roasted Pumpkin Seeds',
      'calories' => 170,
      'protein_g' => 9,
      'carbs_g' => 4,
      'fat_g' => 14,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 15,
      'image' => 'https://images.unsplash.com/photo-1508061461528-ce15f91753c1?w=600&q=80',
      'ingredients' => ['1/4 cup pumpkin seeds', 'Olive oil spray', 'Sea salt'],
      'instructions' => 'Toss pumpkin seeds with light olive oil spray and salt.\nRoast in the oven at 350°F until crunchy.',
      'tags' => ['snack', 'vegan', 'vegetarian', 'keto', 'low carb']
    ],
    [
      'id' => 158,
      'title' => 'String Cheese & Cherry Tomatoes',
      'calories' => 120,
      'protein_g' => 8,
      'carbs_g' => 6,
      'fat_g' => 7,
      'fiber_g' => 2,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&q=80',
      'ingredients' => ['1 mozzarella string cheese', '1 cup cherry tomatoes'],
      'instructions' => 'Enjoy string cheese alongside fresh, sweet cherry tomatoes.',
      'tags' => ['snack', 'vegetarian', 'keto', 'low carb']
    ],
    [
      'id' => 159,
      'title' => 'Protein Bar',
      'calories' => 200,
      'protein_g' => 20,
      'carbs_g' => 22,
      'fat_g' => 7,
      'fiber_g' => 14,
      'servings' => 1,
      'readyInMinutes' => 1,
      'image' => 'https://images.unsplash.com/photo-1579722820308-d74e571900a9?w=600&q=80',
      'ingredients' => ['1 low-sugar protein bar'],
      'instructions' => 'Unwrap and enjoy on the go.',
      'tags' => ['snack', 'vegetarian', 'high protein']
    ],
    [
      'id' => 160,
      'title' => 'Guacamole with Carrot Sticks',
      'calories' => 180,
      'protein_g' => 3,
      'carbs_g' => 16,
      'fat_g' => 14,
      'fiber_g' => 8,
      'servings' => 1,
      'readyInMinutes' => 5,
      'image' => 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=600&q=80',
      'ingredients' => ['1/4 cup guacamole', '1 cup baby carrots'],
      'instructions' => 'Scoop fresh guacamole using crunchy baby carrots instead of tortilla chips.',
      'tags' => ['snack', 'vegan', 'vegetarian', 'low carb']
    ]
  ];

  private static $foods = [
    // --- BREAKFAST RAW FOODS (15 entries) ---
    ['fdcId' => 200001, 'description' => "Egg, whole, raw, fresh, large", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 50, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 143, 1003 => 12.6, 1005 => 0.7, 1004 => 9.5, 1079 => 0.0, 2000 => 0.7, 1093 => 140]],
    ['fdcId' => 200002, 'description' => "Greek Yogurt, plain, low-fat, unsweetened", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 73, 1003 => 10.0, 1005 => 3.6, 1004 => 2.0, 1079 => 0.0, 2000 => 3.6, 1093 => 36]],
    ['fdcId' => 200003, 'description' => "Oats, rolled, quick-cooking, dry", 'dataType' => "SR Legacy", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 379, 1003 => 13.2, 1005 => 67.7, 1004 => 6.5, 1079 => 10.1, 2000 => 1.0, 1093 => 2]],
    ['fdcId' => 200004, 'description' => "Granola, low-sugar, organic fruit & nut blend", 'dataType' => "Branded", 'brandOwner' => "Nature's Path", 'servingSize' => 55, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 240, 1003 => 6.0, 1005 => 37.0, 1004 => 8.0, 1079 => 5.0, 2000 => 7.0, 1093 => 45]],
    ['fdcId' => 200005, 'description' => "Strawberries, fresh, organic", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 32, 1003 => 0.7, 1005 => 7.7, 1004 => 0.3, 1079 => 2.0, 2000 => 4.9, 1093 => 1]],
    ['fdcId' => 200006, 'description' => "Blueberries, fresh, organic", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 57, 1003 => 0.7, 1005 => 14.5, 1004 => 0.3, 1079 => 2.4, 2000 => 10.0, 1093 => 1]],
    ['fdcId' => 200007, 'description' => "Whole milk, raw, pasture-raised", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 244, 'servingSizeUnit' => "ml", 'nutrients' => [1008 => 149, 1003 => 7.7, 1005 => 11.7, 1004 => 8.0, 1079 => 0.0, 2000 => 12.0, 1093 => 105]],
    ['fdcId' => 200008, 'description' => "Almond milk, unsweetened, fortified", 'dataType' => "Branded", 'brandOwner' => "Blue Diamond", 'servingSize' => 240, 'servingSizeUnit' => "ml", 'nutrients' => [1008 => 30, 1003 => 1.0, 1005 => 1.0, 1004 => 2.5, 1079 => 1.0, 2000 => 0.0, 1093 => 160]],
    ['fdcId' => 200009, 'description' => "Corn flakes cereal, toasted", 'dataType' => "Branded", 'brandOwner' => "Kellogg's", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 100, 1003 => 2.0, 1005 => 24.0, 1004 => 0.0, 1079 => 1.0, 2000 => 3.0, 1093 => 200]],
    ['fdcId' => 200010, 'description' => "Cottage cheese, low-fat, plain", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 113, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 92, 1003 => 12.4, 1005 => 3.0, 1004 => 3.0, 1079 => 0.0, 2000 => 3.0, 1093 => 400]],
    ['fdcId' => 200011, 'description' => "Egg whites, liquid, pasteurized", 'dataType' => "Branded", 'brandOwner' => "Egg Beaters", 'servingSize' => 46, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 25, 1003 => 5.0, 1005 => 0.0, 1004 => 0.0, 1079 => 0.0, 2000 => 0.0, 1093 => 75]],
    ['fdcId' => 200012, 'description' => "Chia seeds, organic, dry", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 138, 1003 => 4.7, 1005 => 11.9, 1004 => 8.7, 1079 => 9.8, 2000 => 0.0, 1093 => 5]],
    ['fdcId' => 200013, 'description' => "Peanut butter, smooth, natural", 'dataType' => "Branded", 'brandOwner' => "Smucker's", 'servingSize' => 32, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 190, 1003 => 8.0, 1005 => 6.0, 1004 => 16.0, 1079 => 2.0, 2000 => 1.0, 1093 => 110]],
    ['fdcId' => 200014, 'description' => "Whole wheat bread, toasted slice", 'dataType' => "Branded", 'brandOwner' => "Arnold", 'servingSize' => 38, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 90, 1003 => 4.0, 1005 => 18.0, 1004 => 1.0, 1079 => 3.0, 2000 => 2.0, 1093 => 140]],
    ['fdcId' => 200015, 'description' => "Honey, organic, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 21, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 64, 1003 => 0.0, 1005 => 17.0, 1004 => 0.0, 1079 => 0.0, 2000 => 17.0, 1093 => 1]],

    // --- LUNCH RAW FOODS (15 entries) ---
    ['fdcId' => 200016, 'description' => "Chicken breast, boneless, skinless, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 120, 1003 => 22.5, 1005 => 0.0, 1004 => 2.6, 1079 => 0.0, 2000 => 0.0, 1093 => 45]],
    ['fdcId' => 200017, 'description' => "Turkey breast, oven-roasted, sliced deli meat", 'dataType' => "Branded", 'brandOwner' => "Hillshire Farm", 'servingSize' => 56, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 60, 1003 => 10.0, 1005 => 2.0, 1004 => 1.0, 1079 => 0.0, 2000 => 1.0, 1093 => 520]],
    ['fdcId' => 200018, 'description' => "Paneer, Indian cottage cheese, organic", 'dataType' => "Branded", 'brandOwner' => "Gopi Dairy", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 90, 1003 => 6.0, 1005 => 1.0, 1004 => 7.0, 1079 => 0.0, 2000 => 1.0, 1093 => 10]],
    ['fdcId' => 200019, 'description' => "Avocado, Hass, fresh, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 160, 1003 => 2.0, 1005 => 8.5, 1004 => 14.7, 1079 => 6.7, 2000 => 0.7, 1093 => 7]],
    ['fdcId' => 200020, 'description' => "Romaine lettuce, fresh crisp leaves", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 17, 1003 => 1.2, 1005 => 3.3, 1004 => 0.3, 1079 => 2.1, 2000 => 1.2, 1093 => 8]],
    ['fdcId' => 200021, 'description' => "Cherry tomatoes, sweet red, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 18, 1003 => 0.9, 1005 => 3.9, 1004 => 0.2, 1079 => 1.2, 2000 => 2.6, 1093 => 5]],
    ['fdcId' => 200022, 'description' => "Cucumber, organic, raw with peel", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 15, 1003 => 0.7, 1005 => 3.6, 1004 => 0.1, 1079 => 0.5, 2000 => 1.7, 1093 => 2]],
    ['fdcId' => 200023, 'description' => "Hummus, classic garbanzo spread", 'dataType' => "Branded", 'brandOwner' => "Sabra", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 70, 1003 => 2.0, 1005 => 4.0, 1004 => 5.0, 1079 => 1.0, 2000 => 0.0, 1093 => 130]],
    ['fdcId' => 200024, 'description' => "Spinach, baby leaves, raw, organic", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 23, 1003 => 2.9, 1005 => 3.6, 1004 => 0.4, 1079 => 2.2, 2000 => 0.4, 1093 => 79]],
    ['fdcId' => 200025, 'description' => "Whole wheat wrap tortilla", 'dataType' => "Branded", 'brandOwner' => "Mission", 'servingSize' => 49, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 130, 1003 => 4.0, 1005 => 22.0, 1004 => 3.0, 1079 => 3.0, 2000 => 1.0, 1093 => 260]],
    ['fdcId' => 200026, 'description' => "Quinoa, white grain, dry organic", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 368, 1003 => 14.1, 1005 => 64.2, 1004 => 6.1, 1079 => 7.0, 2000 => 0.0, 1093 => 5]],
    ['fdcId' => 200027, 'description' => "Canned chickpeas, drained, low sodium", 'dataType' => "Branded", 'brandOwner' => "Goya", 'servingSize' => 130, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 120, 1003 => 6.0, 1005 => 20.0, 1004 => 2.0, 1079 => 6.0, 2000 => 1.0, 1093 => 140]],
    ['fdcId' => 200028, 'description' => "Olive oil, extra virgin", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 14, 'servingSizeUnit' => "ml", 'nutrients' => [1008 => 119, 1003 => 0.0, 1005 => 0.0, 1004 => 13.5, 1079 => 0.0, 2000 => 0.0, 1093 => 0]],
    ['fdcId' => 200029, 'description' => "Feta cheese, crumbled block", 'dataType' => "Branded", 'brandOwner' => "President", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 75, 1003 => 4.0, 1005 => 1.0, 1004 => 6.0, 1079 => 0.0, 2000 => 0.0, 1093 => 320]],
    ['fdcId' => 200030, 'description' => "Tuna, canned in water, drained", 'dataType' => "Branded", 'brandOwner' => "StarKist", 'servingSize' => 56, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 50, 1003 => 11.0, 1005 => 0.0, 1004 => 0.5, 1079 => 0.0, 2000 => 0.0, 1093 => 180]],

    // --- DINNER RAW FOODS (16 entries) ---
    ['fdcId' => 200031, 'description' => "Salmon, Atlantic, wild, raw fillet", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 142, 1003 => 19.8, 1005 => 0.0, 1004 => 6.3, 1079 => 0.0, 2000 => 0.0, 1093 => 44]],
    ['fdcId' => 200032, 'description' => "Sirloin steak, beef, lean, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 190, 1003 => 22.0, 1005 => 0.0, 1004 => 11.0, 1079 => 0.0, 2000 => 0.0, 1093 => 54]],
    ['fdcId' => 200033, 'description' => "Tofu, organic, firm, fresh", 'dataType' => "Branded", 'brandOwner' => "House Foods", 'servingSize' => 85, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 70, 1003 => 8.0, 1005 => 2.0, 1004 => 4.0, 1079 => 1.0, 2000 => 0.0, 1093 => 10]],
    ['fdcId' => 200034, 'description' => "Brown rice, long-grain, raw", 'dataType' => "SR Legacy", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 362, 1003 => 7.5, 1005 => 76.2, 1004 => 2.7, 1079 => 3.4, 2000 => 0.9, 1093 => 4]],
    ['fdcId' => 200035, 'description' => "White rice, basmati, raw", 'dataType' => "SR Legacy", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 356, 1003 => 7.0, 1005 => 78.0, 1004 => 0.6, 1079 => 1.0, 2000 => 0.0, 1093 => 0]],
    ['fdcId' => 200036, 'description' => "Yellow split lentils, toor dal, dry", 'dataType' => "SR Legacy", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 343, 1003 => 22.0, 1005 => 62.0, 1004 => 1.5, 1079 => 15.0, 2000 => 1.5, 1093 => 15]],
    ['fdcId' => 200037, 'description' => "Whole wheat spaghetti pasta", 'dataType' => "Branded", 'brandOwner' => "Barilla", 'servingSize' => 56, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 180, 1003 => 7.0, 1005 => 39.0, 1004 => 1.0, 1079 => 6.0, 2000 => 1.0, 1093 => 0]],
    ['fdcId' => 200038, 'description' => "Sweet potato, raw, orange flesh", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 86, 1003 => 1.6, 1005 => 20.1, 1004 => 0.1, 1079 => 3.0, 2000 => 4.2, 1093 => 55]],
    ['fdcId' => 200039, 'description' => "Shrimp, raw, peeled & deveined", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 85, 1003 => 20.1, 1005 => 0.2, 1004 => 0.5, 1079 => 0.0, 2000 => 0.0, 1093 => 111]],
    ['fdcId' => 200040, 'description' => "Broccoli florets, fresh, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 34, 1003 => 2.8, 1005 => 6.6, 1004 => 0.4, 1079 => 2.6, 2000 => 1.7, 1093 => 33]],
    ['fdcId' => 200041, 'description' => "Asparagus spears, fresh, green", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 20, 1003 => 2.2, 1005 => 3.9, 1004 => 0.1, 1079 => 2.1, 2000 => 1.9, 1093 => 2]],
    ['fdcId' => 200042, 'description' => "Cod fish fillet, wild caught, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 82, 1003 => 17.8, 1005 => 0.0, 1004 => 0.7, 1079 => 0.0, 2000 => 0.0, 1093 => 54]],
    ['fdcId' => 200043, 'description' => "Red lentils, masoor dal, dry", 'dataType' => "SR Legacy", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 350, 1003 => 24.0, 1005 => 60.0, 1004 => 1.0, 1079 => 10.0, 2000 => 1.0, 1093 => 10]],
    ['fdcId' => 200044, 'description' => "Coconut milk, organic light canned", 'dataType' => "Branded", 'brandOwner' => "Thai Kitchen", 'servingSize' => 80, 'servingSizeUnit' => "ml", 'nutrients' => [1008 => 50, 1003 => 0.5, 1005 => 1.0, 1004 => 5.0, 1079 => 0.0, 2000 => 0.0, 1093 => 15]],
    ['fdcId' => 200045, 'description' => "Cauliflower rice, fresh grated", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 25, 1003 => 1.9, 1005 => 5.0, 1004 => 0.3, 1079 => 2.0, 2000 => 1.9, 1093 => 30]],
    ['fdcId' => 200046, 'description' => "Beef flank steak, lean, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 165, 1003 => 21.4, 1005 => 0.0, 1004 => 8.0, 1079 => 0.0, 2000 => 0.0, 1093 => 56]],

    // --- SNACK RAW FOODS (15 entries) ---
    ['fdcId' => 200047, 'description' => "Apple, red delicious, raw, with skin", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 52, 1003 => 0.3, 1005 => 13.8, 1004 => 0.2, 1079 => 2.4, 2000 => 10.4, 1093 => 1]],
    ['fdcId' => 200048, 'description' => "Almonds, raw, whole, unsalted", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 162, 1003 => 6.0, 1005 => 6.0, 1004 => 14.0, 1079 => 3.5, 2000 => 1.2, 1093 => 0]],
    ['fdcId' => 200049, 'description' => "Cashews, raw, halves, unsalted", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 155, 1003 => 5.0, 1005 => 9.0, 1004 => 12.0, 1079 => 1.0, 2000 => 1.6, 1093 => 5]],
    ['fdcId' => 200050, 'description' => "Walnuts, English halves, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 185, 1003 => 4.3, 1005 => 3.9, 1004 => 18.5, 1079 => 1.9, 2000 => 0.7, 1093 => 0]],
    ['fdcId' => 200051, 'description' => "Mixed berries, strawberries & raspberries", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 43, 1003 => 1.0, 1005 => 10.0, 1004 => 0.3, 1079 => 5.0, 2000 => 4.4, 1093 => 1]],
    ['fdcId' => 200052, 'description' => "Protein bar, whey vanilla, low sugar", 'dataType' => "Branded", 'brandOwner' => "Quest Nutrition", 'servingSize' => 60, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 200, 1003 => 21.0, 1005 => 21.0, 1004 => 7.0, 1079 => 14.0, 2000 => 1.0, 1093 => 240]],
    ['fdcId' => 200053, 'description' => "Dark chocolate, 85% cocoa block", 'dataType' => "Branded", 'brandOwner' => "Lindt", 'servingSize' => 40, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 250, 1003 => 4.0, 1005 => 15.0, 1004 => 22.0, 1079 => 6.0, 2000 => 4.0, 1093 => 10]],
    ['fdcId' => 200054, 'description' => "Rice cakes, puffed brown rice", 'dataType' => "Branded", 'brandOwner' => "Quaker", 'servingSize' => 9, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 35, 1003 => 1.0, 1005 => 7.0, 1004 => 0.0, 1079 => 0.0, 2000 => 0.0, 1093 => 15]],
    ['fdcId' => 200055, 'description' => "Protein powder, whey isolate vanilla", 'dataType' => "Branded", 'brandOwner' => "Optimum Nutrition", 'servingSize' => 31, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 120, 1003 => 24.0, 1005 => 3.0, 1004 => 1.0, 1079 => 0.0, 2000 => 1.0, 1093 => 100]],
    ['fdcId' => 200056, 'description' => "Air-popped popcorn, unsalted", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 387, 1003 => 12.9, 1005 => 77.9, 1004 => 4.5, 1079 => 14.5, 2000 => 0.8, 1093 => 8]],
    ['fdcId' => 200057, 'description' => "Baby carrots, raw, sweet", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 41, 1003 => 0.9, 1005 => 9.6, 1004 => 0.2, 1079 => 2.8, 2000 => 4.7, 1093 => 78]],
    ['fdcId' => 200058, 'description' => "Pistachio nuts, shell removed, dry roasted", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 28, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 160, 1003 => 6.0, 1005 => 8.0, 1004 => 13.0, 1079 => 3.0, 2000 => 2.0, 1093 => 0]],
    ['fdcId' => 200059, 'description' => "Raisins, seedless, dark dry", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 40, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 120, 1003 => 1.0, 1005 => 32.0, 1004 => 0.2, 1079 => 2.0, 2000 => 26.0, 1093 => 4]],
    ['fdcId' => 200060, 'description' => "Celery sticks, fresh crisp, raw", 'dataType' => "Foundation", 'brandOwner' => "", 'servingSize' => 100, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 16, 1003 => 0.7, 1005 => 3.0, 1004 => 0.2, 1079 => 1.6, 2000 => 1.3, 1093 => 80]],
    ['fdcId' => 200061, 'description' => "Peanut butter powder, defatted", 'dataType' => "Branded", 'brandOwner' => "PB2", 'servingSize' => 13, 'servingSizeUnit' => "g", 'nutrients' => [1008 => 60, 1003 => 6.0, 1005 => 5.0, 1004 => 1.5, 1079 => 2.0, 2000 => 2.0, 1093 => 90]]
  ];

  public static function getRecipes(string $query, int $maxCalories = 0, string $diet = 'anything'): array {
    $q = strtolower(urldecode($query));
    $matched = [];

    $queryWords = preg_split('/[\s,\+]+/', $q, -1, PREG_SPLIT_NO_EMPTY);

    foreach (self::$recipes as $r) {
      if ($maxCalories > 0 && $r['calories'] > $maxCalories) {
        continue;
      }

      if ($diet !== 'anything') {
        if (!in_array(strtolower($diet), $r['tags'])) {
          continue;
        }
      }

      $match = empty($queryWords);
      foreach ($queryWords as $word) {
        if (strpos(strtolower($r['title']), $word) !== false || in_array($word, $r['tags'])) {
          $match = true;
          break;
        }
      }

      if ($match) {
        $nutrients = [
          ['name' => 'Calories', 'amount' => $r['calories']],
          ['name' => 'Protein', 'amount' => $r['protein_g']],
          ['name' => 'Carbohydrates', 'amount' => $r['carbs_g']],
          ['name' => 'Fat', 'amount' => $r['fat_g']],
          ['name' => 'Fiber', 'amount' => $r['fiber_g']]
        ];

        $extendedIngredients = [];
        if (isset($r['ingredients'])) {
          foreach ($r['ingredients'] as $ing) {
            $extendedIngredients[] = ['original' => $ing];
          }
        }
        
        $instructionsRaw = str_replace('\n', "\n", $r['instructions']);
        $steps = array_filter(array_map('trim', explode("\n", $instructionsRaw)));

        $matched[] = [
          'id' => $r['id'],
          'title' => $r['title'],
          'image' => $r['image'],
          'servings' => $r['servings'],
          'readyInMinutes' => $r['readyInMinutes'],
          'instructions' => implode(" ", $steps),
          'extendedIngredients' => $extendedIngredients,
          'analyzedInstructions' => [
            [
              'steps' => array_map(function($stepText, $idx) {
                return ['number' => $idx + 1, 'step' => $stepText];
              }, $steps, array_keys($steps))
            ]
          ],
          'nutrition' => [
            'nutrients' => $nutrients
          ]
        ];
      }
    }

    if (empty($matched)) {
      foreach (self::$recipes as $r) {
        if ($maxCalories > 0 && $r['calories'] > $maxCalories) continue;
        if ($diet !== 'anything' && !in_array(strtolower($diet), $r['tags'])) continue;
        
        $nutrients = [
          ['name' => 'Calories', 'amount' => $r['calories']],
          ['name' => 'Protein', 'amount' => $r['protein_g']],
          ['name' => 'Carbohydrates', 'amount' => $r['carbs_g']],
          ['name' => 'Fat', 'amount' => $r['fat_g']],
          ['name' => 'Fiber', 'amount' => $r['fiber_g']]
        ];

        $extendedIngredients = [];
        if (isset($r['ingredients'])) {
          foreach ($r['ingredients'] as $ing) {
            $extendedIngredients[] = ['original' => $ing];
          }
        }
        
        $instructionsRaw = str_replace('\n', "\n", $r['instructions']);
        $steps = array_filter(array_map('trim', explode("\n", $instructionsRaw)));

        $matched[] = [
          'id' => $r['id'],
          'title' => $r['title'],
          'image' => $r['image'],
          'servings' => $r['servings'],
          'readyInMinutes' => $r['readyInMinutes'],
          'instructions' => implode(" ", $steps),
          'extendedIngredients' => $extendedIngredients,
          'analyzedInstructions' => [
            [
              'steps' => array_map(function($stepText, $idx) {
                return ['number' => $idx + 1, 'step' => $stepText];
              }, $steps, array_keys($steps))
            ]
          ],
          'nutrition' => [
            'nutrients' => $nutrients
          ]
        ];
      }
    }

    return ['results' => $matched];
  }

  public static function getRecipeById(int $id): array {
    foreach (self::$recipes as $r) {
      if ($r['id'] === $id) {
        $nutrients = [
          ['name' => 'Calories', 'amount' => $r['calories']],
          ['name' => 'Protein', 'amount' => $r['protein_g']],
          ['name' => 'Carbohydrates', 'amount' => $r['carbs_g']],
          ['name' => 'Fat', 'amount' => $r['fat_g']],
          ['name' => 'Fiber', 'amount' => $r['fiber_g']]
        ];

        $extendedIngredients = [];
        if (isset($r['ingredients'])) {
          foreach ($r['ingredients'] as $ing) {
            $extendedIngredients[] = ['original' => $ing];
          }
        }
        
        $instructionsRaw = str_replace('\n', "\n", $r['instructions']);
        $steps = array_filter(array_map('trim', explode("\n", $instructionsRaw)));

        return [
          'id' => $r['id'],
          'title' => $r['title'],
          'image' => $r['image'],
          'servings' => $r['servings'],
          'readyInMinutes' => $r['readyInMinutes'],
          'instructions' => implode(" ", $steps),
          'analyzedInstructions' => [
            [
              'steps' => array_map(function($stepText, $idx) {
                return ['number' => $idx + 1, 'step' => $stepText];
              }, $steps, array_keys($steps))
            ]
          ],
          'extendedIngredients' => $extendedIngredients,
          'nutrition' => [
            'nutrients' => $nutrients
          ]
        ];
      }
    }
    throw new Exception('Recipe not found locally');
  }

  public static function getFoods(string $query, int $pageSize = 15): array {
    $q = strtolower(urldecode($query));
    $matched = [];

    $queryWords = preg_split('/[\s,\+]+/', $q, -1, PREG_SPLIT_NO_EMPTY);

    foreach (self::$foods as $f) {
      $match = empty($queryWords);
      foreach ($queryWords as $word) {
        if (strpos(strtolower($f['description']), $word) !== false) {
          $match = true;
          break;
        }
      }

      if ($match) {
        $nutrientsList = [];
        foreach ($f['nutrients'] as $nutId => $val) {
          $nutrientsList[] = [
            'nutrientId' => $nutId,
            'value' => $val
          ];
        }

        $matched[] = [
          'fdcId' => $f['fdcId'],
          'description' => $f['description'],
          'dataType' => $f['dataType'],
          'brandOwner' => $f['brandOwner'],
          'servingSize' => $f['servingSize'],
          'servingSizeUnit' => $f['servingSizeUnit'],
          'foodNutrients' => $nutrientsList
        ];
      }

      if (count($matched) >= $pageSize) break;
    }

    if (empty($matched)) {
      foreach (array_slice(self::$foods, 0, $pageSize) as $f) {
        $nutrientsList = [];
        foreach ($f['nutrients'] as $nutId => $val) {
          $nutrientsList[] = ['nutrientId' => $nutId, 'value' => $val];
        }
        $matched[] = [
          'fdcId' => $f['fdcId'],
          'description' => $f['description'],
          'dataType' => $f['dataType'],
          'brandOwner' => $f['brandOwner'],
          'servingSize' => $f['servingSize'],
          'servingSizeUnit' => $f['servingSizeUnit'],
          'foodNutrients' => $nutrientsList
        ];
      }
    }

    return [
      'totalHits' => count($matched),
      'foods' => $matched
    ];
  }

  public static function getFoodById(int $fdcId): array {
    foreach (self::$foods as $f) {
      if ($f['fdcId'] === $fdcId) {
        $nutrientsList = [];
        foreach ($f['nutrients'] as $nutId => $val) {
          $nutrientsList[] = ['nutrientId' => $nutId, 'value' => $val];
        }
        return [
          'fdcId' => $f['fdcId'],
          'description' => $f['description'],
          'dataType' => $f['dataType'],
          'brandOwner' => $f['brandOwner'],
          'servingSize' => $f['servingSize'],
          'servingSizeUnit' => $f['servingSizeUnit'],
          'foodNutrients' => $nutrientsList
        ];
      }
    }
    throw new Exception('Food not found locally');
  }
}
