-- UPGRADE DATABASE SCHEMA FOR HEALTH-TECH UPGRADE
-- Execute these queries against your `nutriplan` database to support report uploads, biomarkers, and health risk storage.

USE nutriplan;

-- 1. Track uploaded health reports
CREATE TABLE IF NOT EXISTS user_health_reports (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  user_id       INT NOT NULL,
  file_name     VARCHAR(255) NOT NULL,
  file_path     VARCHAR(500) NOT NULL, -- Path to securely stored file (AES encrypted)
  status        ENUM('PENDING', 'PROCESSED', 'FAILED') DEFAULT 'PENDING',
  raw_text      LONGTEXT DEFAULT NULL, -- Full extracted text for backup
  overall_risk_score INT DEFAULT NULL,
  uploaded_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at  TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Store parsed clinical biomarkers
CREATE TABLE IF NOT EXISTS user_biomarkers (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  report_id       INT NOT NULL,
  user_id         INT NOT NULL,
  biomarker_name  VARCHAR(100) NOT NULL, -- e.g., 'glucose', 'ldl_cholesterol', 'hemoglobin'
  measured_value  DECIMAL(10,2) NOT NULL,
  unit            VARCHAR(50) NOT NULL,  -- e.g., 'mg/dL', 'g/dL', 'mmHg'
  reference_range VARCHAR(100) NOT NULL, -- e.g., '70-99', '12.0-17.5'
  status          ENUM('NORMAL', 'HIGH', 'LOW') NOT NULL,
  FOREIGN KEY (report_id) REFERENCES user_health_reports(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Store predicted health risks and dietary rules
CREATE TABLE IF NOT EXISTS user_health_risks (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  report_id      INT NOT NULL,
  user_id        INT NOT NULL,
  risk_condition VARCHAR(150) NOT NULL, -- e.g., 'Iron Deficiency Anemia Risk', 'Prediabetes Risk'
  severity       ENUM('LOW', 'MODERATE', 'HIGH') NOT NULL,
  risk_percentage DECIMAL(5,2) DEFAULT NULL,
  dietary_rules  TEXT NOT NULL,          -- JSON string of dietary guidelines (avoid/recommend foods)
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES user_health_reports(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
