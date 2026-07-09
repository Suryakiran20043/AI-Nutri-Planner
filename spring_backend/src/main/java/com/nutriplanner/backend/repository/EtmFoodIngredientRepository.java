package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.EtmFoodIngredient;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface EtmFoodIngredientRepository extends JpaRepository<EtmFoodIngredient, Long> {
    List<EtmFoodIngredient> findByEtmFoodId(Long etmFoodId);
}
