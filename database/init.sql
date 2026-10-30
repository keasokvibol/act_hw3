
CREATE DATABASE IF NOT EXISTS iqa_hw2_db;

USE iqa_hw2_db;

CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

INSERT INTO departments (department_name, description)
VALUES
    ('Human Resources', 'Manages staff and recruitment'),
    ('Information Technology', 'Manages computers and systems'),
    ('Finance', 'Manages budgets and financial records');
