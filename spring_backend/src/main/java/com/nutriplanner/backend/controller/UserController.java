package com.nutriplanner.backend.controller;

import com.nutriplanner.backend.model.User;
import com.nutriplanner.backend.payload.request.UserProfileRequest;
import com.nutriplanner.backend.payload.response.MessageResponse;
import com.nutriplanner.backend.repository.UserRepository;
import com.nutriplanner.backend.security.services.UserDetailsImpl;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.web.bind.annotation.*;

import java.util.Optional;

@CrossOrigin(origins = "*", maxAge = 3600)
@RestController
@RequestMapping("/api/user")
public class UserController {

    @Autowired
    UserRepository userRepository;

    @GetMapping("/profile")
    public ResponseEntity<?> getUserProfile() {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        String currentPrincipalName = authentication.getName();
        
        Optional<User> userOptional = userRepository.findByEmail(currentPrincipalName);
        
        if (userOptional.isPresent()) {
            User user = userOptional.get();
            // Don't return password hash
            user.setPasswordHash(null);
            return ResponseEntity.ok(user);
        } else {
            return ResponseEntity.status(404).body(new MessageResponse("Error: User not found"));
        }
    }

    @Autowired
    org.springframework.security.crypto.password.PasswordEncoder encoder;

    @PutMapping("/profile")
    public ResponseEntity<?> updateUserProfile(@RequestBody UserProfileRequest profileRequest) {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        String currentPrincipalName = authentication.getName();

        Optional<User> userOptional = userRepository.findByEmail(currentPrincipalName);

        if (userOptional.isPresent()) {
            User user = userOptional.get();
            if (profileRequest.getName() != null) user.setName(profileRequest.getName());
            if (profileRequest.getAllergies() != null) user.setAllergies(profileRequest.getAllergies());
            if (profileRequest.getFavorites() != null) user.setFavorites(profileRequest.getFavorites());
            
            if (profileRequest.getPassword() != null && !profileRequest.getPassword().isEmpty()) {
                user.setPasswordHash(encoder.encode(profileRequest.getPassword()));
            }

            if (profileRequest.getAge() != null) user.setAge(profileRequest.getAge());
            if (profileRequest.getGender() != null) user.setGender(profileRequest.getGender());
            if (profileRequest.getWeight_kg() != null) user.setWeightKg(profileRequest.getWeight_kg());
            if (profileRequest.getHeight_cm() != null) user.setHeightCm(profileRequest.getHeight_cm());
            if (profileRequest.getActivity_level() != null) user.setActivityLevel(profileRequest.getActivity_level());
            if (profileRequest.getGoal() != null) user.setGoal(profileRequest.getGoal());
            if (profileRequest.getDiet_type() != null) user.setDietType(profileRequest.getDiet_type());
            if (profileRequest.getDaily_calories() != null) user.setDailyCalories(profileRequest.getDaily_calories());
            if (profileRequest.getProtein_g() != null) user.setProteinG(profileRequest.getProtein_g());
            if (profileRequest.getCarbs_g() != null) user.setCarbsG(profileRequest.getCarbs_g());
            if (profileRequest.getFat_g() != null) user.setFatG(profileRequest.getFat_g());

            userRepository.save(user);
            return ResponseEntity.ok(new MessageResponse("User profile updated successfully!"));
        } else {
            return ResponseEntity.status(404).body(new MessageResponse("Error: User not found"));
        }
    }
}
