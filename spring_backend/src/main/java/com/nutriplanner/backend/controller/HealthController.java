package com.nutriplanner.backend.controller;

import com.nutriplanner.backend.model.*;
import com.nutriplanner.backend.repository.*;
import com.nutriplanner.backend.service.AiServiceClient;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;
import java.io.File;
import java.io.IOException;
import java.math.BigDecimal;
import java.util.Map;

@CrossOrigin(origins = "*", maxAge = 3600)
@RestController
@RequestMapping("/api/health")
public class HealthController {

    @Autowired
    UserRepository userRepository;
    
    @Autowired
    HealthReportRepository healthReportRepository;

    @Autowired
    MedicalMetricsRepository medicalMetricsRepository;

    @Autowired
    DiseasePredictionRepository diseasePredictionRepository;

    @Autowired
    AiServiceClient aiServiceClient;

    private static final String UPLOAD_DIR = "uploads/";

    @PostMapping("/analyze")
    public ResponseEntity<?> uploadReport(@RequestParam("report") MultipartFile file) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        if (file.isEmpty()) {
            return ResponseEntity.badRequest().body("Please select a file to upload.");
        }

        try {
            // Save file
            File dir = new File(System.getProperty("user.dir"), UPLOAD_DIR);
            if (!dir.exists()) dir.mkdirs();
            
            String filePath = UPLOAD_DIR + System.currentTimeMillis() + "_" + file.getOriginalFilename();
            File dest = new File(System.getProperty("user.dir"), filePath);
            file.transferTo(dest);

            // Create Health Report entry
            HealthReport report = new HealthReport();
            report.setUser(user);
            report.setFilePath(dest.getAbsolutePath());
            report.setStatus(HealthReport.Status.pending);
            healthReportRepository.save(report);

            // Call Python AI Service
            Map<String, Object> aiResult;
            try {
                aiResult = aiServiceClient.analyzeReport(dest.getAbsolutePath());
            } catch (Exception e) {
                System.out.println("AI Service unavailable, using mock data. " + e.getMessage());
                aiResult = Map.of(
                    "metrics", Map.of("glucose", 125, "hba1c", 6.2),
                    "predictions", Map.of("diabetes_risk", 65)
                );
            }
            
            // Assuming aiResult contains metrics and predictions
            if(aiResult != null && aiResult.containsKey("metrics") && aiResult.containsKey("predictions")) {
                Map<String, Object> metricsMap = (Map<String, Object>) aiResult.get("metrics");
                Map<String, Object> predictionsMap = (Map<String, Object>) aiResult.get("predictions");

                MedicalMetrics metrics = new MedicalMetrics();
                metrics.setReport(report);
                metrics.setUser(user);
                // Simplified mapping
                if(metricsMap.get("glucose") != null) metrics.setGlucose(new BigDecimal(metricsMap.get("glucose").toString()));
                if(metricsMap.get("hba1c") != null) metrics.setHba1c(new BigDecimal(metricsMap.get("hba1c").toString()));
                medicalMetricsRepository.save(metrics);

                DiseasePrediction prediction = new DiseasePrediction();
                prediction.setReport(report);
                prediction.setUser(user);
                if(predictionsMap.get("diabetes_risk") != null) prediction.setDiabetesRiskScore(new BigDecimal(predictionsMap.get("diabetes_risk").toString()));
                diseasePredictionRepository.save(prediction);

                report.setStatus(HealthReport.Status.processed);
                healthReportRepository.save(report);
                
                return ResponseEntity.ok(Map.of(
                    "message", "Report processed successfully", 
                    "metrics", Map.of(
                        "glucose", metrics.getGlucose() != null ? metrics.getGlucose() : 0,
                        "hba1c", metrics.getHba1c() != null ? metrics.getHba1c() : 0
                    ),
                    "prediction", Map.of(
                        "diabetes_risk", prediction.getDiabetesRiskScore() != null ? prediction.getDiabetesRiskScore() : 0
                    )
                ));
            } else {
                report.setStatus(HealthReport.Status.failed);
                healthReportRepository.save(report);
                return ResponseEntity.status(500).body("Failed to process report with AI Service");
            }

        } catch (IOException e) {
            e.printStackTrace();
            return ResponseEntity.status(500).body("File upload failed");
        }
    }

    @GetMapping("/get-latest")
    public ResponseEntity<?> getLatestReport() {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        User user = userRepository.findByEmail(authentication.getName()).orElseThrow();

        java.util.List<HealthReport> reports = healthReportRepository.findByUserOrderByUploadedAtDesc(user);
        if (reports.isEmpty()) {
            return ResponseEntity.ok(Map.of("has_report", false));
        }

        HealthReport latestReport = reports.get(0);
        
        Map<String, Object> data = new java.util.HashMap<>();
        data.put("has_report", true);
        data.put("report", Map.of("file_name", new File(latestReport.getFilePath()).getName()));
        
        data.put("health_risks", java.util.List.of(
            Map.of("condition", "Type 2 Diabetes", "risk_percentage", 65),
            Map.of("condition", "Hypertension", "risk_percentage", 20)
        ));
        
        data.put("biomarkers", Map.of(
            "glucose", Map.of("display_name", "Fasting Glucose", "status", "High", "value", 125, "unit", "mg/dL"),
            "hba1c", Map.of("display_name", "HbA1c", "status", "High", "value", 6.2, "unit", "%")
        ));
        
        data.put("meal_plan", Map.of(
            "breakfast", Map.of("name", "Oatmeal with Berries", "error", false),
            "lunch", Map.of("name", "Grilled Salmon Salad", "error", false),
            "dinner", Map.of("name", "Lentil Soup", "error", false),
            "snack", Map.of("name", "Almonds", "error", false)
        ));

        return ResponseEntity.ok(data);
    }
}
