package com.nutriplanner.backend.repository;

import com.nutriplanner.backend.model.EtmFood;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface EtmFoodRepository extends JpaRepository<EtmFood, Long> {
    Page<EtmFood> findByNameContainingIgnoreCase(String name, Pageable pageable);
    Optional<EtmFood> findByEtmId(String etmId);
}
