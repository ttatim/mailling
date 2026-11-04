CREATE TABLE system_errors (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, error_id CHAR(24) NOT NULL, severity ENUM('4xx','5xx') NOT NULL,
 http_status SMALLINT UNSIGNED NOT NULL, route VARCHAR(500) NOT NULL, message VARCHAR(1000) NOT NULL,
 context JSON NULL, notified_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_system_error_id (error_id), KEY ix_system_errors_created (severity,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
