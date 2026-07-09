package com.nutriplanner.backend.model;

import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Entity
@Table(name = "meal_recommendations")
public class MealRecommendation {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "report_id")
    private HealthReport report;

    @Column(name = "plan_date")
    private LocalDate planDate;

    @Enumerated(EnumType.STRING)
    @Column(name = "meal_slot")
    private MealSlot mealSlot;

    @Column(name = "food_name")
    private String foodName;

    @Column(columnDefinition = "TEXT")
    private String reason;

    @Column(name = "is_favorite_match")
    private Boolean isFavoriteMatch;

    @Column(name = "created_at")
    private LocalDateTime createdAt = LocalDateTime.now();

    @Column(name = "serving_size")
    private String servingSize;

    @Column(name = "etm_food_id")
    private Long etmFoodId;

    public enum MealSlot {
        breakfast, lunch, dinner, snack
    }

    // Getters and Setters

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public User getUser() { return user; }
    public void setUser(User user) { this.user = user; }

    public HealthReport getReport() { return report; }
    public void setReport(HealthReport report) { this.report = report; }

    public LocalDate getPlanDate() { return planDate; }
    public void setPlanDate(LocalDate planDate) { this.planDate = planDate; }

    public MealSlot getMealSlot() { return mealSlot; }
    public void setMealSlot(MealSlot mealSlot) { this.mealSlot = mealSlot; }

    public String getFoodName() { return foodName; }
    public void setFoodName(String foodName) { this.foodName = foodName; }

    public String getReason() { return reason; }
    public void setReason(String reason) { this.reason = reason; }

    public Boolean getIsFavoriteMatch() { return isFavoriteMatch; }
    public void setIsFavoriteMatch(Boolean isFavoriteMatch) { this.isFavoriteMatch = isFavoriteMatch; }

    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    public String getServingSize() { return servingSize; }
    public void setServingSize(String servingSize) { this.servingSize = servingSize; }

    public Long getEtmFoodId() { return etmFoodId; }
    public void setEtmFoodId(Long etmFoodId) { this.etmFoodId = etmFoodId; }
}
