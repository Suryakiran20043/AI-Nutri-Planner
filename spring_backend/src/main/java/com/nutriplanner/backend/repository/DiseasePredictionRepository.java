package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.DiseasePrediction;
import com.nutriplanner.backend.model.HealthReport;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface DiseasePredictionRepository extends JpaRepository<DiseasePrediction, Long> {
    Optional<DiseasePrediction> findByReport(HealthReport report);
}
