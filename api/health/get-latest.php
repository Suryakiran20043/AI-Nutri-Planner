<?php
require_once dirname(__DIR__, 2) . '/api/config.php';
require_once dirname(__DIR__, 2) . '/api/db.php';
require_once dirname(__DIR__, 2) . '/api/helpers.php';

// Enforce auth check
$uid = auth_check();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed', 405);
}

$db = get_db();
try {
    // 1. Fetch latest report
    $reportSt = $db->prepare('
        SELECT id, file_name, uploaded_at, status, overall_risk_score 
        FROM user_health_reports 
        WHERE user_id = ? 
        ORDER BY uploaded_at DESC 
        LIMIT 1
    ');
    $reportSt->execute([$uid]);
    $report = $reportSt->fetch();

    if (!$report) {
        // No reports found is a valid state, return empty data
        json_ok([
            'has_report' => false,
            'report' => null,
            'biomarkers' => [],
            'health_risks' => []
        ]);
    }

    $reportId = (int)$report['id'];

    // 2. Fetch biomarkers for this report
    $biomarkerSt = $db->prepare('
        SELECT biomarker_name, measured_value, unit, reference_range, status 
        FROM user_biomarkers 
        WHERE report_id = ? AND user_id = ?
    ');
    $biomarkerSt->execute([$reportId, $uid]);
    $biomarkers = $biomarkerSt->fetchAll();

    // Reformat biomarkers into an associative object
    $biomarkerObj = [];
    foreach ($biomarkers as $bio) {
        $name = $bio['biomarker_name'];
        $displayName = str_replace('_', ' ', $name);
        $displayName = ucwords($displayName);
        
        // Match default names from config
        if ($name === 'glucose') $displayName = 'Blood Sugar (Glucose)';
        else if ($name === 'ldl_cholesterol') $displayName = 'LDL Cholesterol';
        else if ($name === 'hdl_cholesterol') $displayName = 'HDL Cholesterol (Good)';
        else if ($name === 'total_cholesterol') $displayName = 'Total Cholesterol';
        else if ($name === 'hemoglobin') $displayName = 'Hemoglobin (Iron Indicator)';
        else if ($name === 'vitamin_d') $displayName = 'Vitamin D';
        else if ($name === 'vitamin_b12') $displayName = 'Vitamin B12';
        else if ($name === 'systolic_bp') $displayName = 'Systolic Blood Pressure';
        else if ($name === 'diastolic_bp') $displayName = 'Diastolic Blood Pressure';
        else if ($name === 'platelet_count') $displayName = 'Platelet Count';
        else if ($name === 'mcv') $displayName = 'Mean Cell Volume (MCV)';
        else if ($name === 'mch') $displayName = 'Mean Cell Hemoglobin (MCH)';
        else if ($name === 'mchc') $displayName = 'Mean Cell Hemoglobin Concentration (MCHC)';
        else if ($name === 'rdw') $displayName = 'Red Cell Distribution Width (RDW)';
        else if ($name === 'neutrophils') $displayName = 'Neutrophils';
        else if ($name === 'lymphocytes') $displayName = 'Lymphocytes';
        else if ($name === 'eosinophils') $displayName = 'Eosinophils';
        else if ($name === 'monocytes') $displayName = 'Monocytes';
        else if ($name === 'basophils') $displayName = 'Basophils';

        $biomarkerObj[$name] = [
            'display_name' => $displayName,
            'value' => (float)$bio['measured_value'],
            'unit' => $bio['unit'],
            'reference_range' => $bio['reference_range'],
            'status' => $bio['status']
        ];
    }

    // 3. Fetch health risks for this report
    $riskSt = $db->prepare('
        SELECT risk_condition, severity, dietary_rules, risk_percentage 
        FROM user_health_risks 
        WHERE report_id = ? AND user_id = ?
    ');
    $riskSt->execute([$reportId, $uid]);
    $risks = $riskSt->fetchAll();

    $healthRisks = [];
    $dietaryRules = [
        'avoid_foods' => [],
        'recommend_foods' => [],
        'nutrient_targets' => []
    ];

    foreach ($risks as $r) {
        $healthRisks[] = [
            'condition' => $r['risk_condition'],
            'severity' => $r['severity'],
            'risk_percentage' => (int)($r['risk_percentage'] ?? 0)
        ];
        
        // Decode dietary rules if present
        $rules = json_decode($r['dietary_rules'], true);
        if ($rules) {
            $dietaryRules['avoid_foods'] = array_unique(array_merge($dietaryRules['avoid_foods'], $rules['restricted_ingredients'] ?? []));
            $dietaryRules['recommend_foods'] = array_unique(array_merge($dietaryRules['recommend_foods'], $rules['recommended_ingredients'] ?? []));
            if (!empty($rules['active_targets'])) {
                $dietaryRules['nutrient_targets'] = array_merge($dietaryRules['nutrient_targets'], $rules['active_targets']);
            }
        }
    }

    // 4. Fetch meal plan generated for today
    $planDate = date('Y-m-d');
    $mealSt = $db->prepare('
        SELECT meal_slot, food_name, calories, protein_g, carbs_g, fat_g, fiber_g, instructions, image_url
        FROM meal_plans 
        WHERE user_id = ? AND plan_date = ?
    ');
    $mealSt->execute([$uid, $planDate]);
    $mealsRaw = $mealSt->fetchAll();
    
    $meals = [];
    foreach ($mealsRaw as $m) {
        $meals[$m['meal_slot']] = [
            'name' => $m['food_name'],
            'calories' => (int)$m['calories'],
            'protein' => (float)$m['protein_g'],
            'carbs' => (float)$m['carbs_g'],
            'fat' => (float)$m['fat_g'],
            'fiber' => (float)$m['fiber_g'],
            'image_url' => $m['image_url'],
            'instructions' => $m['instructions']
        ];
    }

    json_ok([
        'has_report' => true,
        'report' => [
            'id' => $reportId,
            'file_name' => $report['file_name'],
            'uploaded_at' => $report['uploaded_at'],
            'status' => $report['status'],
            'overall_risk_score' => (int)($report['overall_risk_score'] ?? 0)
        ],
        'biomarkers' => $biomarkerObj,
        'health_risks' => $healthRisks,
        'dietary_summary' => $dietaryRules,
        'meal_plan' => $meals
    ]);

} catch (Exception $e) {
    json_error('Failed to retrieve health metrics: ' . $e->getMessage(), 500);
}
