package com.nutriplanner.backend.controller;

import com.nutriplanner.backend.model.EtmFood;
import com.nutriplanner.backend.repository.EtmFoodRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.client.RestTemplate;

import java.util.*;

@CrossOrigin(origins = "*", maxAge = 3600)
@RestController
@RequestMapping("/api")
public class IntegrationController {

    @Autowired
    EtmFoodRepository etmFoodRepository;

    private final String SPOONACULAR_API_KEY = "YOUR_SPOONACULAR_API_KEY"; // Adjust as needed
    private final String USDA_API_KEY = "DEMO_KEY";

    @GetMapping("/etm/search_etm")
    public ResponseEntity<?> searchEtm(
            @RequestParam(defaultValue = "") String q,
            @RequestParam(defaultValue = "name") String sort,
            @RequestParam(defaultValue = "asc") String order,
            @RequestParam(defaultValue = "1") int page,
            @RequestParam(defaultValue = "20") int limit
    ) {
        Sort.Direction direction = order.equalsIgnoreCase("desc") ? Sort.Direction.DESC : Sort.Direction.ASC;
        PageRequest pr = PageRequest.of(page - 1, limit, Sort.by(direction, sort));

        Page<EtmFood> result = etmFoodRepository.findByNameContainingIgnoreCase(q, pr);

        return ResponseEntity.ok(Map.of(
                "foods", result.getContent(),
                "total", result.getTotalElements(),
                "page", page,
                "totalPages", result.getTotalPages()
        ));
    }

    @GetMapping("/etm/get_etm_food")
    public ResponseEntity<?> getEtmFood(@RequestParam Long id) {
        Optional<EtmFood> food = etmFoodRepository.findById(id);
        if (food.isPresent()) {
            return ResponseEntity.ok(Map.of("food", food.get()));
        } else {
            return ResponseEntity.status(404).body(Map.of("error", "Not found"));
        }
    }

    @GetMapping("/spoonacular/search-recipes")
    public ResponseEntity<?> searchRecipes(@RequestParam String query, @RequestParam(required = false) Integer maxCalories, @RequestParam(required = false) String diet) {
        try {
            RestTemplate restTemplate = new RestTemplate();
            String url = "https://api.spoonacular.com/recipes/complexSearch?query=" + query + "&number=15&addRecipeNutrition=true&apiKey=" + SPOONACULAR_API_KEY;
            
            if (maxCalories != null && maxCalories > 0) {
                url += "&maxCalories=" + maxCalories;
            }
            if (diet != null && !diet.isEmpty() && !diet.equals("anything")) {
                url += "&diet=" + diet;
            }
            
            Map<String, Object> response = restTemplate.getForObject(url, Map.class);
            
            List<Map<String, Object>> recipes = new ArrayList<>();
            if (response != null && response.containsKey("results")) {
                List<Map<String, Object>> results = (List<Map<String, Object>>) response.get("results");
                for (Map<String, Object> r : results) {
                    Map<String, Object> nutrition = (Map<String, Object>) r.get("nutrition");
                    List<Map<String, Object>> nutrients = (List<Map<String, Object>>) nutrition.get("nutrients");
                    
                    Map<String, Double> nutMap = new HashMap<>();
                    for (Map<String, Object> n : nutrients) {
                        nutMap.put((String) n.get("name"), Double.valueOf(n.get("amount").toString()));
                    }
                    
                    Map<String, Object> recipe = new HashMap<>();
                    recipe.put("id", r.get("id"));
                    recipe.put("title", r.get("title"));
                    recipe.put("image", r.get("image"));
                    recipe.put("calories", nutMap.getOrDefault("Calories", 0.0));
                    recipe.put("protein_g", nutMap.getOrDefault("Protein", 0.0));
                    recipe.put("carbs_g", nutMap.getOrDefault("Carbohydrates", 0.0));
                    recipe.put("fat_g", nutMap.getOrDefault("Fat", 0.0));
                    recipe.put("fiber_g", nutMap.getOrDefault("Fiber", 0.0));
                    recipe.put("readyInMinutes", r.getOrDefault("readyInMinutes", 30));
                    recipe.put("servingSize", r.getOrDefault("servings", 1) + " serving");
                    
                    recipes.add(recipe);
                }
            }
            return ResponseEntity.ok(Map.of("recipes", recipes));
        } catch (Exception e) {
            return ResponseEntity.status(502).body(Map.of("error", e.getMessage()));
        }
    }

    @GetMapping("/spoonacular/get-recipe")
    public ResponseEntity<?> getRecipe(@RequestParam String id) {
        try {
            RestTemplate restTemplate = new RestTemplate();
            String url = "https://api.spoonacular.com/recipes/" + id + "/information?includeNutrition=true&apiKey=" + SPOONACULAR_API_KEY;
            
            Map<String, Object> r = restTemplate.getForObject(url, Map.class);
            
            if (r != null) {
                Map<String, Object> nutrition = (Map<String, Object>) r.get("nutrition");
                List<Map<String, Object>> nutrients = (List<Map<String, Object>>) nutrition.get("nutrients");
                
                Map<String, Double> nutMap = new HashMap<>();
                for (Map<String, Object> n : nutrients) {
                    nutMap.put((String) n.get("name"), Double.valueOf(n.get("amount").toString()));
                }
                
                List<String> ingredients = new ArrayList<>();
                List<Map<String, Object>> extendedIngredients = (List<Map<String, Object>>) r.get("extendedIngredients");
                if (extendedIngredients != null) {
                    for (Map<String, Object> ing : extendedIngredients) {
                        ingredients.add((String) ing.get("original"));
                    }
                }
                
                Map<String, Object> recipe = new HashMap<>();
                recipe.put("id", r.get("id"));
                recipe.put("title", r.get("title"));
                recipe.put("image", r.get("image"));
                recipe.put("calories", nutMap.getOrDefault("Calories", 0.0));
                recipe.put("protein_g", nutMap.getOrDefault("Protein", 0.0));
                recipe.put("carbs_g", nutMap.getOrDefault("Carbohydrates", 0.0));
                recipe.put("fat_g", nutMap.getOrDefault("Fat", 0.0));
                recipe.put("fiber_g", nutMap.getOrDefault("Fiber", 0.0));
                recipe.put("readyInMinutes", r.getOrDefault("readyInMinutes", 30));
                recipe.put("servings", r.getOrDefault("servings", 1));
                recipe.put("instructions", r.get("instructions"));
                recipe.put("ingredients", ingredients);
                
                return ResponseEntity.ok(Map.of("recipe", recipe));
            }
            return ResponseEntity.status(404).body(Map.of("error", "Recipe not found"));
        } catch (Exception e) {
            return ResponseEntity.status(502).body(Map.of("error", e.getMessage()));
        }
    }

    @GetMapping("/usda/get-food")
    public ResponseEntity<?> getUsdaFood(@RequestParam String fdcId) {
        try {
            RestTemplate restTemplate = new RestTemplate();
            String url = "https://api.nal.usda.gov/fdc/v1/food/" + fdcId + "?api_key=" + USDA_API_KEY;
            
            Map<String, Object> r = restTemplate.getForObject(url, Map.class);
            
            if (r != null) {
                Map<String, Double> nutMap = new HashMap<>();
                List<Map<String, Object>> foodNutrients = (List<Map<String, Object>>) r.get("foodNutrients");
                if (foodNutrients != null) {
                    for (Map<String, Object> n : foodNutrients) {
                        Map<String, Object> nutrient = (Map<String, Object>) n.get("nutrient");
                        if (nutrient != null) {
                            String name = (String) nutrient.get("name");
                            Object amountObj = n.get("amount");
                            if (name != null && amountObj != null) {
                                nutMap.put(name, Double.valueOf(amountObj.toString()));
                            }
                        }
                    }
                }
                
                Map<String, Object> food = new HashMap<>();
                food.put("fdcId", r.get("fdcId"));
                food.put("description", r.get("description"));
                food.put("calories", nutMap.getOrDefault("Energy", 0.0));
                food.put("protein_g", nutMap.getOrDefault("Protein", 0.0));
                food.put("carbs_g", nutMap.getOrDefault("Carbohydrate, by difference", 0.0));
                food.put("fat_g", nutMap.getOrDefault("Total lipid (fat)", 0.0));
                food.put("fiber_g", nutMap.getOrDefault("Fiber, total dietary", 0.0));
                
                return ResponseEntity.ok(Map.of("food", food));
            }
            return ResponseEntity.status(404).body(Map.of("error", "Food not found"));
        } catch (Exception e) {
            return ResponseEntity.status(502).body(Map.of("error", e.getMessage()));
        }
    }
}
