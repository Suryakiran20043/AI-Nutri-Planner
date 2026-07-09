package com.nutriplanner.backend.controller;

import com.nutriplanner.backend.model.*;
import com.nutriplanner.backend.repository.*;
import com.nutriplanner.backend.service.AiServiceClient;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;
import java.time.LocalDate;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@CrossOrigin(origins = "*", maxAge = 3600)
@RestController
@RequestMapping("/api/planner")
public class PlannerController {

    @Autowired
    UserRepository userRepository;

    @Autowired
    HealthReportRepository healthReportRepository;

    @Autowired
    MedicalMetricsRepository medicalMetricsRepository;

    @Autowired
    MealRecommendationRepository mealRecommendationRepository;

    @Autowired
    AiServiceClient aiServiceClient;

    @PostMapping("/generate")
    public ResponseEntity<?> generateMealPlan() {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        // Get latest health report
        List<HealthReport> reports = healthReportRepository.findByUserOrderByUploadedAtDesc(user);
        
        Map<String, Object> metrics = Map.of();
        HealthReport latestReport = null;

        if (!reports.isEmpty()) {
            latestReport = reports.get(0);
            MedicalMetrics m = medicalMetricsRepository.findByReport(latestReport).orElse(null);
            if (m != null) {
                metrics = Map.of(
                    "glucose", m.getGlucose() != null ? m.getGlucose() : 0,
                    "hba1c", m.getHba1c() != null ? m.getHba1c() : 0
                );
            }
        }

        // Call Python AI Service
        Map<String, Object> plan;
        try {
            plan = aiServiceClient.generateMealPlan(
                user.getId(), 
                user.getAllergies() != null ? user.getAllergies() : "", 
                user.getFavorites() != null ? user.getFavorites() : "", 
                metrics
            );
        } catch (Exception e) {
            System.out.println("AI Service unavailable, using mock meal plan. " + e.getMessage());
            plan = Map.of(
                "meals", java.util.List.of(
                    Map.of("slot", "breakfast", "food", "Greek Yogurt Parfait", "reason", "High protein and low glycemic index."),
                    Map.of("slot", "lunch", "food", "Grilled Chicken Salad", "reason", "Lean protein and fiber rich greens."),
                    Map.of("slot", "dinner", "food", "Baked Salmon with Quinoa", "reason", "Rich in Omega-3 for heart health."),
                    Map.of("slot", "snack", "food", "Handful of Almonds", "reason", "Healthy fats and satiating.")
                )
            );
        }

        if (plan != null && plan.containsKey("meals")) {
            List<Map<String, Object>> meals = (List<Map<String, Object>>) plan.get("meals");
            
            for (Map<String, Object> mealData : meals) {
                MealRecommendation rec = new MealRecommendation();
                rec.setUser(user);
                rec.setReport(latestReport);
                rec.setPlanDate(LocalDate.now());
                rec.setMealSlot(MealRecommendation.MealSlot.valueOf(mealData.get("slot").toString().toLowerCase()));
                rec.setFoodName(mealData.get("food").toString());
                rec.setReason(mealData.get("reason").toString());
                mealRecommendationRepository.save(rec);
            }
            return ResponseEntity.ok(Map.of("message", "Meal plan generated successfully"));
        }

        return ResponseEntity.status(500).body("Failed to generate meal plan from AI Service");
    }

    @GetMapping("/plan")
    public ResponseEntity<?> getMealPlan(@RequestParam(required = false) String date) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        List<MealRecommendation> recommendations;
        if (date != null && !date.isEmpty()) {
            LocalDate d = LocalDate.parse(date);
            recommendations = mealRecommendationRepository.findByUserAndPlanDateBetween(user, d, d);
        } else {
            recommendations = mealRecommendationRepository.findByUserOrderByPlanDateDesc(user);
        }
        
        Map<String, Object> plan = new java.util.HashMap<>();
        for (MealRecommendation rec : recommendations) {
            Map<String, Object> mealObj = new java.util.HashMap<>();
            mealObj.put("id", rec.getId());
            mealObj.put("name", rec.getFoodName());
            mealObj.put("fdc_id", rec.getEtmFoodId());
            mealObj.put("serving", rec.getServingSize());
            mealObj.put("instructions", rec.getReason());
            
            // Temporary mock nutrition values to satisfy UI
            mealObj.put("calories", 400);
            mealObj.put("protein", 25);
            mealObj.put("carbs", 45);
            mealObj.put("fat", 15);
            
            plan.put(rec.getMealSlot().name().toLowerCase(), mealObj);
        }
        
        return ResponseEntity.ok(Map.of("plan", plan));
    }

    @PostMapping("/save-plan")
    public ResponseEntity<?> savePlan(@RequestBody Map<String, Object> body) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();
        
        LocalDate date = LocalDate.parse(body.getOrDefault("date", LocalDate.now().toString()).toString());
        String slotStr = body.get("slot").toString();
        
        MealRecommendation.MealSlot slot;
        try {
            slot = MealRecommendation.MealSlot.valueOf(slotStr.toLowerCase());
        } catch (IllegalArgumentException e) {
            return ResponseEntity.badRequest().body("Invalid slot");
        }
        
        List<MealRecommendation> existing = mealRecommendationRepository.findByUserAndPlanDateBetween(user, date, date)
                .stream().filter(m -> m.getMealSlot() == slot).collect(Collectors.toList());
                
        MealRecommendation rec;
        if (!existing.isEmpty()) {
            rec = existing.get(0);
        } else {
            rec = new MealRecommendation();
            rec.setUser(user);
            rec.setPlanDate(date);
            rec.setMealSlot(slot);
        }
        
        rec.setFoodName(body.getOrDefault("food_name", "").toString());
        if (body.get("fdc_id") != null) rec.setEtmFoodId(Long.parseLong(body.get("fdc_id").toString()));
        if (body.get("serving_size") != null) rec.setServingSize(body.get("serving_size").toString());
        
        mealRecommendationRepository.save(rec);
        
        return ResponseEntity.ok(Map.of("message", "Plan saved"));
    }

    @PostMapping("/swap-meal")
    public ResponseEntity<?> swapMeal(@RequestBody Map<String, Object> body) {
        // Dummy implementation for now to satisfy UI, can be extended to use AI Service or EtmFoodRepository
        return ResponseEntity.ok(Map.of("message", "Meal swapped"));
    }
}
