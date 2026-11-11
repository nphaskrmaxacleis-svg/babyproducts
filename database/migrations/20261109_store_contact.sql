CREATE TABLE IF NOT EXISTS store_contact_channels (
    code VARCHAR(24) NOT NULL PRIMARY KEY,
    value VARCHAR(600) NOT NULL DEFAULT '',
    enabled BOOLEAN NOT NULL DEFAULT 0,
    verified BOOLEAN NOT NULL DEFAULT 0,
    verified_by INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    updated_by INT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_store_contact_verified_by FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_store_contact_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    submission_token CHAR(64) NOT NULL UNIQUE,
    sender_hash CHAR(64) NOT NULL,
    user_id INT UNSIGNED NULL,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(30) NULL,
    subject VARCHAR(160) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new','in_progress','replied') NOT NULL DEFAULT 'new',
    privacy_notice_version VARCHAR(20) NOT NULL DEFAULT '2026-11-09',
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_contact_message_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_contact_message_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_contact_status_date (status,created_at),
    INDEX idx_contact_sender_date (sender_hash,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO store_contact_channels(code,value,enabled,verified) VALUES
('phone','063-674-5398',1,1),
('email','nphaskrmaxacleic@gmail.com',1,1),
('hours','08.30–18.00 น.',1,1),
('line','@babyproducts.th',0,0),
('address','ร้าน Baby Products อำเภอเมืองเลย จังหวัดเลย 42000',0,0);
