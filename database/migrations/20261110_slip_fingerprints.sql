CREATE TABLE IF NOT EXISTS slip_fingerprints (
 content_hash CHAR(64) PRIMARY KEY,slip_id BIGINT UNSIGNED NULL,
 FOREIGN KEY(slip_id) REFERENCES order_slips(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
