CREATE DATABASE IF NOT EXISTS tmdb_agc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tmdb_agc;

-- USERS
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  nohp VARCHAR(30) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  status ENUM('free','premium') DEFAULT 'free',
  role ENUM('user','admin') DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email, nohp, password, status, role)
VALUES (
  'admin',
  'admin@example.com',
  '08123456789',
  '$2y$10$yCp5hDHqwtd7VdkfzNRXkOT1api3L7zFaUCzAeYE1eQUVOVvyYau6', -- 'admin123'
  'premium',
  'admin'
);

-- SITES (domain milik user)
CREATE TABLE sites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  domain VARCHAR(190) NOT NULL UNIQUE,
  site_name VARCHAR(150) NOT NULL,
  tagline VARCHAR(255) DEFAULT '',
  meta_description VARCHAR(255) DEFAULT '',
  ad_header TEXT DEFAULT NULL,
  ad_between_grid TEXT DEFAULT NULL,
  theme ENUM('classic','cinema','cards') NOT NULL DEFAULT 'classic',
  status ENUM('active','disabled') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- GLOBAL SETTINGS
CREATE TABLE global_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT DEFAULT NULL
);

INSERT INTO global_settings (setting_key, setting_value) VALUES
('tmdb_api_key', ''),
('items_per_page', '24'),
('premium_price_idr', '30000');

-- MOVIES (cache TMDB)
CREATE TABLE movies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tmdb_id INT NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  overview TEXT,
  poster_path VARCHAR(255),
  backdrop_path VARCHAR(255),
  release_date DATE NULL,
  vote_average DECIMAL(3,1) DEFAULT 0.0,
  original_language VARCHAR(10),
  popularity DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug(slug),
  INDEX idx_popularity(popularity)
);

-- DAILY STATS PER SITE
CREATE TABLE site_stats_daily (
  site_id INT NOT NULL,
  stat_date DATE NOT NULL,
  page_views INT NOT NULL DEFAULT 0,
  PRIMARY KEY (site_id, stat_date),
  FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- PAYMENT SETTINGS
CREATE TABLE payment_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  method ENUM('qris','bank','ewallet') NOT NULL,
  receiver_name VARCHAR(100) NOT NULL,
  receiver_number VARCHAR(100) NOT NULL,
  extra_info TEXT DEFAULT NULL
);

ALTER TABLE payment_settings ADD UNIQUE KEY uniq_method(method);

-- PAYMENT ORDERS
CREATE TABLE payment_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  base_amount INT NOT NULL,
  final_amount INT NOT NULL,
  method ENUM('qris','bank','ewallet') NOT NULL,
  status ENUM('pending','paid','canceled') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  paid_at DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ACTIVITY LOGS
CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  meta TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_created_at (user_id, created_at)
);
