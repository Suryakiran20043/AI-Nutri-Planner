package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.MealRecommendation;
import com.nutriplanner.backend.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import java.time.LocalDate;
import java.util.List;

@Repository
public interface MealRecommendationRepository extends JpaRepository<MealRecommendation, Long> {
    List<MealRecommendation> findByUserOrderByPlanDateDesc(User user);
    List<MealRecommendation> findByUserAndPlanDateBetween(User user, LocalDate startDate, LocalDate endDate);
}
