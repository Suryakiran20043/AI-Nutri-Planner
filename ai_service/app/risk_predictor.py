import logging
from typing import Dict, Any, List
from app.config import settings

logger = logging.getLogger("RiskPredictor")

class RiskPredictor:
    def __init__(self):
        self.ranges = settings.BIOMARKER_RANGES

    def analyze_biomarkers(self, parsed_data: Dict[str, Dict[str, Any]]) -> Dict[str, Any]:
        """
        Analyzes parsed biomarkers, compares values against reference ranges,
        flags abnormal levels, and predicts health risks/dietary needs.
        """
        flagged_biomarkers = {}
        health_risks = []
        dietary_rules = {
            "avoid_foods": [],
            "recommend_foods": [],
            "nutrient_targets": {} # e.g. {"sodium_mg": 2000, "fiber_g": 35}
        }
        
        # 1. Flag each biomarker
        for key, data in parsed_data.items():
            val = data["value"]
            ref_range = data["reference_range"]
            
            # Determine min/max thresholds
            min_val, max_val = self._parse_reference_range(key, ref_range)
            
            # Evaluate against range
            status = "NORMAL"
            if val < min_val:
                status = "LOW"
            elif val > max_val:
                status = "HIGH"
                
            flagged_biomarkers[key] = {
                "display_name": data.get("display_name", key.replace("_", " ").title()),
                "value": val,
                "unit": data["unit"],
                "reference_range": f"{min_val}-{max_val}",
                "status": status,
                "matched_text": data.get("matched_text", "")
            }
            
        # 2. Rule-based Health Risk & Diet Prediction
        
        # Rule A: Fasting Glucose (Pre-Diabetes / Diabetes Risk)
        glucose_data = flagged_biomarkers.get("glucose")
        if glucose_data:
            val = glucose_data["value"]
            if val >= 100.0 and val < 126.0:
                risk_pct = int((val - 100.0) / 26.0 * 20 + 60)
                health_risks.append({
                    "condition": "Prediabetes Risk",
                    "severity": "MODERATE",
                    "risk_pct": risk_pct,
                    "description": "Fasting blood sugar is elevated (100-125 mg/dL), indicating prediabetes risk.",
                    "biomarkers_affected": ["glucose"]
                })
                dietary_rules["avoid_foods"].extend(["refined sugar", "white bread", "soda", "pastries", "high-glycemic fruits"])
                dietary_rules["recommend_foods"].extend(["whole grains", "leafy greens", "avocado", "chia seeds", "legumes"])
                dietary_rules["nutrient_targets"]["carbs_limit_percent"] = 40  # limit carbohydrate ratio
            elif val >= 126.0:
                risk_pct = int(min(99, 80 + (val - 126.0) * 0.1))
                health_risks.append({
                    "condition": "Hyperglycemia / Diabetes Risk",
                    "severity": "HIGH",
                    "risk_pct": risk_pct,
                    "description": "Fasting blood sugar is high (>= 126 mg/dL), indicating diabetic range. Consult a clinician.",
                    "biomarkers_affected": ["glucose"]
                })
                dietary_rules["avoid_foods"].extend(["sugary drinks", "sweets", "white rice", "refined carbs", "sweeteners"])
                dietary_rules["recommend_foods"].extend(["spinach", "broccoli", "tofu", "wild salmon", "cinnamon", "nuts"])

        # Rule B: Lipid Panel (Cardiovascular / Dyslipidemia Risk)
        ldl = flagged_biomarkers.get("ldl_cholesterol")
        total_chol = flagged_biomarkers.get("total_cholesterol")
        hdl = flagged_biomarkers.get("hdl_cholesterol")
        
        lipid_affected = []
        lipid_issues = []
        
        if ldl and ldl["status"] == "HIGH":
            lipid_affected.append("ldl_cholesterol")
            lipid_issues.append("elevated LDL")
        if total_chol and total_chol["status"] == "HIGH":
            lipid_affected.append("total_cholesterol")
            lipid_issues.append("high total cholesterol")
        if hdl and hdl["status"] == "LOW":
            lipid_affected.append("hdl_cholesterol")
            lipid_issues.append("low HDL (good) cholesterol")
            
        if lipid_affected:
            severity = "HIGH" if (ldl and ldl["value"] > 160.0) or (total_chol and total_chol["value"] > 240.0) else "MODERATE"
            ldl_val = ldl["value"] if ldl else 100.0
            tc_val = total_chol["value"] if total_chol else 200.0
            base_risk = 75 if severity == "HIGH" else 62
            risk_pct = int(min(99, base_risk + max(0.0, ldl_val - 130.0) * 0.3 + max(0.0, tc_val - 200.0) * 0.2))
            
            health_risks.append({
                "condition": "Dyslipidemia / Cardiovascular Risk",
                "severity": severity,
                "risk_pct": risk_pct,
                "description": f"Abnormal lipid panel found: {', '.join(lipid_issues)}. Elevated cholesterol is linked to arterial plaque build-up.",
                "biomarkers_affected": lipid_affected
            })
            dietary_rules["avoid_foods"].extend(["saturated fats", "trans fats", "fatty beef", "butter", "fried foods", "cheese"])
            dietary_rules["recommend_foods"].extend(["oatmeal", "olive oil", "almonds", "walnuts", "mackerel", "beans", "barley"])
            dietary_rules["nutrient_targets"]["saturated_fat_limit_g"] = 15
            dietary_rules["nutrient_targets"]["fiber_target_g"] = 35

        # Rule C: Hemoglobin (Anemia Risk)
        hb = flagged_biomarkers.get("hemoglobin")
        mcv = flagged_biomarkers.get("mcv")
        mch = flagged_biomarkers.get("mch")
        
        if hb and hb["status"] == "LOW":
            severity = "HIGH" if hb["value"] < 10.0 else "MODERATE"
            hb_val = hb["value"]
            if severity == "HIGH":
                risk_pct = int(min(99, 80 + (10.0 - hb_val) * 2.0))
            else:
                risk_pct = int((12.0 - hb_val) / 2.0 * 20 + 60)
            
            is_microcytic = False
            if mcv and mcv["value"] < 80.0:
                is_microcytic = True
            if mch and mch["value"] < 27.0:
                is_microcytic = True
                
            condition_name = "Microcytic Anemia Risk (Iron Deficiency)" if is_microcytic else "Iron Deficiency Anemia Risk"
            if is_microcytic:
                severity = "HIGH"
                risk_pct = max(risk_pct, 85)
                
            health_risks.append({
                "condition": condition_name,
                "severity": severity,
                "risk_pct": risk_pct,
                "description": f"Low hemoglobin ({hb_val} {hb['unit']}) {'with microcytic indices (low MCV/MCH)' if is_microcytic else ''} indicates potential anemia, limiting blood oxygen transport.",
                "biomarkers_affected": ["hemoglobin"] + [k for k in ["mcv", "mch"] if k in flagged_biomarkers]
            })
            dietary_rules["avoid_foods"].extend(["excess coffee", "black tea", "calcium supplements during meals"])
            dietary_rules["recommend_foods"].extend(["red meat", "spinach", "lentils", "fortified cereals", "pumpkin seeds", "oranges", "bell peppers"])
            dietary_rules["nutrient_targets"]["iron_rich"] = True
            dietary_rules["nutrient_targets"]["vitamin_c_rich"] = True
            
        elif (mcv and mcv["status"] == "LOW") or (mch and mch["status"] == "LOW"):
            health_risks.append({
                "condition": "Microcytic Red Cell Indices",
                "severity": "MODERATE",
                "risk_pct": 62,
                "description": "Low MCV or MCH indicates smaller red blood cells, which can be an early sign of iron depletion.",
                "biomarkers_affected": [k for k in ["mcv", "mch"] if k in flagged_biomarkers]
            })
            dietary_rules["recommend_foods"].extend(["lentils", "spinach", "pumpkin seeds", "vitamin C foods"])

        # Rule D: Vitamin D Deficiency
        vit_d = flagged_biomarkers.get("vitamin_d")
        if vit_d and vit_d["status"] == "LOW":
            severity = "HIGH" if vit_d["value"] < 20.0 else "MODERATE"
            val = vit_d["value"]
            if severity == "HIGH":
                risk_pct = int(min(99, 80 + (20.0 - val) * 1.0))
            else:
                risk_pct = int((30.0 - val) / 10.0 * 20 + 60)
                
            health_risks.append({
                "condition": "Vitamin D Deficiency",
                "severity": severity,
                "risk_pct": risk_pct,
                "description": f"Vitamin D is low ({val} {vit_d['unit']}), risking bone health, immunity, and calcium absorption issues.",
                "biomarkers_affected": ["vitamin_d"]
            })
            dietary_rules["recommend_foods"].extend(["egg yolks", "mushrooms", "salmon", "sardines", "fortified milk", "fortified orange juice"])
            dietary_rules["nutrient_targets"]["vitamin_d_mcg"] = 20

        # Rule E: Vitamin B12 Deficiency
        vit_b12 = flagged_biomarkers.get("vitamin_b12")
        if vit_b12 and vit_b12["status"] == "LOW":
            severity = "HIGH" if vit_b12["value"] < 150.0 else "MODERATE"
            val = vit_b12["value"]
            if severity == "HIGH":
                risk_pct = int(min(99, 80 + (150.0 - val) * 0.2))
            else:
                risk_pct = int((200.0 - val) / 50.0 * 20 + 60)
                
            health_risks.append({
                "condition": "Vitamin B12 Deficiency",
                "severity": severity,
                "risk_pct": risk_pct,
                "description": f"Vitamin B12 is deficient ({val} {vit_b12['unit']}), which can lead to neurological fatigue and anemia.",
                "biomarkers_affected": ["vitamin_b12"]
            })
            dietary_rules["recommend_foods"].extend(["tuna", "beef liver", "fortified nutritional yeast", "milk", "clams", "eggs"])

        # Rule F: Hypertension (High Blood Pressure)
        sys = flagged_biomarkers.get("systolic_bp")
        dia = flagged_biomarkers.get("diastolic_bp")
        bp_affected = []
        if sys and sys["status"] == "HIGH":
            bp_affected.append("systolic_bp")
        if dia and dia["status"] == "HIGH":
            bp_affected.append("diastolic_bp")
            
        if bp_affected:
            severity = "HIGH" if (sys and sys["value"] >= 140) or (dia and dia["value"] >= 90) else "MODERATE"
            sys_val = sys["value"] if sys else 120.0
            dia_val = dia["value"] if dia else 80.0
            risk_pct = int(min(98, 60 + max(0.0, sys_val - 120.0) * 0.8 + max(0.0, dia_val - 80.0) * 1.5))
            
            health_risks.append({
                "condition": "Hypertension Risk (High Blood Pressure)",
                "severity": severity,
                "risk_pct": risk_pct,
                "description": "Blood pressure exceeds normal thresholds. High BP strains blood vessels and increases cardiac load.",
                "biomarkers_affected": bp_affected
            })
            dietary_rules["avoid_foods"].extend(["salt", "canned soups", "processed meats", "pickles", "fast food", "soy sauce"])
            dietary_rules["recommend_foods"].extend(["bananas", "sweet potatoes", "spinach", "garlic", "beets", "dark chocolate", "berries"])
            dietary_rules["nutrient_targets"]["sodium_limit_mg"] = 1500 # DASH diet standard
            dietary_rules["nutrient_targets"]["potassium_rich"] = True

        # Rule G: Platelets (Thrombocytopenia)
        plt = flagged_biomarkers.get("platelet_count")
        if plt and plt["status"] != "NORMAL":
            val = plt["value"]
            if plt["status"] == "LOW":
                severity = "HIGH" if val < 100.0 else "MODERATE"
                risk_pct = int(min(99, 85 + (100.0 - val) * 0.15)) if severity == "HIGH" else int((150.0 - val) / 50.0 * 20 + 60)
                health_risks.append({
                    "condition": "Thrombocytopenia Risk (Low Platelets)",
                    "severity": severity,
                    "risk_pct": risk_pct,
                    "description": f"Platelet count is low ({val} {plt['unit']}), which can affect normal blood clotting and coagulation.",
                    "biomarkers_affected": ["platelet_count"]
                })
                dietary_rules["recommend_foods"].extend(["spinach", "kale", "broccoli", "citrus fruits", "papaya leaf extract", "omega-3 foods"])
                dietary_rules["avoid_foods"].extend(["alcohol", "tonic water", "aspartame"])
            else:
                health_risks.append({
                    "condition": "Thrombocytosis Risk (High Platelets)",
                    "severity": "MODERATE",
                    "risk_pct": 65,
                    "description": f"Platelet count is elevated ({val} {plt['unit']}), indicating potential inflammation or reactive marrow response.",
                    "biomarkers_affected": ["platelet_count"]
                })
                dietary_rules["recommend_foods"].extend(["anti-inflammatory foods", "garlic", "ginger", "berries", "extra virgin olive oil"])

        # Rule H: Eosinophils (Allergy/Inflammation response)
        eos = flagged_biomarkers.get("eosinophils")
        if eos and eos["status"] == "HIGH":
            val = eos["value"]
            severity = "HIGH" if val > 15.0 else "MODERATE"
            risk_pct = int(min(95, 60 + (val - 6.0) * 4.0))
            health_risks.append({
                "condition": "Eosinophilia (Allergic/Inflammation Response)",
                "severity": severity,
                "risk_pct": risk_pct,
                "description": f"Elevated eosinophils ({val}%) point to a heightened allergic response, bronchial asthma, or mild systemic inflammation.",
                "biomarkers_affected": ["eosinophils"]
            })
            dietary_rules["recommend_foods"].extend(["anti-inflammatory foods", "turmeric", "ginger", "flaxseeds", "green tea"])
            dietary_rules["avoid_foods"].extend(["common allergens", "processed sugars", "highly processed wheat"])

        # Rule I: Neutrophils / Lymphocytes
        neut = flagged_biomarkers.get("neutrophils")
        lym = flagged_biomarkers.get("lymphocytes")
        if neut and neut["status"] == "HIGH":
            val = neut["value"]
            health_risks.append({
                "condition": "Neutrophilia (Acute Infection/Inflammation)",
                "severity": "MODERATE",
                "risk_pct": 68,
                "description": f"High neutrophils ({val}%) indicate the body is responding to an acute bacterial infection or physical stress.",
                "biomarkers_affected": ["neutrophils"]
            })
            dietary_rules["recommend_foods"].extend(["citrus fruits", "garlic", "zinc-rich foods", "bone broth", "warm tea"])
        if lym and lym["status"] == "HIGH":
            val = lym["value"]
            health_risks.append({
                "condition": "Lymphocytosis (Viral Response/Immune Activation)",
                "severity": "MODERATE",
                "risk_pct": 66,
                "description": f"High lymphocytes ({val}%) typically reflect a recent or active immune response to a viral infection.",
                "biomarkers_affected": ["lymphocytes"]
            })
            dietary_rules["recommend_foods"].extend(["elderberry", "citrus fruits", "spinach", "mushrooms", "antioxidant-rich foods"])

        # Deduplicate food lists
        dietary_rules["avoid_foods"] = list(set(dietary_rules["avoid_foods"]))
        dietary_rules["recommend_foods"] = list(set(dietary_rules["recommend_foods"]))

        # Calculate Overall Health Risk Score
        overall_risk_score = 12  # Healthy baseline
        if health_risks:
            max_risk = max(r["risk_pct"] for r in health_risks)
            bonus = (len(health_risks) - 1) * 3
            overall_risk_score = int(min(99, max_risk + bonus))
        else:
            abnormal_count = sum(1 for b in flagged_biomarkers.values() if b["status"] != "NORMAL")
            if abnormal_count > 0:
                overall_risk_score = int(min(45, 12 + abnormal_count * 8))

        return {
            "biomarkers": flagged_biomarkers,
            "health_risks": health_risks,
            "dietary_rules": dietary_rules,
            "overall_risk_score": overall_risk_score
        }

    def _parse_reference_range(self, key: str, ref_range_str: str) -> (float, float):
        """Helper to parse raw reference range strings like '70-99' or '<100' or '>30'."""
        try:
            # Clean string
            cleaned = ref_range_str.replace(" ", "").lower()
            
            # Pattern: "70-99" or "70-99.5"
            range_match = re.match(r"(\d+(?:\.\d+)?)-(\d+(?:\.\d+)?)", cleaned)
            if range_match:
                return float(range_match.group(1)), float(range_match.group(2))
                
            # Pattern: "<100"
            lt_match = re.match(r"<(\d+(?:\.\d+)?)", cleaned)
            if lt_match:
                return 0.0, float(lt_match.group(1))
                
            # Pattern: ">30"
            gt_match = re.match(r">(\d+(?:\.\d+)?)", cleaned)
            if gt_match:
                return float(gt_match.group(1)), 99999.0
        except Exception:
            pass
            
        # Fallback to defaults in settings
        default_range = self.ranges.get(key)
        if default_range:
            return default_range["min"], default_range["max"]
            
        return 0.0, 99999.0  # Safe catch-all
