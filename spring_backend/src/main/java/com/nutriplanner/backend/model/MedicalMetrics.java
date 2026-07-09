package com.nutriplanner.backend.model;

import jakarta.persistence.*;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "medical_metrics")
public class MedicalMetrics {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "report_id", nullable = false)
    private HealthReport report;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    private BigDecimal glucose;
    private BigDecimal hba1c;
    private BigDecimal cholesterol;
    private BigDecimal hdl;
    private BigDecimal ldl;
    private BigDecimal triglycerides;
    private BigDecimal creatinine;
    private BigDecimal urea;
    private BigDecimal egfr;
    private BigDecimal tsh;

    @Column(name = "extracted_at")
    private LocalDateTime extractedAt = LocalDateTime.now();

    // Getters and Setters

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public HealthReport getReport() { return report; }
    public void setReport(HealthReport report) { this.report = report; }

    public User getUser() { return user; }
    public void setUser(User user) { this.user = user; }

    public BigDecimal getGlucose() { return glucose; }
    public void setGlucose(BigDecimal glucose) { this.glucose = glucose; }

    public BigDecimal getHba1c() { return hba1c; }
    public void setHba1c(BigDecimal hba1c) { this.hba1c = hba1c; }

    public BigDecimal getCholesterol() { return cholesterol; }
    public void setCholesterol(BigDecimal cholesterol) { this.cholesterol = cholesterol; }

    public BigDecimal getHdl() { return hdl; }
    public void setHdl(BigDecimal hdl) { this.hdl = hdl; }

    public BigDecimal getLdl() { return ldl; }
    public void setLdl(BigDecimal ldl) { this.ldl = ldl; }

    public BigDecimal getTriglycerides() { return triglycerides; }
    public void setTriglycerides(BigDecimal triglycerides) { this.triglycerides = triglycerides; }

    public BigDecimal getCreatinine() { return creatinine; }
    public void setCreatinine(BigDecimal creatinine) { this.creatinine = creatinine; }

    public BigDecimal getUrea() { return urea; }
    public void setUrea(BigDecimal urea) { this.urea = urea; }

    public BigDecimal getEgfr() { return egfr; }
    public void setEgfr(BigDecimal egfr) { this.egfr = egfr; }

    public BigDecimal getTsh() { return tsh; }
    public void setTsh(BigDecimal tsh) { this.tsh = tsh; }

    public LocalDateTime getExtractedAt() { return extractedAt; }
    public void setExtractedAt(LocalDateTime extractedAt) { this.extractedAt = extractedAt; }
}
