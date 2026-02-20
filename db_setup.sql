-- =============================================================
-- Cattle Direct Marketplace - Database Setup
-- Run this in phpMyAdmin or MySQL CLI to initialise the DB
-- =============================================================

CREATE DATABASE IF NOT EXISTS cattle_marketplace
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cattle_marketplace;

-- Main listings table
CREATE TABLE IF NOT EXISTS cows (
    id          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    seller_name VARCHAR(120)     NOT NULL,
    phone       VARCHAR(30)      NOT NULL,
    location    VARCHAR(150)     NOT NULL,
    breed       VARCHAR(100)     NOT NULL,
    age         DECIMAL(4,1)     NOT NULL COMMENT 'Age in years',
    weight      DECIMAL(7,2)     NOT NULL COMMENT 'Weight in kg',
    price       DECIMAL(12,2)    NOT NULL COMMENT 'Price in ETB',
    image_path  VARCHAR(300)     NOT NULL,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_breed    (breed),
    INDEX idx_location (location),
    INDEX idx_price    (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
