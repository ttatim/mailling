ALTER TABLE users ADD COLUMN role ENUM('user','super_admin') NOT NULL DEFAULT 'user' AFTER status;
ALTER TABLE users ADD COLUMN access_blocked_at DATETIME NULL AFTER role;
ALTER TABLE users ADD COLUMN block_reason VARCHAR(255) NULL AFTER access_blocked_at;

CREATE TABLE payment_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id BIGINT UNSIGNED NULL,
  provider VARCHAR(30) NOT NULL,
  provider_event_id VARCHAR(191) NOT NULL,
  event_type VARCHAR(80) NOT NULL,
  payload JSON NOT NULL,
  processed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_provider_event (provider, provider_event_id),
  CONSTRAINT fk_payment_events_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE financial_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  subscription_id BIGINT UNSIGNED NULL,
  provider VARCHAR(30) NOT NULL,
  provider_reference VARCHAR(191) NULL,
  amount_cents INT UNSIGNED NOT NULL,
  status ENUM('pending','paid','overdue','refunded','cancelled') NOT NULL DEFAULT 'pending',
  due_at DATETIME NULL,
  paid_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_financial_status (status,due_at), KEY ix_financial_user (user_id,status),
  CONSTRAINT fk_financial_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_financial_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
