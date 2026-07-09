package com.nutriplanner.backend.payload.request;

public class UserProfileRequest {
    private String name;
    private String allergies;
    private String favorites;
    private String password;
    private Integer age;
    private String gender;
    private Double weight_kg;
    private Double height_cm;
    private String activity_level;
    private String goal;
    private String diet_type;
    private Integer daily_calories;
    private Integer protein_g;
    private Integer carbs_g;
    private Integer fat_g;

    public String getName() { return name; }
    public void setName(String name) { this.name = name; }
    
    public String getAllergies() { return allergies; }
    public void setAllergies(String allergies) { this.allergies = allergies; }
    
    public String getFavorites() { return favorites; }
    public void setFavorites(String favorites) { this.favorites = favorites; }

    public String getPassword() { return password; }
    public void setPassword(String password) { this.password = password; }

    public Integer getAge() { return age; }
    public void setAge(Integer age) { this.age = age; }

    public String getGender() { return gender; }
    public void setGender(String gender) { this.gender = gender; }

    public Double getWeight_kg() { return weight_kg; }
    public void setWeight_kg(Double weight_kg) { this.weight_kg = weight_kg; }

    public Double getHeight_cm() { return height_cm; }
    public void setHeight_cm(Double height_cm) { this.height_cm = height_cm; }

    public String getActivity_level() { return activity_level; }
    public void setActivity_level(String activity_level) { this.activity_level = activity_level; }

    public String getGoal() { return goal; }
    public void setGoal(String goal) { this.goal = goal; }

    public String getDiet_type() { return diet_type; }
    public void setDiet_type(String diet_type) { this.diet_type = diet_type; }

    public Integer getDaily_calories() { return daily_calories; }
    public void setDaily_calories(Integer daily_calories) { this.daily_calories = daily_calories; }

    public Integer getProtein_g() { return protein_g; }
    public void setProtein_g(Integer protein_g) { this.protein_g = protein_g; }

    public Integer getCarbs_g() { return carbs_g; }
    public void setCarbs_g(Integer carbs_g) { this.carbs_g = carbs_g; }

    public Integer getFat_g() { return fat_g; }
    public void setFat_g(Integer fat_g) { this.fat_g = fat_g; }
}
