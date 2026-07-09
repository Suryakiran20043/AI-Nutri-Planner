package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.HealthReport;
import com.nutriplanner.backend.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface HealthReportRepository extends JpaRepository<HealthReport, Long> {
    List<HealthReport> findByUserOrderByUploadedAtDesc(User user);
}
