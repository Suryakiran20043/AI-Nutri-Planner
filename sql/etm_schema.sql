-- EatThisMuch Food Database Schema
-- Run after nutriplan.sql

USE nutriplan;

-- ETM FOODS (scraped recipe/food data)
CREATE TABLE IF NOT EXISTS etm_foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etm_id VARCHAR(50),
    slug VARCHAR(255),
    name VARCHAR(255) NOT NULL,
    food_type VARCHAR(50) DEFAULT 'recipe',
    image_url TEXT,
    description TEXT,
    servings INT DEFAULT 1,
    serving_size VARCHAR(100),
    serving_weight_grams DECIMAL(8,2),
    prep_time_minutes INT,
    cook_time_minutes INT,
    total_time_minutes INT,
    calories DECIMAL(8,2),
    total_fat DECIMAL(8,2),
    saturated_fat DECIMAL(8,2),
    trans_fat DECIMAL(8,2),
    cholesterol DECIMAL(8,2),
    sodium DECIMAL(8,2),
    total_carbs DECIMAL(8,2),
    dietary_fiber DECIMAL(8,2),
    sugar DECIMAL(8,2),
    net_carbs DECIMAL(8,2),
    protein DECIMAL(8,2),
    calcium DECIMAL(8,2),
    iron DECIMAL(8,2),
    potassium DECIMAL(8,2),
    vitamin_d DECIMAL(8,2),
    vitamin_a DECIMAL(8,2),
    vitamin_c DECIMAL(8,2),
    vitamin_b6 DECIMAL(8,2),
    vitamin_b12 DECIMAL(8,2),
    vitamin_e DECIMAL(8,2),
    vitamin_k DECIMAL(8,2),
    magnesium DECIMAL(8,2),
    zinc DECIMAL(8,2),
    phosphorus DECIMAL(8,2),
    selenium DECIMAL(8,2),
    directions JSON,
    rating DECIMAL(3,1),
    source_url VARCHAR(500),
    scraped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_etm_id (etm_id),
    INDEX idx_name (name),
    INDEX idx_calories (calories)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ETM FOOD INGREDIENTS
CREATE TABLE IF NOT EXISTS etm_food_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etm_food_id INT NOT NULL,
    ingredient_name VARCHAR(255),
    ingredient_amount VARCHAR(100),
    ingredient_image_url TEXT,
    ingredient_link VARCHAR(500),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (etm_food_id) REFERENCES etm_foods(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
