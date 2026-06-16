# OCR IMPLEMENTATION GUIDE

## Overview
The OCR (Optical Character Recognition) module is responsible for reading uploaded medical reports (PNG, JPG, PDF) and extracting actionable numerical biomarkers to feed into the Machine Learning prediction models.

## Technology Stack
- **Engine**: EasyOCR (Preferred for ease of setup) / PaddleOCR (Alternative for high accuracy).
- **Image Processing**: OpenCV, Pillow (PIL).
- **Pattern Matching**: Python `re` (Regular Expressions).

## Pipeline Steps

### 1. Image Enhancement (Pre-processing)
Before passing the image to the OCR engine, we enhance the image to improve text detection accuracy:
- **Grayscale Conversion**: Removes color noise.
- **Binarization**: Applies an adaptive threshold to make text stand out against the background.
- **Noise Reduction**: Gaussian Blur to remove artifacts.

### 2. Text Detection & Extraction
The pre-processed image is passed to `easyocr.Reader(['en'])`. The reader returns bounding boxes and the detected text. We concatenate the text into a single contiguous string.

### 3. Medical Entity Extraction (Regex)
We use highly specific regular expressions to find biomarkers and their corresponding values. 

**Example Regex Patterns**:
- Glucose: `(?i)glucose[^0-9]*?([\d\.]+)`
- HbA1c: `(?i)hba1c[^0-9]*?([\d\.]+)`
- Cholesterol: `(?i)cholesterol[^0-9]*?([\d\.]+)`

### 4. Normalization
The extracted string values are cast to floating-point numbers (`float()`). If a value cannot be found or parsed, it is recorded as `None` (Null) and will be handled by the ML pipeline's imputation logic.

## Output Format
The module outputs a structured JSON object:
```json
{
  "glucose": 105.5,
  "hba1c": 5.8,
  "cholesterol": 180.0,
  "hdl": 50.0,
  "ldl": 110.0,
  "creatinine": 0.9,
  "tsh": 2.5
}
```
This JSON is sent to the Risk Prediction module and returned to the Laravel backend for database storage.
