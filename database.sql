-- database.sql
-- Run this SQL in phpMyAdmin to create the database and product table.

CREATE DATABASE IF NOT EXISTS `trade_lokal` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `trade_lokal`;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `seller` VARCHAR(255) NOT NULL,
  `owner_id` INT UNSIGNED NULL DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image_url` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_products_owner` (`owner_id`),
  CONSTRAINT `fk_products_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('customer','seller') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`name`, `category`, `seller`, `price`, `image_url`, `description`) VALUES
('Sweet Banana Chips', 'Food', 'Lolas Foodery', 120.00, 'https://placehold.co/400x300/FF9800/white?text=Banana+Chips', 'Crispy local banana chips made from fresh bananas.'),
('Rattan Basket', 'Handmade', 'Manay Lydia', 450.00, 'https://placehold.co/400x300/8D6E63/white?text=Woven+Basket', 'Handwoven basket perfect for home storage and gifts.'),
('Barong Tagalog', 'Clothing', 'Philippines Weave', 2500.00, 'https://placehold.co/400x300/EEE/333?text=Barong+Tagalog', 'Traditional Filipino formal wear made from fine fabric.'),
('Banana Ketchup', 'Food', 'Lutong Bahay Co.', 45.00, 'https://placehold.co/400x300/D32F2F/white?text=Banana+Ketchup', 'Sweet banana ketchup made from ripe bananas and spices.'),
('Organic Rice', 'Agriculture', 'Green Farm Co.', 650.00, 'https://placehold.co/400x300/8BC34A/white?text=Organic+Rice', 'Premium organic rice grown using sustainable farming methods.');
