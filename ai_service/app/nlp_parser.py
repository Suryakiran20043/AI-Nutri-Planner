import re
import logging
from typing import Dict, Any, List, Optional
from app.config import settings

logger = logging.getLogger("NLPParser")

class NLPParser:
    def __init__(self):
        # Maps standard keys to lists of common synonyms found in clinical lab reports
        self.synonyms = {
            "glucose": ["glucose", "fasting glucose", "glucose, fasting", "fasting blood sugar", "fbs", "sugar"],
            "ldl_cholesterol": ["ldl cholesterol", "ldl-c", "ldl", "cholesterol, ldl", "ldl cholesterols"],
            "hdl_cholesterol": ["hdl cholesterol", "hdl-c", "hdl", "cholesterol, hdl", "hdl cholesterols"],
            "total_cholesterol": ["total cholesterol", "cholesterol, total", "total chol", "cholesterol"],
            "hemoglobin": ["hemoglobin", "hb", "hgb", "hemoglobin, total", "haemoglobin"],
            "vitamin_d": ["vitamin d", "vit d", "25-hydroxyvitamin d", "vitamin d, total", "25-oh vit d"],
            "vitamin_b12": ["vitamin b12", "vit b12", "cobalamin", "b12"],
            "systolic_bp": ["systolic bp", "systolic blood pressure", "systolic", "bp - systolic"],
            "diastolic_bp": ["diastolic bp", "diastolic blood pressure", "diastolic", "bp - diastolic"],
            "platelet_count": ["platelet count", "platelets", "plt", "platelet"],
            "mcv": ["mean cell volume", "mcv", "mean corpuscular volume"],
            "mch": ["mean cell hemoglobin", "mch", "mean corpuscular hemoglobin", "mean cell haemoglobin"],
            "mchc": ["mean cell hemoglobin concentration", "mchc", "mean corpuscular hemoglobin concentration", "mean cell haemoglobin concentration"],
            "rdw": ["red cell distribution width", "rdw", "rdw-cv", "rdw-sd"],
            "neutrophils": ["neutrophils_", "neutrophils", "neut", "polymorphs"],
            "lymphocytes": ["lymphocytes", "lymph", "lym"],
            "eosinophils": ["eosinophils", "eos"],
            "monocytes": ["monocytes", "mono"],
            "basophils": ["basophils", "baso"],
        }
        
        # Compile standard regex patterns for numerical value extraction
        # Matches patterns like: "Glucose 115 mg/dL 70-99" or "LDL: 135 (mg/dL) [Ref: 0-100]"
        self.value_pattern = re.compile(
            r"(\b\d{2,3}(?:\.\d{1,2})?)\b" # Matches value like 115 or 12.5 or 115.00
        )
        
        # Reference range patterns (e.g. "70-99", "70 - 100", "< 100", "> 30")
        self.range_pattern = re.compile(
            r"(\b\d{1,4}(?:\.\d{1,2})?\s*-\s*\d{1,4}(?:\.\d{1,2})?|\b[<>]\s*\d{1,4}(?:\.\d{1,2})?)"
        )

    def parse_report_text(self, text: str) -> Dict[str, Dict[str, Any]]:
        """
        Parses raw OCR text to extract configured biomarkers, their values,
        units, and original reference ranges.
        """
        parsed_results = {}
        lines = text.split('\n')
        
        # Pre-process lines to lowercase and strip whitespaces
        processed_lines = []
        for line in lines:
            line_str = line.strip().lower()
            if line_str:
                processed_lines.append((line, line_str))

        # First, search for Blood Pressure written as "135/85" or "120/80"
        bp_match = re.search(r"(\b\d{2,3})\s*/\s*(\b\d{2,3})\s*(?:mm\s*hg|mmhg)?", text.lower())
        if bp_match:
            sys_val = float(bp_match.group(1))
            dia_val = float(bp_match.group(2))
            
            parsed_results["systolic_bp"] = {
                "value": sys_val,
                "unit": "mmHg",
                "reference_range": "90-120",
                "matched_text": bp_match.group(0)
            }
            parsed_results["diastolic_bp"] = {
                "value": dia_val,
                "unit": "mmHg",
                "reference_range": "60-80",
                "matched_text": bp_match.group(0)
            }
            logger.info(f"Extracted Blood Pressure from slash pattern: {sys_val}/{dia_val}")

        # Scan for each biomarker
        for key, aliases in self.synonyms.items():
            # If already extracted via custom pattern (like BP), skip searching again unless missing
            if key in parsed_results:
                continue

            for original_line, lowercase_line in processed_lines:
                # Check if any alias exists in the line
                alias_matched = None
                for alias in aliases:
                    # Use boundary markers to avoid partial matches (e.g., "glucose" in "glucosed")
                    if re.search(rf"\b{re.escape(alias)}\b", lowercase_line):
                        alias_matched = alias
                        break
                
                if alias_matched:
                    # Extract values from the matching line
                    # We look for a number in the line. Let's strip the alias first to avoid matching parts of the alias
                    content_after_alias = lowercase_line.split(alias_matched, 1)[1]
                    
                    # 1. Try to extract reference range first
                    range_match = self.range_pattern.search(content_after_alias)
                    ref_range = "N/A"
                    if range_match:
                        ref_range = range_match.group(1).replace(" ", "")
                        # Remove the range text to avoid confusing it with the measured value
                        content_after_alias = content_after_alias.replace(range_match.group(0), "")
                    
                    # 2. Extract measured value (the first remaining float/int)
                    val_match = self.value_pattern.search(content_after_alias)
                    if val_match:
                        try:
                            value = float(val_match.group(1))
                            
                            # 3. Determine units
                            unit = self._detect_unit(key, lowercase_line)
                            
                            # If we don't find a range, get the standard one from config
                            if ref_range == "N/A":
                                ref_range = f"{settings.BIOMARKER_RANGES[key]['min']}-{settings.BIOMARKER_RANGES[key]['max']}"
                            
                            parsed_results[key] = {
                                "value": value,
                                "unit": unit,
                                "reference_range": ref_range,
                                "matched_text": original_line.strip()
                            }
                            logger.info(f"Extracted biomarker: {key} = {value} {unit} (Ref: {ref_range})")
                            break  # Found match for this biomarker, move to next
                        except ValueError:
                            continue
                            
        # Fill in missing metadata using settings default if not found
        for key in parsed_results:
            default_meta = settings.BIOMARKER_RANGES.get(key, {})
            if default_meta:
                parsed_results[key]["display_name"] = default_meta.get("name")
                if parsed_results[key]["unit"] == "unknown":
                    parsed_results[key]["unit"] = default_meta.get("unit")
                    
        return parsed_results

    def _detect_unit(self, biomarker_key: str, line_text: str) -> str:
        """Determines the unit of measurement from the context line."""
        line_text = line_text.lower()
        if "mg/dl" in line_text or "mgdl" in line_text:
            return "mg/dL"
        elif "g/dl" in line_text or "gdl" in line_text:
            return "g/dL"
        elif "ng/ml" in line_text or "ngml" in line_text:
            return "ng/mL"
        elif "pg/ml" in line_text or "pgml" in line_text:
            return "pg/mL"
        elif "mmhg" in line_text or "mm hg" in line_text:
            return "mmHg"
        elif "mmol/l" in line_text or "mmoll" in line_text:
            return "mmol/L"
        
        # Fallback to standard config default
        return settings.BIOMARKER_RANGES.get(biomarker_key, {}).get("unit", "unknown")
