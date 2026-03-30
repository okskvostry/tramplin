-- ============================================
-- БАЗА ДАННЫХ ДЛЯ ПЛАТФОРМЫ "ТРАМПЛИН"
-- ============================================

-- 1. Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('applicant', 'employer', 'curator') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Таблица соискателей
CREATE TABLE IF NOT EXISTS applicants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    last_name VARCHAR(255),
    first_name VARCHAR(255),
    patronymic VARCHAR(255),
    university VARCHAR(255),
    graduation_year VARCHAR(4),
    studying_now BOOLEAN DEFAULT FALSE,
    birth_date DATE,
    city VARCHAR(255),
    portfolio VARCHAR(500),
    photo_path VARCHAR(500),
    resume_path VARCHAR(500),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Таблица навыков
CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- 4. Таблица связи соискателей и навыков
CREATE TABLE IF NOT EXISTS applicant_skills (
    applicant_id INT NOT NULL,
    skill_id INT NOT NULL,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    PRIMARY KEY (applicant_id, skill_id)
);

-- 5. Таблица работодателей
CREATE TABLE IF NOT EXISTS employers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    company_name VARCHAR(255),
    description TEXT,
    city VARCHAR(255),
    phone VARCHAR(50),
    address VARCHAR(500),
    industry VARCHAR(255),
    logo_path VARCHAR(500),
    office_photo_1 VARCHAR(500),
    office_photo_2 VARCHAR(500),
    office_photo_3 VARCHAR(500),
    office_photo_4 VARCHAR(500),
    office_photo_5 VARCHAR(500),
    inn VARCHAR(12),
    website VARCHAR(255),
    egrul_file VARCHAR(255),
    verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    verified_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Таблица кураторов
CREATE TABLE IF NOT EXISTS curators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    institution VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. Таблица возможностей (вакансии, стажировки, мероприятия, менторские программы)
CREATE TABLE IF NOT EXISTS opportunities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('vacancy', 'internship', 'event', 'mentorship') NOT NULL,
    format ENUM('online', 'offline') NOT NULL,
    city VARCHAR(255),
    start_date DATE,
    time TIME,
    price VARCHAR(100),
    salary_min INT,
    status ENUM('pending', 'active', 'closed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. Таблица связи возможностей и навыков
CREATE TABLE IF NOT EXISTS opportunity_skills (
    opportunity_id INT NOT NULL,
    skill_id INT NOT NULL,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    PRIMARY KEY (opportunity_id, skill_id)
);

-- 9. Добавляем базовые навыки
INSERT IGNORE INTO skills (name) VALUES 
('C#'), ('JavaScript'), ('PHP'), ('Python'),
('MySQL'), ('PostgreSQL'), ('SQL'),
('Excel'), ('NumPy'), ('Pandas'),
('Android (Kotlin)'), ('Flutter'), ('iOS (Swift)'), ('React Native');

-- 10. Добавляем тестового куратора (пароль: curator123)
INSERT IGNORE INTO users (email, name, password_hash, role, created_at) 
VALUES ('curator@tramplin.ru', 'Анна Куратор', '$2y$10$h8z9E3FqG7rJ5kL2mN4pQ6uR8sT0vW2xY4zA6bC8dE0fG2hI4jK6', 'curator', NOW());

INSERT IGNORE INTO curators (user_id, email, password_hash, name, institution) 
SELECT id, email, password_hash, name, 'АлтГУ' FROM users WHERE email = 'curator@tramplin.ru';