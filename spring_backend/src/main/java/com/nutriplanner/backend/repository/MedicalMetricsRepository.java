package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.MedicalMetrics;
import com.nutriplanner.backend.model.HealthReport;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface MedicalMetricsRepository extends JpaRepository<MedicalMetrics, Long> {
    Optional<MedicalMetrics> findByReport(HealthReport report);
}
