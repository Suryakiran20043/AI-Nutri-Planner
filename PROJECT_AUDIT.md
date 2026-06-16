# PROJECT AUDIT REPORT

## Current Architecture
The current Nutri-Planner platform follows a monolithic-lite architecture:
- **Frontend**: Vanilla HTML/CSS/JS tightly coupled with PHP scripts (`index.php`, `pages/*.php`).
- **Backend API**: PHP scripts in the `api/` directory serving requests.
- **Database**: MySQL database (`nutriplan`) for users, profiles, meal plans, and logs.
- **AI Microservice**: A Python FastAPI service (`ai_service/`) handling document uploads, text extraction (PyMuPDF, Tesseract, EasyOCR), and heavily relying on the **Gemini API** (`google-generativeai`) for natural language understanding and meal recommendation generation.

## Existing Issues & Technical Debt
- **Gemini Dependency**: Core value proposition relies entirely on a third-party LLM API (Gemini), posing data privacy concerns for healthcare data and lack of local control.
- **Outdated Tech Stack**: PHP for backend logic and vanilla JS for frontend is not suited for a highly scalable, reactive enterprise healthcare platform.
- **Scalability**: Lack of containerization (Docker/Kubernetes), microservices separation, and caching (Redis) limits horizontal scalability.
- **Monolithic DB**: Single database without advanced partitioning, making it a bottleneck for 1M+ users.
- **No Local ML**: Absence of local predictive models for health risks (Diabetes, Heart Disease, etc.).

## Security Gaps
- **Authentication**: Basic session-based auth; lacking robust JWT token mechanisms and RBAC (Role-Based Access Control).
- **Data Privacy**: Medical reports and personal health information (PHI) are not encrypted at rest.
- **Audit Trails**: Missing comprehensive audit logging for medical record access and modifications.

## Refactoring Strategy
1. **Frontend Modernization**: Migrate from PHP templates to a standalone React application (Vite or Next.js) with responsive, premium UI components.
2. **Backend Overhaul**: Replace the PHP backend with a robust Java Spring Boot microservices architecture.
3. **AI Transformation**: Completely remove `google-generativeai`. Implement a local, open-source OCR pipeline (PaddleOCR/EasyOCR) and train local ML models using XGBoost/LightGBM for risk prediction.
4. **RAG Integration**: Deploy Qdrant/ChromaDB with local embeddings for context-aware, guideline-based nutrition recommendations.
5. **DevOps & Cloud**: Containerize all services using Docker and orchestrate with Kubernetes. Deploy on AWS (EKS, RDS, S3).
