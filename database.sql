-- College Event Management System : database schema
-- Import this file in phpMyAdmin (Import tab) or run: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS college_events CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE college_events;

CREATE TABLE IF NOT EXISTS admin (
  admin_id  INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(100) NOT NULL,
  email     VARCHAR(120) NOT NULL UNIQUE,
  password  VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(120) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  phone      VARCHAR(15)  NOT NULL
) ENGINE=InnoDB;

-- ER diagram: Admin "Manages" Event
CREATE TABLE IF NOT EXISTS events (
  event_id    INT AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT NULL,
  category    VARCHAR(50)  NOT NULL DEFAULT 'Technical',
  event_name  VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  event_date  DATE NOT NULL,
  event_time  TIME NOT NULL,
  venue       VARCHAR(150) NOT NULL,
  organizer   VARCHAR(120) NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_admin FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ER diagram: Student "Registers For" Event  (Event "Has" Registration)
CREATE TABLE IF NOT EXISTS registrations (
  registration_id   INT AUTO_INCREMENT PRIMARY KEY,
  student_id        INT NOT NULL,
  event_id          INT NOT NULL,
  registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_event (student_id, event_id),
  CONSTRAINT fk_reg_student FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
  CONSTRAINT fk_reg_event   FOREIGN KEY (event_id)   REFERENCES events(event_id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ER diagram: Registration "Has" Attendance (a row exists only once attendance is marked)
CREATE TABLE IF NOT EXISTS attendance (
  attendance_id   INT AUTO_INCREMENT PRIMARY KEY,
  registration_id INT NOT NULL UNIQUE,
  status          ENUM('Present','Absent') NOT NULL,
  CONSTRAINT fk_att_reg FOREIGN KEY (registration_id) REFERENCES registrations(registration_id) ON DELETE CASCADE
) ENGINE=InnoDB;
