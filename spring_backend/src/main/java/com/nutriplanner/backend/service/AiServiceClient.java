package com.nutriplanner.backend.service;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import org.springframework.web.client.RestTemplate;
import org.springframework.http.ResponseEntity;
import java.util.Map;

@Service
public class AiServiceClient {

    @Value("${ai.service.url:http://localhost:8005/api/v1}")
    private String aiServiceUrl;

    private final RestTemplate restTemplate = new RestTemplate();

    public Map<String, Object> analyzeReport(String filePath) {
        String url = aiServiceUrl + "/analyze-report?file_path=" + filePath;
        // In a real scenario, this would likely be a POST request sending the file or file path
        ResponseEntity<Map> response = restTemplate.postForEntity(url, null, Map.class);
        return response.getBody();
    }

    public Map<String, Object> generateMealPlan(Long userId, String allergies, String favorites, Map<String, Object> healthMetrics) {
        String url = aiServiceUrl + "/generate-meal-plan";
        
        // Construct payload
        Map<String, Object> requestBody = Map.of(
            "user_id", userId,
            "allergies", allergies,
            "favorites", favorites,
            "metrics", healthMetrics
        );

        ResponseEntity<Map> response = restTemplate.postForEntity(url, requestBody, Map.class);
        return response.getBody();
    }
}
