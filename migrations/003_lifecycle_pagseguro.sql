ALTER TABLE users ADD COLUMN cancellation_requested_at DATETIME NULL AFTER block_reason;
ALTER TABLE users ADD COLUMN cancelled_at DATETIME NULL AFTER cancellation_requested_at;
ALTER TABLE users ADD COLUMN retained_until DATE NULL AFTER cancelled_at;
ALTER TABLE users ADD COLUMN platform_notification_email_sent_at DATETIME NULL AFTER retained_until;
ALTER TABLE subscriptions ADD COLUMN payment_method ENUM('credit_card','pix','boleto') NULL AFTER provider;
ALTER TABLE subscriptions ADD COLUMN cancel_requested_at DATETIME NULL AFTER cancelled_at;
ALTER TABLE subscriptions ADD COLUMN cancellation_due_at DATETIME NULL AFTER cancel_requested_at;
ALTER TABLE financial_transactions ADD COLUMN payment_url VARCHAR(1000) NULL AFTER provider_reference;

CREATE TABLE user_notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id BIGINT UNSIGNED NOT NULL,type VARCHAR(60) NOT NULL,title VARCHAR(160) NOT NULL,body TEXT NOT NULL,read_at DATETIME NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY ix_notifications_user (user_id,read_at,created_at),CONSTRAINT fk_notifications_user FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
