import os
import re
import csv
import cv2
import easyocr
from rapidfuzz import fuzz

def extract_tabular_data(ocr_results):
    items = []
    for bbox, text, conf in ocr_results:
        y_center = (bbox[0][1] + bbox[2][1]) / 2
        x_center = (bbox[0][0] + bbox[2][0]) / 2
        items.append({'y': y_center, 'x': x_center, 'text': text})
        
    items.sort(key=lambda item: item['y'])
    lines = []
    if not items: return lines
        
    current_line = [items[0]]
    y_threshold = 15.0  
    
    for item in items[1:]:
        avg_y = sum(i['y'] for i in current_line) / len(current_line)
        if abs(item['y'] - avg_y) <= y_threshold:
            current_line.append(item)
        else:
            current_line.sort(key=lambda i: i['x'])
            lines.append(" ".join([i['text'] for i in current_line]))
            current_line = [item]
            
    if current_line:
        current_line.sort(key=lambda i: i['x'])
        lines.append(" ".join([i['text'] for i in current_line]))
    return lines

def fuzzy_extract(text_lines, target_word):
    best_score = 0
    best_value = None
    for line in text_lines:
        line = line.lower()
        words = line.split()
        for i, w in enumerate(words):
            score = fuzz.ratio(w, target_word.lower())
            if score > 80:
                remaining = " ".join(words[i:])
                match = re.search(r'\b(\d+\.?\d*)\b', remaining)
                if match:
                    try:
                        val = float(match.group(1))
                        if score > best_score:
                            best_score = score
                            best_value = val
                    except ValueError:
                        pass
    return best_value

def generate_labels(features):
    # Medical Thresholds
    glucose = features.get('glucose') or 90.0
    hba1c = features.get('hba1c') or 5.5
    chol = features.get('cholesterol') or 180.0
    ldl = features.get('ldl') or 100.0
    trig = features.get('triglycerides') or 120.0
    creat = features.get('creatinine') or 0.9
    urea = features.get('urea') or 15.0
    egfr = features.get('egfr') or 90.0
    
    diabetes = 1 if (glucose >= 126 or hba1c >= 6.5) else 0
    heart = 1 if (chol > 240 or ldl > 160 or trig > 200) else 0
    kidney = 1 if (egfr < 60 or creat > 1.2 or urea > 40) else 0
    
    return diabetes, heart, kidney

def main():
    dataset_dir = r"C:\Users\vishnu\Desktop\Nutri-Planner\Medical repo\archive (3)\lbmaske"
    output_csv = os.path.join(os.path.dirname(__file__), "..", "biomarkers_dataset.csv")
    
    print(f"Loading easyocr reader...")
    reader = easyocr.Reader(['en'], gpu=False)
    
    image_files = [f for f in os.listdir(dataset_dir) if f.lower().endswith(('.png', '.jpg', '.jpeg'))]
    image_files = image_files[:50]
    print(f"Selected 50 images for efficient training. Starting extraction...")
    
    with open(output_csv, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['filename', 'glucose', 'hba1c', 'cholesterol', 'hdl', 'ldl', 'triglycerides', 'creatinine', 'urea', 'egfr', 'bmi', 'diabetes', 'heart_disease', 'kidney_disease'])
        
        for idx, filename in enumerate(image_files):
            img_path = os.path.join(dataset_dir, filename)
            try:
                ocr_res = reader.readtext(img_path, detail=1)
                lines = extract_tabular_data(ocr_res)
                
                features = {
                    'glucose': fuzzy_extract(lines, "glucose"),
                    'hba1c': fuzzy_extract(lines, "hba1c"),
                    'cholesterol': fuzzy_extract(lines, "cholesterol"),
                    'hdl': fuzzy_extract(lines, "hdl"),
                    'ldl': fuzzy_extract(lines, "ldl"),
                    'triglycerides': fuzzy_extract(lines, "triglycerides"),
                    'creatinine': fuzzy_extract(lines, "creatinine"),
                    'urea': fuzzy_extract(lines, "urea"),
                    'egfr': fuzzy_extract(lines, "egfr"),
                    'bmi': fuzzy_extract(lines, "bmi")
                }
                
                diabetes, heart, kidney = generate_labels(features)
                
                # Fill missing with safe defaults for training purposes
                row = [
                    filename,
                    features['glucose'] or 90.0,
                    features['hba1c'] or 5.5,
                    features['cholesterol'] or 180.0,
                    features['hdl'] or 50.0,
                    features['ldl'] or 100.0,
                    features['triglycerides'] or 120.0,
                    features['creatinine'] or 0.9,
                    features['urea'] or 15.0,
                    features['egfr'] or 90.0,
                    features['bmi'] or 24.0,
                    diabetes,
                    heart,
                    kidney
                ]
                writer.writerow(row)
                
                if (idx + 1) % 10 == 0:
                    print(f"Processed {idx + 1} / {len(image_files)}")
            except Exception as e:
                print(f"Error processing {filename}: {e}")
                
    print(f"Dataset extraction complete! Saved to {output_csv}")

if __name__ == "__main__":
    main()
