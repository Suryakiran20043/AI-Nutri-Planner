package com.nutriplanner.backend.controller;

import com.nutriplanner.backend.model.GroceryItem;
import com.nutriplanner.backend.model.MealRecommendation;
import com.nutriplanner.backend.model.EtmFoodIngredient;
import com.nutriplanner.backend.model.User;
import com.nutriplanner.backend.repository.GroceryItemRepository;
import com.nutriplanner.backend.repository.MealRecommendationRepository;
import com.nutriplanner.backend.repository.EtmFoodIngredientRepository;
import com.nutriplanner.backend.repository.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDate;
import java.util.*;

@CrossOrigin(origins = "*", maxAge = 3600)
@RestController
@RequestMapping("/api/grocery")
public class GroceryController {

    @Autowired
    GroceryItemRepository groceryItemRepository;

    @Autowired
    UserRepository userRepository;

    @Autowired
    MealRecommendationRepository mealRecommendationRepository;

    @Autowired
    EtmFoodIngredientRepository etmFoodIngredientRepository;

    private String categorize(String name) {
        String lower = name.toLowerCase();
        if (lower.matches(".*(chicken|beef|salmon|fish|egg|tuna|pork|turkey|shrimp|meat|bacon|poultry|sausage|steak).*")) return "protein";
        if (lower.matches(".*(milk|yogurt|cheese|butter|cream|dairy|whey|parmesan|cheddar|mozzarella).*")) return "dairy";
        if (lower.matches(".*(rice|pasta|bread|oat|quinoa|flour|wheat|cereal|spaghetti|noodle|tortilla|bun).*")) return "grains";
        if (lower.matches(".*(apple|banana|spinach|broccoli|carrot|tomato|onion|lettuce|berry|fruit|vegetable|salad|potato|avocad|lemon|lime|garlic|pepper|kale|cucumber).*")) return "produce";
        return "pantry";
    }

    @Transactional
    @PostMapping("/generate-list")
    public ResponseEntity<?> generateList(@RequestBody Map<String, Object> body) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        String weekStartStr = body.getOrDefault("week_start", LocalDate.now().toString()).toString();
        LocalDate weekStart = LocalDate.parse(weekStartStr);

        groceryItemRepository.deleteByUserIdAndWeekStartAndIsCheckedFalse(user.getId(), weekStart);

        List<GroceryItem> checkedItems = groceryItemRepository.findByUserIdAndWeekStartAndIsCheckedTrue(user.getId(), weekStart);
        Set<String> checkedNames = new HashSet<>();
        for (GroceryItem item : checkedItems) {
            checkedNames.add(item.getFoodName());
        }

        LocalDate end = weekStart.plusDays(6);
        List<MealRecommendation> meals = mealRecommendationRepository.findByUserAndPlanDateBetween(user, weekStart, end);

        int added = 0;
        Set<String> processedNames = new HashSet<>();

        for (MealRecommendation meal : meals) {
            if (meal.getFoodName() == null || meal.getFoodName().isEmpty()) continue;

            List<Map<String, String>> ingredientsToAdd = new ArrayList<>();

            if (meal.getEtmFoodId() != null) {
                List<EtmFoodIngredient> realIngs = etmFoodIngredientRepository.findByEtmFoodId(meal.getEtmFoodId());
                if (!realIngs.isEmpty()) {
                    for (EtmFoodIngredient ri : realIngs) {
                        if (ri.getIngredientName() != null && !ri.getIngredientName().trim().isEmpty()) {
                            ingredientsToAdd.add(Map.of("name", ri.getIngredientName().trim(), "amount", ri.getIngredientAmount() != null ? ri.getIngredientAmount() : "1 serving"));
                        }
                    }
                } else {
                    ingredientsToAdd.add(Map.of("name", meal.getFoodName().trim(), "amount", meal.getServingSize() != null ? meal.getServingSize() : "1 serving"));
                }
            } else {
                ingredientsToAdd.add(Map.of("name", meal.getFoodName().trim(), "amount", meal.getServingSize() != null ? meal.getServingSize() : "1 serving"));
            }

            for (Map<String, String> ing : ingredientsToAdd) {
                String name = ing.get("name");
                String key = name.toLowerCase();

                if (processedNames.contains(key) || checkedNames.contains(name)) {
                    continue;
                }

                String cat = categorize(name);
                GroceryItem item = new GroceryItem();
                item.setUserId(user.getId());
                item.setWeekStart(weekStart);
                item.setFoodName(name);
                item.setQuantity(ing.get("amount"));
                item.setCategory(cat);
                groceryItemRepository.save(item);
                
                processedNames.add(key);
                added++;
            }
        }

        return ResponseEntity.ok(Map.of("items_added", added));
    }

    @GetMapping("/get-list")
    public ResponseEntity<?> getList(@RequestParam String week_start) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        LocalDate weekStart = LocalDate.parse(week_start);
        List<GroceryItem> items = groceryItemRepository.findByUserIdAndWeekStart(user.getId(), weekStart);

        Map<String, List<GroceryItem>> categorized = new HashMap<>();
        for (GroceryItem item : items) {
            categorized.computeIfAbsent(item.getCategory(), k -> new ArrayList<>()).add(item);
        }

        return ResponseEntity.ok(categorized);
    }

    @PostMapping("/toggle-item")
    public ResponseEntity<?> toggleItem(@RequestBody Map<String, Object> body) {
        Long id = Long.parseLong(body.get("id").toString());
        Boolean checked = (Boolean) body.get("checked");
        
        Optional<GroceryItem> itemOpt = groceryItemRepository.findById(id);
        if (itemOpt.isPresent()) {
            GroceryItem item = itemOpt.get();
            item.setIsChecked(checked);
            groceryItemRepository.save(item);
            return ResponseEntity.ok(Map.of("message", "Toggled"));
        }
        return ResponseEntity.status(404).body("Item not found");
    }
}
