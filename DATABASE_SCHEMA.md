# DATABASE SCHEMA (MySQL 8)

## Core Tables

### 1. `users`
- `id` (PK, INT)
- `name` (VARCHAR)
- `email` (VARCHAR, UNIQUE)
- `password_hash` (VARCHAR)
- `allergies` (JSON) - e.g., ["peanuts", "dairy"]
- `favorites` (JSON) - e.g., ["chicken", "broccoli"]
- `created_at` (TIMESTAMP)

### 2. `health_reports`
- `id` (PK, INT)
- `user_id` (FK, INT)
- `file_path` (VARCHAR) - Path to stored PNG/PDF
- `status` (ENUM: pending, processed, failed)
- `uploaded_at` (TIMESTAMP)

### 3. `medical_metrics`
- `id` (PK, INT)
- `report_id` (FK, INT)
- `user_id` (FK, INT)
- `glucose` (DECIMAL)
- `hba1c` (DECIMAL)
- `cholesterol` (DECIMAL)
- `hdl` (DECIMAL)
- `ldl` (DECIMAL)
- `triglycerides` (DECIMAL)
- `creatinine` (DECIMAL)
- `urea` (DECIMAL)
- `egfr` (DECIMAL)
- `tsh` (DECIMAL)
- `extracted_at` (TIMESTAMP)

### 4. `disease_predictions` (Risk Assessments)
- `id` (PK, INT)
- `report_id` (FK, INT)
- `user_id` (FK, INT)
- `diabetes_risk_score` (DECIMAL)
- `heart_disease_risk_score` (DECIMAL)
- `kidney_disease_risk_score` (DECIMAL)
- `thyroid_risk_score` (DECIMAL)
- `calculated_at` (TIMESTAMP)

### 5. `meal_recommendations`
- `id` (PK, INT)
- `user_id` (FK, INT)
- `report_id` (FK, INT)
- `plan_date` (DATE)
- `meal_slot` (ENUM: breakfast, lunch, dinner, snack)
- `food_name` (VARCHAR)
- `reason` (TEXT) - Explains why this was recommended by RAG
- `is_favorite_match` (BOOLEAN)
- `created_at` (TIMESTAMP)

## Optimization Strategies
- **Indexing**: B-Tree indexes on `user_id`, `report_id`, and `email`.
- **JSON Types**: Storing variable preferences like `allergies` and `favorites` as JSON for flexible querying without excessive mapping tables.
- **Foreign Keys**: Enforcing ON DELETE CASCADE to ensure referential integrity when a user is deleted.
