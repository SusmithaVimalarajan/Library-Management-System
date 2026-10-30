
CREATE DATABASE IF NOT EXISTS library_management3;


USE library_management3;




CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'librarian',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    index_number VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    -- CR001: Nullable for existing students until a librarian sets their password.
    password_hash VARCHAR(255) DEFAULT NULL,
    grade VARCHAR(50),
    contact_number VARCHAR(20),
    registration_date DATE NOT NULL
);


CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(50),
    category VARCHAR(100),
    quantity INT NOT NULL DEFAULT 1,
    available_quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    -- CR002: Fine is retained with the borrowing when it is returned.
    fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('Issued', 'Returned') NOT NULL DEFAULT 'Issued',

    CONSTRAINT fk_borrowing_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_borrowing_book
        FOREIGN KEY (book_id)
        REFERENCES books(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);



INSERT INTO users (username, password, role)
VALUES (
    'admin',
    '$2y$10$pGi52d1iVOg1zKHjVc8g3Op1txGLNNyRTl46HKwOq7j9SufV5aEQ6',
    'librarian'
);


CREATE TABLE IF NOT EXISTS book_holds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hold_token CHAR(64) NOT NULL UNIQUE,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    hold_time DATETIME NOT NULL,
    expiry_time DATETIME NOT NULL,
    status ENUM('ACTIVE','CANCELLED','EXPIRED','BORROWED') NOT NULL DEFAULT 'ACTIVE',
    notification_sent TINYINT(1) NOT NULL DEFAULT 0,
    notification_sent_at DATETIME NULL,
    notification_claimed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_hold_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_hold_book
        FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_hold_student (student_id),
    INDEX idx_hold_book (book_id),
    INDEX idx_hold_status (status),
    INDEX idx_hold_expiry (expiry_time),
    INDEX idx_hold_notification (notification_sent, notification_claimed_at, hold_time, expiry_time),
    INDEX idx_hold_student_book_status (student_id, book_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



SELECT
    id,
    email,
    status,
    hold_time,
    expiry_time,
    notification_sent,
    notification_sent_at,
    notification_claimed_at,
    NOW() AS server_now,
    TIMESTAMPDIFF(MINUTE, hold_time, NOW()) AS minutes_old
FROM book_holds
ORDER BY id DESC
LIMIT 5;
