# SYSTEM ARCHITECTURE

## Overview
The Nutri-Planner Enterprise AI ecosystem is a highly modular, decoupled platform designed for scalability, security, and medical data processing accuracy. The architecture consists of three core components:
1. **Frontend**: Vanilla JS client
2. **Backend**: PHP Laravel 12 API Layer
3. **AI Service**: Python FastAPI Microservice

## System Components

### 1. Frontend Layer (Vanilla JS)
- **Technology**: HTML5, CSS3, Vanilla ES6 JavaScript, Bootstrap 5.
- **Responsibility**: Provides the user interface for patient login, report uploads, and meal plan dashboard.
- **Communication**: Interacts exclusively with the Laravel Backend via REST APIs.

### 2. Backend API Layer (PHP Laravel 12)
- **Architecture**: MVC, Service Layer, Repository Pattern.
- **Responsibility**: Acts as the central orchestrator and data gateway.
  - Handles User Authentication (JWT).
  - Validates and stores incoming file uploads.
  - Forwards medical images to the AI Service.
  - Receives and persists structured ML predictions and RAG recommendations to the MySQL database.
  - Serves aggregated data to the Frontend.

### 3. AI Intelligence Layer (Python FastAPI)
- **Technology**: Python 3.10+, FastAPI, EasyOCR, XGBoost, ChromaDB/Qdrant.
- **Responsibility**: Performs all heavy ML computation.
  - **OCR Module**: Extracts biomarkers (Glucose, HbA1c, Lipids, etc.) from PNG/PDF reports.
  - **Risk Prediction Module**: Uses trained XGBoost models to calculate disease risk metrics based on extracted data.
  - **RAG Engine**: Queries the local vector database of nutrition guidelines considering user allergies, favorites, and medical restrictions to output a highly personalized meal plan.

### 4. Database Layer (MySQL 8)
- **Technology**: MySQL 8+
- **Responsibility**: Central source of truth for users, raw uploaded files (references), structured medical metrics, ML predictions, and final meal recommendations.

## Data Flow
`User -> Frontend -> (REST API) -> Laravel -> (Internal API) -> Python AI -> (Returns JSON) -> Laravel -> (Stores in MySQL & returns to Frontend)`
