import os
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    # API Configurations
    PROJECT_NAME: str = "AI Nutri-Planner Health Parser"
    API_V1_STR: str = "/api/v1"
    
    # Security Key for API calls (FastAPI <-> PHP Backend validation)
    API_SECURITY_KEY: str = os.getenv("API_SECURITY_KEY", "nutriplan_secure_communication_key_2026")
    
    # Upload Directories
    UPLOAD_DIR: str = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "uploads"))
    
    DB_HOST: str = os.getenv("DB_HOST", "localhost")
    DB_NAME: str = os.getenv("DB_NAME", "nutriplan")
    DB_USER: str = os.getenv("DB_USER", "root")
    DB_PASS: str = os.getenv("DB_PASS", "Vishnu@1234")  # Set via environment variable or .env file
    
    # External API Keys (Default to Demo or empty)
    SPOONACULAR_API_KEY: str = os.getenv("SPOONACULAR_API_KEY", "")
    SPOONACULAR_BASE_URL: str = "https://api.spoonacular.com"
    USDA_API_KEY: str = os.getenv("USDA_API_KEY", "DEMO_KEY")
    USDA_BASE_URL: str = "https://api.nal.usda.gov/fdc/v1"
    GEMINI_API_KEY: str = os.getenv("GEMINI_API_KEY", "")  # Set via environment variable or .env file
    
    # OCR Settings
    # Supports: "tesseract", "easyocr", or "mock" (for testing/development fallback)
    OCR_BACKEND: str = os.getenv("OCR_BACKEND", "easyocr")
    TESSERACT_CMD: str = os.getenv("TESSERACT_CMD", r"C:\Program Files\Tesseract-OCR\tesseract.exe")
    
    # Clinical Biomarker Reference Ranges
    # These are default normal ranges for adults
    BIOMARKER_RANGES: dict = {
        "glucose": {"min": 70.0, "max": 99.0, "unit": "mg/dL", "name": "Blood Sugar (Glucose)"},
        "ldl_cholesterol": {"min": 0.0, "max": 100.0, "unit": "mg/dL", "name": "LDL Cholesterol"},
        "hdl_cholesterol": {"min": 40.0, "max": 60.0, "unit": "mg/dL", "name": "HDL Cholesterol (Good)"},
        "total_cholesterol": {"min": 100.0, "max": 200.0, "unit": "mg/dL", "name": "Total Cholesterol"},
        "hemoglobin": {"min": 12.0, "max": 17.5, "unit": "g/dL", "name": "Hemoglobin (Iron Indicator)"},
        "vitamin_d": {"min": 30.0, "max": 100.0, "unit": "ng/mL", "name": "Vitamin D"},
        "vitamin_b12": {"min": 200.0, "max": 900.0, "unit": "pg/mL", "name": "Vitamin B12"},
        "systolic_bp": {"min": 90.0, "max": 120.0, "unit": "mmHg", "name": "Systolic Blood Pressure"},
        "diastolic_bp": {"min": 60.0, "max": 80.0, "unit": "mmHg", "name": "Diastolic Blood Pressure"},
        "platelet_count": {"min": 150.0, "max": 450.0, "unit": "10^3/µL", "name": "Platelet Count"},
        "mcv": {"min": 80.0, "max": 100.0, "unit": "fL", "name": "Mean Cell Volume (MCV)"},
        "mch": {"min": 27.0, "max": 33.0, "unit": "pg", "name": "Mean Cell Hemoglobin (MCH)"},
        "mchc": {"min": 32.0, "max": 36.0, "unit": "g/dL", "name": "Mean Cell Hemoglobin Concentration (MCHC)"},
        "rdw": {"min": 11.0, "max": 15.0, "unit": "%", "name": "Red Cell Distribution Width (RDW)"},
        "neutrophils": {"min": 40.0, "max": 75.0, "unit": "%", "name": "Neutrophils"},
        "lymphocytes": {"min": 20.0, "max": 45.0, "unit": "%", "name": "Lymphocytes"},
        "eosinophils": {"min": 1.0, "max": 6.0, "unit": "%", "name": "Eosinophils"},
        "monocytes": {"min": 2.0, "max": 10.0, "unit": "%", "name": "Monocytes"},
        "basophils": {"min": 0.0, "max": 2.0, "unit": "%", "name": "Basophils"},
    }

    class Config:
        env_file = ".env"
        case_sensitive = True

settings = Settings()

# Ensure uploads folder exists
os.makedirs(settings.UPLOAD_DIR, exist_ok=True)
