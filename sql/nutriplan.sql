CREATE DATABASE IF NOT EXISTS nutriplan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nutriplan;

-- USERS
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- USER PROFILE (health data)
CREATE TABLE IF NOT EXISTS user_profiles (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL UNIQUE,
  age             INT DEFAULT 25,
  gender          ENUM('male','female') DEFAULT 'male',
  weight_kg       DECIMAL(5,2) DEFAULT 70.00,
  height_cm       DECIMAL(5,2) DEFAULT 170.00,
  activity_level  ENUM('sedentary','light','moderate','active','very_active') DEFAULT 'moderate',
  goal            ENUM('lose','maintain','gain') DEFAULT 'maintain',
  diet_type       ENUM('anything','vegetarian','vegan','keto','paleo') DEFAULT 'anything',
  bmr             INT DEFAULT 1600,
  tdee            INT DEFAULT 2400,
  daily_calories  INT DEFAULT 2400,
  protein_g       INT DEFAULT 180,
  carbs_g         INT DEFAULT 240,
  fat_g           INT DEFAULT 80,
  allergies       VARCHAR(255) DEFAULT '',
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- MEAL PLANS (Updated for Spoonacular Recipes)
CREATE TABLE IF NOT EXISTS meal_plans (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  plan_date   DATE NOT NULL,
  meal_slot   ENUM('breakfast','lunch','dinner','snack') NOT NULL,
  fdc_id      BIGINT, -- Used for USDA FDC ID or Spoonacular Recipe ID
  food_name   VARCHAR(255),
  calories    INT,
  protein_g   DECIMAL(6,2),
  carbs_g     DECIMAL(6,2),
  fat_g       DECIMAL(6,2),
  fiber_g     DECIMAL(6,2),
  serving_size VARCHAR(100),
  image_url   VARCHAR(500) DEFAULT NULL,
  instructions TEXT DEFAULT NULL,
  is_locked   TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_slot (user_id, plan_date, meal_slot),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- FOOD LOG (what user actually ate)
CREATE TABLE IF NOT EXISTS food_log (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  log_date    DATE NOT NULL,
  fdc_id      BIGINT,
  food_name   VARCHAR(255),
  calories    INT,
  protein_g   DECIMAL(6,2),
  carbs_g     DECIMAL(6,2),
  fat_g       DECIMAL(6,2),
  quantity    DECIMAL(6,2) DEFAULT 1.00,
  unit        VARCHAR(50) DEFAULT 'serving',
  logged_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- GROCERY LIST
CREATE TABLE IF NOT EXISTS grocery_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  week_start  DATE NOT NULL,
  food_name   VARCHAR(255),
  quantity    VARCHAR(100),
  category    ENUM('produce','protein','dairy','grains','pantry','other') DEFAULT 'other',
  is_checked  TINYINT(1) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- FAVORITE FOODS
CREATE TABLE IF NOT EXISTS favorites (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  fdc_id     BIGINT NOT NULL,
  food_name  VARCHAR(255),
  calories   INT,
  saved_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_fav (user_id, fdc_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
