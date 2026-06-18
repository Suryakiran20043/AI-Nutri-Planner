<?php
/**
 * EatThisMuch Food Scraper
 * 
 * Scrapes food/recipe data from eatthismuch.com and stores in the database.
 * Run by visiting in browser: http://localhost/api/scrape_etm.php
 * 
 * No authentication required - utility script.
 */

// Override the JSON content-type set by config.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Override headers for HTML output
header('Content-Type: text/html; charset=utf-8');
header_remove('Content-Type');
header('Content-Type: text/html; charset=utf-8');

set_time_limit(0);
ini_set('memory_limit', '256M');

echo "<!DOCTYPE html><html><head><title>ETM Scraper</title></head><body><pre>\n";
echo "=== EatThisMuch Food Scraper ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
ob_flush(); flush();

// ─── 1. Initialize database tables ───────────────────────────────────────────
$pdo = get_db();
$schemaFile = __DIR__ . '/../sql/etm_schema.sql';
if (!file_exists($schemaFile)) {
    echo "ERROR: Schema file not found at $schemaFile\n";
    echo "</pre></body></html>";
    exit;
}
$pdo->exec(file_get_contents($schemaFile));
echo "[OK] Database tables ready.\n\n";
ob_flush(); flush();

// ─── 2. Curated food URL slugs ───────────────────────────────────────────────
$foodSlugs = [
    // Recipes
    'easy-hard-boiled-eggs-4698529',
    'chicken-caesar-salad-4829491',
    'simple-spinach-scramble-56823',
    'basic-scrambled-eggs-34096',
    'easy-grilled-chicken-teriyaki-5175896',
    'microwaved-sweet-potato-42621',
    'all-american-tuna-4984787',
    'easy-grilled-chicken-4850496',
    'tuna-salad-4841202',
    'easy-garlic-chicken-33501',
    'coconut-milk-protein-shake-5187032',
    'coconut-flour-pancake-907140',

    // Basic foods
    'banana-1337',
    'egg-103',
    'chicken-breast-48',
    'white-rice-63',
    'salmon-43',
    'greek-yogurt-5187',
    'oatmeal-51',
    'avocado-1627',
    'broccoli-36',
    'sweet-potato-3835',
    'brown-rice-60',
    'spinach-30',
    'almonds-129',
    'cottage-cheese-16',
    'turkey-breast-45',
    'quinoa-19003',
    'black-beans-6',
    'lentils-18',
    'tofu-110',
    'peanut-butter-95',
    'olive-oil-96',
    'whole-wheat-bread-157',
    'apple-139',
    'blueberries-118',
    'orange-98',
    'strawberries-55',
    'mango-135',
    'kale-1000',
    'cauliflower-37',
    'bell-pepper-122',
    'tomato-54',
    'cucumber-42',
    'carrot-32',
    'mushrooms-56',
    'corn-31',
    'asparagus-15',
    'green-beans-26',
    'zucchini-29',
    'celery-35',
    'onion-88',
    'garlic-13',
    'ginger-14',
    'cheddar-cheese-3',
    'mozzarella-cheese-10',
    'parmesan-cheese-12',
    'cream-cheese-9',
    'whole-milk-80',
    'skim-milk-83',
    'coconut-oil-79',
    'butter-69',
    'honey-78',
    'maple-syrup-107',
    'soy-sauce-75',
    'hot-sauce-112',
    'walnut-108',
    'cashews-130',
    'sunflower-seeds-117',
    'chia-seeds-165',
    'flaxseed-165282',
    'dark-chocolate-4371',
    'granola-21810',
    'hummus-170',
    'edamame-156',
    'chickpeas-162',
    'kidney-beans-5',
    'bacon-90',
    'ground-beef-92',
    'pork-chop-86',
    'shrimp-49',
    'tuna-50',
    'cod-40',
    'tilapia-47',
    'canned-tuna-50',
    'whey-protein-34568',

    // More recipes
    'easy-chicken-stir-fry-34045',
    'protein-pancakes-5187033',
    'overnight-oats-5175868',
    'greek-salad-4855918',
    'baked-salmon-4741982',
    'grilled-cheese-sandwich-5187060',
    'turkey-burger-5175885',
    'chicken-burrito-bowl-5175908',
    'shrimp-stir-fry-5187120',
    'vegetable-soup-5187066',
    'chicken-salad-5175901',
    'beef-tacos-5175912',
    'pasta-with-meat-sauce-5175867',
    'avocado-toast-5175895',
    'smoothie-bowl-5175904',
    'chicken-quesadilla-5175910',
    'veggie-wrap-5187055',
    'meatball-sub-5187071',
];

$total = count($foodSlugs);
echo "Total foods to scrape: $total\n";
echo str_repeat('─', 60) . "\n\n";
ob_flush(); flush();

// ─── 3. Prepare SQL statements ───────────────────────────────────────────────
$upsertFoodSQL = "
    INSERT INTO etm_foods (
        etm_id, slug, name, image_url,
        prep_time_minutes, cook_time_minutes, total_time_minutes,
        servings, serving_size,
        calories, protein, total_carbs, total_fat, dietary_fiber, sugar,
        saturated_fat, trans_fat, cholesterol, sodium,
        calcium, iron, potassium, vitamin_d,
        vitamin_a, vitamin_c, vitamin_b6, vitamin_b12,
        vitamin_e, vitamin_k, magnesium, zinc,
        phosphorus, selenium,
        directions, rating, source_url
    ) VALUES (
        :etm_id, :slug, :name, :image_url,
        :prep_time_min, :cook_time_min, :total_time_min,
        :servings, :serving_size,
        :calories, :protein_g, :carbs_g, :fat_g, :fiber_g, :sugar_g,
        :saturated_fat_g, :trans_fat_g, :cholesterol_mg, :sodium_mg,
        :calcium_mg, :iron_mg, :potassium_mg, :vitamin_d_mcg,
        :vitamin_a_mcg, :vitamin_c_mg, :vitamin_b6_mg, :vitamin_b12_mcg,
        :vitamin_e_mg, :vitamin_k_mcg, :magnesium_mg, :zinc_mg,
        :phosphorus_mg, :selenium_mcg,
        :instructions, :rating, :source_url
    ) ON DUPLICATE KEY UPDATE
        slug = VALUES(slug),
        name = VALUES(name),
        image_url = VALUES(image_url),
        prep_time_minutes = VALUES(prep_time_minutes),
        cook_time_minutes = VALUES(cook_time_minutes),
        total_time_minutes = VALUES(total_time_minutes),
        servings = VALUES(servings),
        serving_size = VALUES(serving_size),
        calories = VALUES(calories),
        protein = VALUES(protein),
        total_carbs = VALUES(total_carbs),
        total_fat = VALUES(total_fat),
        dietary_fiber = VALUES(dietary_fiber),
        sugar = VALUES(sugar),
        saturated_fat = VALUES(saturated_fat),
        trans_fat = VALUES(trans_fat),
        cholesterol = VALUES(cholesterol),
        sodium = VALUES(sodium),
        calcium = VALUES(calcium),
        iron = VALUES(iron),
        potassium = VALUES(potassium),
        vitamin_d = VALUES(vitamin_d),
        vitamin_a = VALUES(vitamin_a),
        vitamin_c = VALUES(vitamin_c),
        vitamin_b6 = VALUES(vitamin_b6),
        vitamin_b12 = VALUES(vitamin_b12),
        vitamin_e = VALUES(vitamin_e),
        vitamin_k = VALUES(vitamin_k),
        magnesium = VALUES(magnesium),
        zinc = VALUES(zinc),
        phosphorus = VALUES(phosphorus),
        selenium = VALUES(selenium),
        directions = VALUES(directions),
        rating = VALUES(rating),
        source_url = VALUES(source_url)
";

$stmtFood = $pdo->prepare($upsertFoodSQL);

$stmtDeleteIngredients = $pdo->prepare("DELETE FROM etm_food_ingredients WHERE etm_food_id = :food_id");

$stmtInsertIngredient = $pdo->prepare("
    INSERT INTO etm_food_ingredients (etm_food_id, sort_order, ingredient_name, ingredient_image_url, ingredient_link)
    VALUES (:food_id, :sort_order, :name, :thumbnail, :link)
");

// ─── 4. Helper functions ─────────────────────────────────────────────────────

/**
 * Parse ISO 8601 duration (PT format) to minutes.
 */
function parse_pt_duration(?string $pt): ?int {
    if (empty($pt)) return null;
    $minutes = 0;
    if (preg_match('/(\d+)H/', $pt, $m)) $minutes += (int)$m[1] * 60;
    if (preg_match('/(\d+)M/', $pt, $m)) $minutes += (int)$m[1];
    if (preg_match('/(\d+)S/', $pt, $m)) $minutes += ceil((int)$m[1] / 60);
    return $minutes > 0 ? $minutes : null;
}

/**
 * Extract numeric value from a nutrition string like "42mg", "1.5g", "150μg", "3 mg".
 */
function parse_nutrient_value(?string $val): ?float {
    if (empty($val)) return null;
    if (preg_match('/([\d\.]+)/', $val, $m)) {
        return (float)$m[1];
    }
    return null;
}

/**
 * Extract slug and ETM ID from a URL path.
 */
function parse_slug_id(string $slugWithId): array {
    if (preg_match('/^(.+)-(\d+)$/', $slugWithId, $m)) {
        return [$m[1], (int)$m[2]];
    }
    return [$slugWithId, 0];
}

/**
 * Fetch a URL.
 */
function fetch_url(string $url): ?string {
    $safeUrl = escapeshellarg($url);
    $psCmd = "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; " .
             "try { \$res = Invoke-WebRequest -Uri $safeUrl -UseBasicParsing -TimeoutSec 15; [Console]::OutputEncoding = [System.Text.Encoding]::UTF8; Write-Output \$res.Content; } catch { exit 1; }";
    $cmd = "powershell -NoProfile -NonInteractive -Command " . escapeshellarg($psCmd);
    return shell_exec($cmd) ?: null;
}

/**
 * Parse micronutrients from the HTML nutrition facts table.
 */
function parse_html_micronutrients(string $html): array {
    $micros = [];
    $fields = [
        'Calcium' => 'calcium_mg',
        'Iron' => 'iron_mg',
        'Potassium' => 'potassium_mg',
        'Vitamin D' => 'vitamin_d_mcg',
        'Vitamin A' => 'vitamin_a_mcg',
        'Vitamin C' => 'vitamin_c_mg',
        'Vitamin B6' => 'vitamin_b6_mg',
        'Vitamin B12' => 'vitamin_b12_mcg',
        'Vitamin E' => 'vitamin_e_mg',
        'Vitamin K' => 'vitamin_k_mcg',
        'Magnesium' => 'magnesium_mg',
        'Zinc' => 'zinc_mg',
        'Phosphorus' => 'phosphorus_mg',
        'Selenium' => 'selenium_mcg',
    ];

    foreach ($fields as $label => $key) {
        if (preg_match('/' . preg_quote($label, '/') . '[^<]*<\/div>\s*<div[^>]*>\s*([\d\.]+)\s*(mg|mcg|μg|g)/i', $html, $m)) {
            $micros[$key] = (float)$m[1];
        } else if (preg_match('/<div[^>]*>\s*' . preg_quote($label, '/') . '\s*<\/div>\s*<div[^>]*>\s*([\d\.]+)\s*(mg|mcg|μg|g)/i', $html, $m)) {
            $micros[$key] = (float)$m[1];
        }
    }
    return $micros;
}

/**
 * Parse ingredient links and thumbnails from HTML.
 */
function parse_ingredient_links(string $html): array {
    $ingredients = [];
    if (preg_match_all('/<a\s+href="\/calories\/([^"]+)"[^>]*class="[^"]*svelte-[^"]*"[^>]*>(?:\s*<img[^>]*src="([^"]*)"[^>]*\/?>)?/i', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $slugId = $m[1];
            $thumb = $m[2] ?? null;
            list($slug, $etmId) = parse_slug_id($slugId);
            $ingredients[] = [
                'slug' => $slug,
                'etm_id' => $etmId,
                'thumbnail' => $thumb,
                'link' => "https://www.eatthismuch.com/calories/{$slugId}"
            ];
        }
    }
    return $ingredients;
}

// ─── 5. Main scraping loop ───────────────────────────────────────────────────
$successCount = 0;
$failCount = 0;
$errors = [];

for ($i = 0; $i < $total; $i++) {
    $slugWithId = $foodSlugs[$i];
    $num = $i + 1;
    $url = "https://www.eatthismuch.com/calories/{$slugWithId}";
    list($slug, $etmId) = parse_slug_id($slugWithId);

    echo "[$num/$total] Scraping: $slugWithId ... ";
    ob_flush(); flush();

    try {
        $html = fetch_url($url);
        if ($html === null) throw new Exception("Failed to fetch URL");

        $jsonLd = null;
        if (preg_match('/<script type="application\/ld\+json"[^>]*>(.+?)<\/script>/s', $html, $jsonMatches)) {
            $jsonLd = json_decode($jsonMatches[1], true);
        }
        if (!$jsonLd) throw new Exception("No JSON-LD data");

        $name = $jsonLd['name'] ?? $slug;
        $imageUrl = isset($jsonLd['image']) ? (is_array($jsonLd['image']) ? ($jsonLd['image'][0] ?? null) : $jsonLd['image']) : null;
        $prepTime = parse_pt_duration($jsonLd['prepTime'] ?? null);
        $cookTime = parse_pt_duration($jsonLd['cookTime'] ?? null);
        $totalTime = parse_pt_duration($jsonLd['totalTime'] ?? null);
        $servings = is_array($jsonLd['recipeYield'] ?? null) ? ($jsonLd['recipeYield'][0] ?? null) : ($jsonLd['recipeYield'] ?? null);
        $nutrition = $jsonLd['nutrition'] ?? [];
        $calories     = parse_nutrient_value($nutrition['calories'] ?? null);
        $protein      = parse_nutrient_value($nutrition['proteinContent'] ?? null);
        $carbs        = parse_nutrient_value($nutrition['carbohydrateContent'] ?? null);
        $fat          = parse_nutrient_value($nutrition['fatContent'] ?? null);
        $fiber        = parse_nutrient_value($nutrition['fiberContent'] ?? null);
        $sugar        = parse_nutrient_value($nutrition['sugarContent'] ?? null);
        $saturatedFat = parse_nutrient_value($nutrition['saturatedFatContent'] ?? null);
        $transFat     = parse_nutrient_value($nutrition['transFatContent'] ?? null);
        $cholesterol  = parse_nutrient_value($nutrition['cholesterolContent'] ?? null);
        $sodium       = parse_nutrient_value($nutrition['sodiumContent'] ?? null);
        $servingSize  = $nutrition['servingSize'] ?? null;
        $rating       = isset($jsonLd['aggregateRating']['ratingValue']) ? (float)$jsonLd['aggregateRating']['ratingValue'] : null;
        
        $instructions = null;
        if (!empty($jsonLd['recipeInstructions'])) {
            $steps = [];
            foreach ($jsonLd['recipeInstructions'] as $step) {
                if (is_array($step) && isset($step['text'])) $steps[] = $step['text'];
            }
            if (!empty($steps)) $instructions = json_encode($steps);
        }

        $ingredientTexts = $jsonLd['recipeIngredient'] ?? [];
        $micros = parse_html_micronutrients($html);
        $ingredientLinks = parse_ingredient_links($html);

        $stmtFood->execute([
            ':etm_id'          => $etmId,
            ':slug'            => $slug,
            ':name'            => $name,
            ':image_url'       => $imageUrl,
            ':prep_time_min'   => $prepTime,
            ':cook_time_min'   => $cookTime,
            ':total_time_min'  => $totalTime,
            ':servings'        => (int)$servings,
            ':serving_size'    => $servingSize,
            ':calories'        => $calories,
            ':protein_g'       => $protein,
            ':carbs_g'         => $carbs,
            ':fat_g'           => $fat,
            ':fiber_g'         => $fiber,
            ':sugar_g'         => $sugar,
            ':saturated_fat_g' => $saturatedFat,
            ':trans_fat_g'     => $transFat,
            ':cholesterol_mg'  => $cholesterol,
            ':sodium_mg'       => $sodium,
            ':calcium_mg'      => $micros['calcium_mg'] ?? null,
            ':iron_mg'         => $micros['iron_mg'] ?? null,
            ':potassium_mg'    => $micros['potassium_mg'] ?? null,
            ':vitamin_d_mcg'   => $micros['vitamin_d_mcg'] ?? null,
            ':vitamin_a_mcg'   => $micros['vitamin_a_mcg'] ?? null,
            ':vitamin_c_mg'    => $micros['vitamin_c_mg'] ?? null,
            ':vitamin_b6_mg'   => $micros['vitamin_b6_mg'] ?? null,
            ':vitamin_b12_mcg' => $micros['vitamin_b12_mcg'] ?? null,
            ':vitamin_e_mg'    => $micros['vitamin_e_mg'] ?? null,
            ':vitamin_k_mcg'   => $micros['vitamin_k_mcg'] ?? null,
            ':magnesium_mg'    => $micros['magnesium_mg'] ?? null,
            ':zinc_mg'         => $micros['zinc_mg'] ?? null,
            ':phosphorus_mg'   => $micros['phosphorus_mg'] ?? null,
            ':selenium_mcg'    => $micros['selenium_mcg'] ?? null,
            ':instructions'    => $instructions,
            ':rating'          => $rating,
            ':source_url'      => $url,
        ]);

        $foodId = $pdo->lastInsertId() ?: ($pdo->query("SELECT id FROM etm_foods WHERE etm_id = $etmId")->fetchColumn());

        if ($foodId && !empty($ingredientTexts)) {
            $stmtDeleteIngredients->execute([':food_id' => $foodId]);
            foreach ($ingredientTexts as $idx => $text) {
                $linkData = $ingredientLinks[$idx] ?? null;
                $stmtInsertIngredient->execute([
                    ':food_id'    => $foodId,
                    ':sort_order' => $idx + 1,
                    ':name'       => $text,
                    ':thumbnail'  => $linkData['thumbnail'] ?? null,
                    ':link'       => $linkData['link'] ?? null,
                ]);
            }
        }

        $calDisplay = $calories !== null ? round($calories) . ' cal' : 'N/A';
        $ingredCount = count($ingredientTexts);
        echo "<b style='color:green'>OK</b> — $name ($calDisplay, $ingredCount ingredients)\n";
        $successCount++;

    } catch (Exception $e) {
        $errMsg = $e->getMessage();
        echo "<b style='color:red'>FAILED</b> — $errMsg\n";
        $errors[] = "$slugWithId: $errMsg";
        $failCount++;
    }

    ob_flush(); flush();

    // Rate limit: sleep 1 second between requests
    if ($i < $total - 1) {
        sleep(1);
    }
}

// ─── 6. Summary ──────────────────────────────────────────────────────────────
echo "\n" . str_repeat('─', 60) . "\n";
echo "=== SCRAPING COMPLETE ===\n";
echo "Scraped <b>$successCount of $total</b> foods successfully.\n";

if ($failCount > 0) {
    echo "\n<b style='color:red'>$failCount failed:</b>\n";
    foreach ($errors as $err) {
        echo "  • $err\n";
    }
}

echo "\nFinished at: " . date('Y-m-d H:i:s') . "\n";
echo "</pre></body></html>\n";
