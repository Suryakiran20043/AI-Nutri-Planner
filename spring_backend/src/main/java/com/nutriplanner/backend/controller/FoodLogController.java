package com.nutriplanner.backend.controller;

import com.nutriplanner.backend.model.FoodLog;
import com.nutriplanner.backend.model.User;
import com.nutriplanner.backend.repository.FoodLogRepository;
import com.nutriplanner.backend.repository.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDate;
import java.util.List;
import java.util.Map;

@CrossOrigin(origins = "*", maxAge = 3600)
@RestController
@RequestMapping("/api/food-log")
public class FoodLogController {

    @Autowired
    FoodLogRepository foodLogRepository;

    @Autowired
    UserRepository userRepository;

    @PostMapping("/log-meal")
    public ResponseEntity<?> logMeal(@RequestBody Map<String, Object> body) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        FoodLog log = new FoodLog();
        log.setUserId(user.getId());
        log.setLogDate(LocalDate.parse(body.get("date").toString()));
        log.setFoodName(body.get("food_name").toString());
        
        if (body.get("calories") != null) log.setCalories(Double.parseDouble(body.get("calories").toString()));
        if (body.get("protein") != null) log.setProtein(Double.parseDouble(body.get("protein").toString()));
        if (body.get("carbs") != null) log.setCarbs(Double.parseDouble(body.get("carbs").toString()));
        if (body.get("fat") != null) log.setFat(Double.parseDouble(body.get("fat").toString()));

        foodLogRepository.save(log);

        return ResponseEntity.ok(Map.of("message", "Meal logged successfully"));
    }

    @GetMapping("/get-log")
    public ResponseEntity<?> getLog(@RequestParam String date) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        LocalDate d = LocalDate.parse(date);
        List<FoodLog> logs = foodLogRepository.findByUserIdAndLogDateOrderByLoggedAtDesc(user.getId(), d);

        Double totalCalories = 0.0, totalProtein = 0.0, totalCarbs = 0.0, totalFat = 0.0;
        for (FoodLog l : logs) {
            if (l.getCalories() != null) totalCalories += l.getCalories();
            if (l.getProtein() != null) totalProtein += l.getProtein();
            if (l.getCarbs() != null) totalCarbs += l.getCarbs();
            if (l.getFat() != null) totalFat += l.getFat();
        }

        return ResponseEntity.ok(Map.of(
            "items", logs,
            "totals", Map.of(
                "calories", totalCalories,
                "protein", totalProtein,
                "carbs", totalCarbs,
                "fat", totalFat
            )
        ));
    }

    @Transactional
    @PostMapping("/delete-log")
    public ResponseEntity<?> deleteLog(@RequestBody Map<String, Object> body) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        Long id = Long.parseLong(body.get("id").toString());
        foodLogRepository.deleteByIdAndUserId(id, user.getId());

        return ResponseEntity.ok(Map.of("message", "Log deleted"));
    }
}
