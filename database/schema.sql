CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'teacher') NOT NULL DEFAULT 'teacher',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oauth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    access_token_encrypted TEXT NOT NULL,
    refresh_token_encrypted TEXT NULL,
    expiry_date TIMESTAMP NULL,
    scopes JSON NULL,
    token_type VARCHAR(255) NULL,
    last_refreshed_at TIMESTAMP NULL,
    revoked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT oauth_tokens_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    course_id VARCHAR(255) NOT NULL,
    coursework_id VARCHAR(255) NULL,
    classroom_link VARCHAR(255) NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    max_score DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    file_id VARCHAR(255) NULL,
    drive_folder_id VARCHAR(255) NULL,
    question_drive_file_id VARCHAR(255) NULL,
    rubric_drive_file_id VARCHAR(255) NULL,
    answer_key_drive_file_id VARCHAR(255) NULL,
    question_local_path VARCHAR(255) NULL,
    rubric_local_path VARCHAR(255) NULL,
    answer_key_local_path VARCHAR(255) NULL,
    auto_approval TINYINT(1) NOT NULL DEFAULT 0,
    auto_email TINYINT(1) NOT NULL DEFAULT 0,
    grade_mode ENUM('none', 'draft', 'final') NOT NULL DEFAULT 'draft',
    min_answer_length INT UNSIGNED NOT NULL DEFAULT 120,
    due_date DATETIME NULL,
    close_on_due TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft', 'ready', 'grading', 'completed', 'failed') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX assignments_teacher_course_index (teacher_id, course_id),
    CONSTRAINT assignments_teacher_id_foreign FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grading_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    student_id VARCHAR(255) NOT NULL,
    student_email VARCHAR(255) NULL,
    score_ai DECIMAL(6,2) NULL,
    confidence ENUM('LOW', 'MEDIUM', 'HIGH') NOT NULL DEFAULT 'MEDIUM',
    needs_review TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('APPROVED', 'REVIEW', 'FAILED') NOT NULL DEFAULT 'REVIEW',
    extraction_status ENUM('PENDING', 'SUCCESS', 'PARTIAL', 'FAILED') NOT NULL DEFAULT 'PENDING',
    extracted_text_length INT UNSIGNED NOT NULL DEFAULT 0,
    output_json_valid TINYINT(1) NOT NULL DEFAULT 0,
    reason TEXT NULL,
    feedback_email LONGTEXT NULL,
    rubric_breakdown JSON NULL,
    raw_llm_output JSON NULL,
    email_sent TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY grading_results_assignment_student_unique (assignment_id, student_id),
    CONSTRAINT grading_results_assignment_id_foreign FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    resource VARCHAR(255) NULL,
    status ENUM('SUCCESS', 'FAILED', 'INFO') NOT NULL DEFAULT 'INFO',
    message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX audit_logs_user_action_index (user_id, action),
    CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
