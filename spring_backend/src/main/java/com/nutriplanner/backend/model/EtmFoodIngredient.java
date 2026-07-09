package com.nutriplanner.backend.model;

import jakarta.persistence.*;

@Entity
@Table(name = "etm_food_ingredients")
public class EtmFoodIngredient {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "etm_food_id", nullable = false)
    private Long etmFoodId;

    @Column(name = "ingredient_name")
    private String ingredientName;

    @Column(name = "ingredient_amount")
    private String ingredientAmount;

    public Long getId() { return id; }
    public void setId(Long id) { this.id = id; }

    public Long getEtmFoodId() { return etmFoodId; }
    public void setEtmFoodId(Long etmFoodId) { this.etmFoodId = etmFoodId; }

    public String getIngredientName() { return ingredientName; }
    public void setIngredientName(String ingredientName) { this.ingredientName = ingredientName; }

    public String getIngredientAmount() { return ingredientAmount; }
    public void setIngredientAmount(String ingredientAmount) { this.ingredientAmount = ingredientAmount; }
}
