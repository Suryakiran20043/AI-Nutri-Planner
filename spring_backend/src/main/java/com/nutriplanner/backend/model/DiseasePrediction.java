package com.nutriplanner.backend.model;

import jakarta.persistence.*;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "disease_predictions")
public class DiseasePrediction {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "report_id", nullable = false)
    private HealthReport report;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @Column(name = "diabetes_risk_score")
    private BigDecimal diabetesRiskScore;

    @Column(name = "heart_disease_risk_score")
    private BigDecimal heartDiseaseRiskScore;

    @Column(name = "kidney_disease_risk_score")
    private BigDecimal kidneyDiseaseRiskScore;

    @Column(name = "thyroid_risk_score")
    private BigDecimal thyroidRiskScore;

    @Column(name = "calculated_at")
    private LocalDateTime calculatedAt = LocalDateTime.now();

    // Getters and Setters

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public HealthReport getReport() { return report; }
    public void setReport(HealthReport report) { this.report = report; }

    public User getUser() { return user; }
    public void setUser(User user) { this.user = user; }

    public BigDecimal getDiabetesRiskScore() { return diabetesRiskScore; }
    public void setDiabetesRiskScore(BigDecimal diabetesRiskScore) { this.diabetesRiskScore = diabetesRiskScore; }

    public BigDecimal getHeartDiseaseRiskScore() { return heartDiseaseRiskScore; }
    public void setHeartDiseaseRiskScore(BigDecimal heartDiseaseRiskScore) { this.heartDiseaseRiskScore = heartDiseaseRiskScore; }

    public BigDecimal getKidneyDiseaseRiskScore() { return kidneyDiseaseRiskScore; }
    public void setKidneyDiseaseRiskScore(BigDecimal kidneyDiseaseRiskScore) { this.kidneyDiseaseRiskScore = kidneyDiseaseRiskScore; }

    public BigDecimal getThyroidRiskScore() { return thyroidRiskScore; }
    public void setThyroidRiskScore(BigDecimal thyroidRiskScore) { this.thyroidRiskScore = thyroidRiskScore; }

    public LocalDateTime getCalculatedAt() { return calculatedAt; }
    public void setCalculatedAt(LocalDateTime calculatedAt) { this.calculatedAt = calculatedAt; }
}
