CREATE DATABASE student_assignment_db;
SHOW DATABASES;
USE student_assignment_db;

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_name VARCHAR(100) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending'
);
SHOW TABLES;
SELECT @@port, @@version, @@datadir;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
ALTER TABLE assignments
ADD COLUMN user_id INT NULL;
DESCRIBE assignments;
SELECT id, assignment_name, subject, due_date, status, user_id
FROM assignments;