-- MySQL 8.0+ | utf8mb4 required
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(254) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  person_type ENUM('PF','PJ') NOT NULL,
  document_ciphertext TEXT NOT NULL,
  document_hash CHAR(64) NOT NULL,
  email_verified_at DATETIME NULL,
  status ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email), UNIQUE KEY uq_users_document_hash (document_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auth_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL, purpose ENUM('email_verification','password_reset') NOT NULL,
  expires_at DATETIME NOT NULL, used_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_auth_token (token_hash), KEY ix_auth_user_purpose (user_id, purpose),
  CONSTRAINT fk_auth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sender_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
  display_name VARCHAR(150) NOT NULL, from_email VARCHAR(254) NOT NULL, reply_to_email VARCHAR(254) NULL,
  smtp_host VARCHAR(255) NOT NULL, smtp_port SMALLINT UNSIGNED NOT NULL, smtp_username VARCHAR(255) NOT NULL,
  smtp_password_ciphertext TEXT NOT NULL, smtp_security ENUM('none','tls','ssl') NOT NULL DEFAULT 'tls',
  hourly_limit SMALLINT UNSIGNED NOT NULL DEFAULT 30, verified_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sender_user (user_id), CONSTRAINT chk_sender_hourly_limit CHECK (hourly_limit BETWEEN 1 AND 200),
  CONSTRAINT fk_sender_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE plans (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(30) NOT NULL, name VARCHAR(80) NOT NULL,
  amount_cents INT UNSIGNED NOT NULL, duration_days SMALLINT UNSIGNED NULL, is_recurring BOOLEAN NOT NULL DEFAULT FALSE,
  active BOOLEAN NOT NULL DEFAULT TRUE, UNIQUE KEY uq_plans_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE subscriptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, plan_id TINYINT UNSIGNED NOT NULL,
  provider VARCHAR(30) NOT NULL, provider_reference VARCHAR(191) NULL, status ENUM('pending','active','past_due','cancelled','expired') NOT NULL DEFAULT 'pending',
  starts_at DATETIME NULL, ends_at DATETIME NULL, cancelled_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_subscriptions_access (user_id,status,ends_at), UNIQUE KEY uq_provider_reference (provider,provider_reference),
  CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id), CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contacts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, email VARCHAR(254) NOT NULL,
  name VARCHAR(150) NULL, source ENUM('manual','file') NOT NULL, unsubscribe_token CHAR(64) NOT NULL,
  status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, unsubscribed_at DATETIME NULL,
  UNIQUE KEY uq_contact_user_email (user_id,email), UNIQUE KEY uq_contact_token (unsubscribe_token), KEY ix_contacts_listing (user_id,status,email),
  CONSTRAINT fk_contacts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE global_suppression_list (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, email VARCHAR(254) NOT NULL, email_hash CHAR(64) NOT NULL,
  reason VARCHAR(500) NULL, opted_out_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, source_contact_id BIGINT UNSIGNED NULL,
  UNIQUE KEY uq_suppression_email_hash (email_hash), KEY ix_suppression_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE campaigns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, title VARCHAR(150) NOT NULL,
  subject VARCHAR(255) NOT NULL, body_html MEDIUMTEXT NOT NULL, body_text MEDIUMTEXT NOT NULL,
  social_links JSON NULL, status ENUM('draft','queued','running','paused','completed','cancelled') NOT NULL DEFAULT 'draft',
  finalized_at DATETIME NULL, paused_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_campaigns_user_status (user_id,status), CONSTRAINT fk_campaigns_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE campaign_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, campaign_id BIGINT UNSIGNED NOT NULL, storage_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NOT NULL, size_bytes INT UNSIGNED NOT NULL, alt_description VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_campaign_images_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE campaign_batches (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, campaign_id BIGINT UNSIGNED NOT NULL, sequence_number INT UNSIGNED NOT NULL,
  scheduled_at DATETIME NOT NULL, status ENUM('pending','processing','completed','failed','paused') NOT NULL DEFAULT 'pending',
  locked_at DATETIME NULL, locked_by VARCHAR(100) NULL, completed_at DATETIME NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_batch_sequence (campaign_id,sequence_number), KEY ix_batches_worker (status,scheduled_at),
  CONSTRAINT fk_batches_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE campaign_recipients (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, campaign_id BIGINT UNSIGNED NOT NULL, batch_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NULL,
  email VARCHAR(254) NOT NULL, recipient_name VARCHAR(150) NULL, status ENUM('pending','sending','sent','failed','suppressed') NOT NULL DEFAULT 'pending',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0, sent_at DATETIME NULL, last_error TEXT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_campaign_recipient_email (campaign_id,email), KEY ix_recipients_worker (batch_id,status),
  CONSTRAINT fk_recipients_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_recipients_batch FOREIGN KEY (batch_id) REFERENCES campaign_batches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rate_limits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, action VARCHAR(40) NOT NULL, identifier_hash CHAR(64) NOT NULL,
  window_started_at DATETIME NOT NULL, attempts SMALLINT UNSIGNED NOT NULL DEFAULT 1, blocked_until DATETIME NULL,
  UNIQUE KEY uq_rate_limit (action,identifier_hash,window_started_at), KEY ix_rate_block (action,identifier_hash,blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, event_type VARCHAR(80) NOT NULL,
  ip_address VARBINARY(16) NULL, metadata JSON NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_audit_user_event (user_id,event_type,created_at), CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO plans (code,name,amount_cents,duration_days,is_recurring) VALUES
('single','Uso Único',2990,7,FALSE), ('monthly','Mensal',1990,NULL,TRUE);
