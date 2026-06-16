# DATASET AUDIT REPORT

## Dataset Location
`C:\Users\vishnu\Desktop\Nutri-Planner\Medical repo\archive (3)\lbmaske`

## Dataset Inventory
Upon traversing the provided dataset directory, the following file distributions were found:
- **CSV Files**: 0
- **JSON Files**: 0
- **XLSX Files**: 0
- **PDF Reports**: 0
- **Medical Images (PNG)**: 426
- **TXT Files**: 0

*Note*: The dataset consists entirely of scanned medical report images (PNG format). There are no pre-structured CSV or JSON files.

## Dataset Statistics
- **Total Number of Files**: 426
- **Total Size**: ~820 MB
- **Format**: PNG images of medical lab reports.
- **Missing Values/Duplicate Records**: Cannot be determined prior to OCR extraction, as the data is currently unstructured image data.

## Healthcare Features Found (To Be Extracted)
Since the dataset is purely image-based, we must build an OCR extraction pipeline to parse these images and construct a tabular dataset containing the following features required for the predictive models:

- **Metabolic & Diabetic Markers**: Glucose, HbA1c
- **Lipid Profile**: Cholesterol, HDL, LDL, Triglycerides
- **Kidney Function**: Creatinine, Urea, eGFR
- **Thyroid Profile**: TSH, T3, T4
- **Vitamins**: Vitamin D, Vitamin B12
- **Vitals (if present in reports)**: BMI, Blood Pressure

## Next Steps
No model training can begin until the data is structured. 
1. We must prioritize building the **OCR Pipeline** (PaddleOCR/EasyOCR).
2. Run the 426 PNG images through the OCR engine.
3. Extract medical entities (Key-Value pairs) using regex or NLP (e.g., local spaCy models).
4. Normalize and output the extracted data into a `dataset.csv` for ML training.
