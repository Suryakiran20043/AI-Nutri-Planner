<?php
require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';

try {
    $db = get_db();
    
    $planDate = date('Y-m-d');
    $uid = 1; // Assuming user 1 exists
    
    $mealSt = $db->prepare('
        INSERT INTO meal_plans (user_id, plan_date, meal_slot, fdc_id, food_name, calories, protein_g, carbs_g, fat_g, fiber_g, serving_size, image_url, instructions, ingredients)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            fdc_id=VALUES(fdc_id),
            food_name=VALUES(food_name),
            calories=VALUES(calories),
            protein_g=VALUES(protein_g),
            carbs_g=VALUES(carbs_g),
            fat_g=VALUES(fat_g),
            fiber_g=VALUES(fiber_g),
            serving_size=VALUES(serving_size),
            image_url=VALUES(image_url),
            instructions=VALUES(instructions),
            ingredients=VALUES(ingredients),
            is_locked=0
    ');

    $mealSt->execute([
        $uid,
        $planDate,
        'breakfast',
        1234567,
        'Test Food',
        100,
        10,
        10,
        10,
        1.0,
        '1 serving',
        null,
        'Test instructions',
        json_encode([['ingredient_name' => 'apple', 'ingredient_amount' => '1', 'ingredient_unit' => 'piece']])
    ]);

    echo "Insert successful.<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
