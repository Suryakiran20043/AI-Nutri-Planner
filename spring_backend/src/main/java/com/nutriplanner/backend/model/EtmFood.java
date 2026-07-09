package com.nutriplanner.backend.model;

import jakarta.persistence.*;

@Entity
@Table(name = "etm_foods")
public class EtmFood {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "etm_id", unique = true)
    private String etmId;

    private String name;

    @Column(name = "image_url")
    private String imageUrl;

    private Double calories;
    private Double protein;
    
    @Column(name = "total_carbs")
    private Double totalCarbs;
    
    @Column(name = "total_fat")
    private Double totalFat;
    
    @Column(name = "dietary_fiber")
    private Double dietaryFiber;
    
    @Column(name = "serving_size")
    private String servingSize;
    
    @Column(columnDefinition = "TEXT")
    private String directions;

    // Getters and Setters

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public String getEtmId() { return etmId; }
    public void setEtmId(String etmId) { this.etmId = etmId; }

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }

    public String getImageUrl() { return imageUrl; }
    public void setImageUrl(String imageUrl) { this.imageUrl = imageUrl; }

    public Double getCalories() { return calories; }
    public void setCalories(Double calories) { this.calories = calories; }

    public Double getProtein() { return protein; }
    public void setProtein(Double protein) { this.protein = protein; }

    public Double getTotalCarbs() { return totalCarbs; }
    public void setTotalCarbs(Double totalCarbs) { this.totalCarbs = totalCarbs; }

    public Double getTotalFat() { return totalFat; }
    public void setTotalFat(Double totalFat) { this.totalFat = totalFat; }

    public Double getDietaryFiber() { return dietaryFiber; }
    public void setDietaryFiber(Double dietaryFiber) { this.dietaryFiber = dietaryFiber; }

    public String getServingSize() { return servingSize; }
    public void setServingSize(String servingSize) { this.servingSize = servingSize; }

    public String getDirections() { return directions; }
    public void setDirections(String directions) { this.directions = directions; }
}
