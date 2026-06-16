# MODEL EVALUATION REPORT

*Note: This report will be fully populated once the dataset extraction via OCR is completed and the initial XGBoost model training is executed. The metrics below serve as the target thresholds for deployment.*

## 1. Diabetes Risk Model (XGBoost)
- **Target Accuracy**: > 92%
- **Target ROC-AUC**: > 0.95
- **Key Features**: HbA1c, Glucose, BMI (if available), Age.
- **Evaluation Priority**: High Recall (minimizing false negatives is critical for early diabetes detection).

## 2. Heart Disease Risk Model (XGBoost)
- **Target Accuracy**: > 88%
- **Target ROC-AUC**: > 0.90
- **Key Features**: LDL, HDL, Total Cholesterol, Triglycerides, Blood Pressure.
- **Evaluation Priority**: Balanced F1-Score.

## 3. Kidney Disease Risk Model (XGBoost)
- **Target Accuracy**: > 90%
- **Target ROC-AUC**: > 0.93
- **Key Features**: eGFR, Creatinine, Urea.
- **Evaluation Priority**: High Precision (avoiding false alarms for chronic conditions).

## 4. Thyroid Risk Model (Random Forest / XGBoost)
- **Target Accuracy**: > 94%
- **Target ROC-AUC**: > 0.96
- **Key Features**: TSH, T3, T4.
- **Evaluation Priority**: High Recall.

## Continuous Monitoring
Once models are deployed, their predictions will be logged and periodically evaluated against actual patient outcomes (Feedback Loop) to track **Data Drift** and trigger automatic retraining pipelines when Accuracy drops below the target thresholds.
