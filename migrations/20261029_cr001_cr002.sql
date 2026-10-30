-- CR001: Add a password hash for student self-service accounts.
ALTER TABLE students
    ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL AFTER name;

-- CR002: Persist the fine calculated when an issued book is returned.
ALTER TABLE borrowings
    ADD COLUMN fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER return_date;
