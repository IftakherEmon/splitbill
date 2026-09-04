-- =======================================================
-- SplitBill Database Schema
-- A simple database design for a Group Expense Tracker
-- =======================================================

-- Create the database if it doesn't already exist
CREATE DATABASE IF NOT EXISTS splitbill_db;
USE splitbill_db;

-- -------------------------------------------------------
-- 1. USERS TABLE
-- Stores basic registered user account information.
-- Passwords will be securely hashed using PHP password_hash().
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- 2. GROUPS TABLE
-- Stores expense groups (e.g., "Roommates", "Cox's Bazar Tour").
-- 'created_by' references the user who created the group.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `groups` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- 3. GROUP MEMBERS TABLE
-- Many-to-Many relationship table connecting users to groups.
-- A user can be in multiple groups, and a group can have multiple users.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_user (group_id, user_id)
);

-- -------------------------------------------------------
-- 4. EXPENSES TABLE
-- Stores each expense logged inside a group.
-- 'paid_by' is the user ID of whoever paid for the expense upfront.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    paid_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE CASCADE
);

-- -------------------------------------------------------
-- 5. EXPENSE SPLITS TABLE
-- Stores how an individual expense is split among participating members.
-- For example, if an expense is $60 split among 3 people, each gets a record of $20.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS expense_splits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_id INT NOT NULL,
    user_id INT NOT NULL,
    split_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =======================================================
-- STARTER / DEMO DATA (Optional for quick testing & viva)
-- Password for all demo accounts below is: password123
-- The hash below was generated using PHP's password_hash("password123", PASSWORD_DEFAULT)
-- =======================================================

INSERT INTO users (id, name, email, password) VALUES
(1, 'Tanvir Ahmed', 'tanvir@example.com', '$2y$10$wTfZG7s5H57rK2bK5L.qCe53p/8c3Kz5hA9kC/gZt0P1K4b/4w9K2'),
(2, 'Sarah Khan', 'sarah@example.com', '$2y$10$wTfZG7s5H57rK2bK5L.qCe53p/8c3Kz5hA9kC/gZt0P1K4b/4w9K2'),
(3, 'Rahim Chowdhury', 'rahim@example.com', '$2y$10$wTfZG7s5H57rK2bK5L.qCe53p/8c3Kz5hA9kC/gZt0P1K4b/4w9K2')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Demo Group: "Trip to Cox's Bazar"
INSERT INTO `groups` (id, name, description, created_by) VALUES
(1, 'Trip to Cox\'s Bazar', 'Weekend tour expenses and shared bills', 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Add all 3 users as members of the demo group
INSERT INTO group_members (group_id, user_id) VALUES
(1, 1),
(1, 2),
(1, 3)
ON DUPLICATE KEY UPDATE group_id=VALUES(group_id);

-- Demo Expense 1: Hotel Booking ($300 paid by Tanvir, split equally among Tanvir, Sarah, Rahim)
INSERT INTO expenses (id, group_id, paid_by, title, amount, expense_date) VALUES
(1, 1, 1, 'Hotel Ocean View (2 Nights)', 300.00, CURDATE())
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO expense_splits (expense_id, user_id, split_amount) VALUES
(1, 1, 100.00),
(1, 2, 100.00),
(1, 3, 100.00)
ON DUPLICATE KEY UPDATE split_amount=VALUES(split_amount);

-- Demo Expense 2: Seafood Dinner ($90 paid by Sarah, split equally among Tanvir, Sarah, Rahim)
INSERT INTO expenses (id, group_id, paid_by, title, amount, expense_date) VALUES
(2, 1, 2, 'Seafood Dinner at Inani Beach', 90.00, CURDATE())
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO expense_splits (expense_id, user_id, split_amount) VALUES
(2, 1, 30.00),
(2, 2, 30.00),
(2, 3, 30.00)
ON DUPLICATE KEY UPDATE split_amount=VALUES(split_amount);
