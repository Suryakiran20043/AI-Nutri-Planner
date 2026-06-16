# MACHINE LEARNING PIPELINE

## Overview
This document outlines the pipeline used to predict medical risks (Diabetes, Heart Disease, Kidney Disease, Thyroid Risk) based on biomarkers extracted via OCR from user-uploaded medical reports. The pipeline is built using Python 3.10+, pandas, scikit-learn, and XGBoost, and hosted within the FastAPI AI microservice.

## 1. Data Collection & Preprocessing
- **Source**: 426 PNG medical reports located at `archive (3)/lbmaske`.
- **Extraction**: The `ocr_extractor.py` script uses EasyOCR/PaddleOCR to detect text and regex patterns to capture numerical values for specific biomarkers (e.g., Glucose, HbA1c, Cholesterol).
- **Normalization**: 
  - Missing values are imputed using median values for specific demographics.
  - Outliers are handled using IQR (Interquartile Range) capping.
  - Features are scaled using `StandardScaler`.

## 2. Feature Engineering
The following features are synthesized to improve model accuracy:
- **Metabolic Ratio**: Triglycerides / HDL (strong predictor for insulin resistance).
- **Kidney Function Indicator**: Urea-to-Creatinine Ratio.
- **Lipid Risk Index**: Non-HDL Cholesterol (Total Cholesterol - HDL).

## 3. Model Training
We train four independent models. The primary algorithm of choice is **XGBoost** due to its superior performance on tabular data with non-linear relationships. 
*Alternative models trained for comparison*: LightGBM, Random Forest, Logistic Regression.

### Training Strategy:
- **Validation**: 5-Fold Cross-Validation.
- **Hyperparameter Tuning**: GridSearchCV over `max_depth`, `learning_rate`, `n_estimators`, and `subsample`.
- **Evaluation Metrics**: ROC-AUC, F1-Score, Precision, and Recall.

## 4. Model Registry & Versioning
Trained models are saved locally in the `python_ai_service/models/` directory using `joblib` or `pickle`.
- `models/diabetes_xgb_v1.pkl`
- `models/heart_xgb_v1.pkl`
- `models/kidney_xgb_v1.pkl`
- `models/thyroid_xgb_v1.pkl`

## 5. Inference Pipeline
When a user uploads a new report:
1. The report is passed to the FastAPI endpoint `/api/ai/analyze-report`.
2. OCR extracts the raw biomarkers.
3. The data is formatted into a NumPy array and scaled using the saved `scaler.pkl`.
4. The four XGBoost models generate Risk Scores (0-100%).
5. Results are returned to the Laravel backend as a JSON response.
