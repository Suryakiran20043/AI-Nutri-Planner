package com.nutriplanner.backend.model;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "users")
public class User {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    private String name;

    @Column(unique = true, nullable = false)
    private String email;

    @Column(name = "password_hash", nullable = false)
    private String passwordHash;

    @Column(columnDefinition = "json")
    private String allergies; // Storing as JSON string for simplicity, or we can use custom converters

    @Column(columnDefinition = "json")
    private String favorites;

    private Integer age;
    private String gender;
    private Double weightKg;
    private Double heightCm;
    private String activityLevel;
    private String goal;
    private String dietType;
    private Integer dailyCalories;
    private Integer proteinG;
    private Integer carbsG;
    private Integer fatG;

    @Column(name = "created_at")
    private LocalDateTime createdAt = LocalDateTime.now();

    // Getters and Setters

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getPasswordHash() { return passwordHash; }
    public void setPasswordHash(String passwordHash) { this.passwordHash = passwordHash; }

    public String getAllergies() { return allergies; }
    public void setAllergies(String allergies) { this.allergies = allergies; }

    public String getFavorites() { return favorites; }
    public void setFavorites(String favorites) { this.favorites = favorites; }

    public LocalDateTime getCreatedAt() { return createdAt; }
    public void setCreatedAt(LocalDateTime createdAt) { this.createdAt = createdAt; }

    public Integer getAge() { return age; }
    public void setAge(Integer age) { this.age = age; }

    public String getGender() { return gender; }
    public void setGender(String gender) { this.gender = gender; }

    public Double getWeightKg() { return weightKg; }
    public void setWeightKg(Double weightKg) { this.weightKg = weightKg; }

    public Double getHeightCm() { return heightCm; }
    public void setHeightCm(Double heightCm) { this.heightCm = heightCm; }

    public String getActivityLevel() { return activityLevel; }
    public void setActivityLevel(String activityLevel) { this.activityLevel = activityLevel; }

    public String getGoal() { return goal; }
    public void setGoal(String goal) { this.goal = goal; }

    public String getDietType() { return dietType; }
    public void setDietType(String dietType) { this.dietType = dietType; }

    public Integer getDailyCalories() { return dailyCalories; }
    public void setDailyCalories(Integer dailyCalories) { this.dailyCalories = dailyCalories; }

    public Integer getProteinG() { return proteinG; }
    public void setProteinG(Integer proteinG) { this.proteinG = proteinG; }

    public Integer getCarbsG() { return carbsG; }
    public void setCarbsG(Integer carbsG) { this.carbsG = carbsG; }

    public Integer getFatG() { return fatG; }
    public void setFatG(Integer fatG) { this.fatG = fatG; }
}
