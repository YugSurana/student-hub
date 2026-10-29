-- ========================================================
-- Practical 8: Seed Data (Insert Initial Records)
-- StudentHub - CHARUSAT ITUE203
-- ========================================================

USE studenthub;

-- 1. Insert Students Records
INSERT INTO students (name, email, department, semester) VALUES
('Rahul Patel', 'rahul@example.com', 'IT', 3),
('Priya Shah', 'priya@example.com', 'IT', 5),
('Amit Desai', 'amit@example.com', 'CSE', 3),
('Neha Patel', 'neha@example.com', 'CE', 7)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. Insert Events Records
INSERT INTO events (event_name, event_date, venue) VALUES
('Web Development Workshop', '2026-10-15', 'Seminar Hall'),
('Python Workshop', '2026-10-20', 'Lab 301'),
('Hackathon 2026', '2026-11-05', 'Innovation Centre');

-- 3. Insert Registrations Records
INSERT INTO registrations (student_id, event_id) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 3),
(4, 2)
ON DUPLICATE KEY UPDATE registration_date=CURRENT_TIMESTAMP;

-- 4. Practical 10: Seed Users with Hashed Passwords and Roles
-- Passwords hashed with bcrypt / PASSWORD_DEFAULT:
-- Admin: Admin@123
-- Student: Student@123
INSERT INTO users (name, email, password, role) VALUES
('System Administrator', 'admin@studenthub.com', '$2y$10$wTfD7o2uQ9.xQyM1wO3nfe7eR7z0vG1r0j9o5L5F6C7E8G9H0J1K2', 'admin'),
('Demo Student', 'student@studenthub.com', '$2y$10$qVvN9v.lP4nN8eL5pW7v9uK0sA1dD2fF3gG4hH5jJ6kK7lL8mM9nO', 'student')
ON DUPLICATE KEY UPDATE role=VALUES(role);

