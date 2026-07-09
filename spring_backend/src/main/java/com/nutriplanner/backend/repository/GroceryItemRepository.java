package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.GroceryItem;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.time.LocalDate;
import java.util.List;

@Repository
public interface GroceryItemRepository extends JpaRepository<GroceryItem, Long> {
    List<GroceryItem> findByUserIdAndWeekStart(Long userId, LocalDate weekStart);
    void deleteByUserIdAndWeekStartAndIsCheckedFalse(Long userId, LocalDate weekStart);
    List<GroceryItem> findByUserIdAndWeekStartAndIsCheckedTrue(Long userId, LocalDate weekStart);
}
