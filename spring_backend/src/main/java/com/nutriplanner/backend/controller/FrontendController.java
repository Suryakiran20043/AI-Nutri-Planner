package com.nutriplanner.backend.controller;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;

@Controller
public class FrontendController {

    @GetMapping("/")
    public String index() {
        return "index";
    }

    @GetMapping("/calculator")
    public String calculator() {
        return "calculator";
    }

    @GetMapping("/dashboard")
    public String dashboard() {
        return "dashboard";
    }

    @GetMapping("/settings")
    public String settings() {
        return "settings";
    }

    @GetMapping("/grocery")
    public String grocery() {
        return "grocery";
    }

    @GetMapping("/food-search")
    public String foodSearch() {
        return "food-search";
    }

    @GetMapping("/browse-foods")
    public String browseFoods() {
        return "browse-foods";
    }

    @GetMapping("/health-report")
    public String healthReport() {
        return "health-report";
    }

    @GetMapping("/planner")
    public String planner() {
        return "planner";
    }

    @GetMapping("/progress")
    public String progress() {
        return "progress";
    }

    @GetMapping("/recipe")
    public String recipe() {
        return "recipe";
    }
}
