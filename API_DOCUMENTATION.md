# API DOCUMENTATION

## Backend (PHP Laravel 12)

### Base URL: `http://localhost:8000/api`

### 1. Authentication
- `POST /auth/register`
  - **Payload**: `{ "name": "John", "email": "john@test.com", "password": "pass" }`
  - **Returns**: JWT Token + User object.
- `POST /auth/login`
  - **Payload**: `{ "email": "john@test.com", "password": "pass" }`
  - **Returns**: JWT Token.

### 2. Reports
- `POST /reports/upload`
  - **Headers**: `Authorization: Bearer <token>`, `Content-Type: multipart/form-data`
  - **Payload**: `report` (File)
  - **Action**: Saves file, sends to Python AI for analysis, stores results.
  - **Returns**: Analysis results and Risk Assessment.
- `GET /reports`
  - **Returns**: List of user's past reports.

### 3. Meals
- `GET /meals/recommendations`
  - **Headers**: `Authorization: Bearer <token>`
  - **Action**: Fetches RAG recommendations based on latest risk profile.
  - **Returns**: JSON array of meals.

---

## AI Microservice (Python FastAPI)

### Base URL: `http://localhost:8001/api/ai`

### 1. Analyze Report
- `POST /analyze-report`
  - **Payload**: `file` (Image)
  - **Action**: Runs OCR and ML Inference.
  - **Returns**: Extracted metrics and predicted risks.

### 2. Recommend Meals (RAG)
- `POST /recommend-meals`
  - **Payload**: `{ "medical_data": {}, "allergies": [], "favorites": [] }`
  - **Action**: Queries local vector DB and local LLM.
  - **Returns**: Structured meal plan.
