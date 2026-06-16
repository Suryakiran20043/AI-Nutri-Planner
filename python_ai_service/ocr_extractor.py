import os
import easyocr
import pandas as pd
import re

DATASET_PATH = r"C:\Users\vishnu\Desktop\Nutri-Planner\Medical repo\archive (3)\lbmaske"
OUTPUT_CSV = r"C:\Users\vishnu\Desktop\Nutri-Planner\AI-Nutri-Planner\python_ai_service\extracted_medical_data.csv"

reader = easyocr.Reader(['en'], gpu=False)  # Set gpu=True if CUDA is available

features = {
    "glucose": r"(?i)glucose[^0-9]*?([\d\.]+)",
    "hba1c": r"(?i)hba1c[^0-9]*?([\d\.]+)",
    "cholesterol": r"(?i)cholesterol[^0-9]*?([\d\.]+)",
    "hdl": r"(?i)hdl[^0-9]*?([\d\.]+)",
    "ldl": r"(?i)ldl[^0-9]*?([\d\.]+)",
    "triglycerides": r"(?i)triglycerides[^0-9]*?([\d\.]+)",
    "creatinine": r"(?i)creatinine[^0-9]*?([\d\.]+)",
    "urea": r"(?i)urea[^0-9]*?([\d\.]+)",
    "egfr": r"(?i)egfr[^0-9]*?([\d\.]+)",
    "tsh": r"(?i)tsh[^0-9]*?([\d\.]+)",
    "t3": r"(?i)t3[^0-9]*?([\d\.]+)",
    "t4": r"(?i)t4[^0-9]*?([\d\.]+)",
    "vitamin_d": r"(?i)vitamin\s*d[^0-9]*?([\d\.]+)",
    "vitamin_b12": r"(?i)vitamin\s*b12[^0-9]*?([\d\.]+)"
}

extracted_data = []

# For the sake of execution time, we will process a subset of images first (first 20)
# to prove the pipeline works, then we can run the rest in a background job later.
files = [f for f in os.listdir(DATASET_PATH) if f.endswith('.png')][:20]

print(f"Starting OCR extraction on {len(files)} files...")

for file_name in files:
    file_path = os.path.join(DATASET_PATH, file_name)
    try:
        results = reader.readtext(file_path, detail=0)
        full_text = " ".join(results)
        
        record = {"filename": file_name}
        for feature, pattern in features.items():
            match = re.search(pattern, full_text)
            if match:
                try:
                    record[feature] = float(match.group(1))
                except ValueError:
                    record[feature] = None
            else:
                record[feature] = None
                
        extracted_data.append(record)
        print(f"Processed: {file_name}")
    except Exception as e:
        print(f"Error processing {file_name}: {e}")

df = pd.DataFrame(extracted_data)
df.to_csv(OUTPUT_CSV, index=False)
print(f"Extraction complete! Saved to {OUTPUT_CSV}")
