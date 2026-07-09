package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.FoodLog;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.time.LocalDate;
import java.util.List;

@Repository
public interface FoodLogRepository extends JpaRepository<FoodLog, Long> {
    List<FoodLog> findByUserIdAndLogDateOrderByLoggedAtDesc(Long userId, LocalDate logDate);
    void deleteByIdAndUserId(Long id, Long userId);
}
