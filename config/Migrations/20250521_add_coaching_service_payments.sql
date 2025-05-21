CREATE TABLE coaching_service_payments (
    coaching_service_payment_id CHAR(36) NOT NULL,
    coaching_service_request_id CHAR(9) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(255) NOT NULL DEFAULT 'stripe',
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    is_deleted BOOLEAN NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (coaching_service_payment_id),
    INDEX idx_csp_request (coaching_service_request_id),
    INDEX idx_csp_transaction (transaction_id),
    CONSTRAINT fk_csp_request FOREIGN KEY (coaching_service_request_id) REFERENCES coaching_service_requests (coaching_service_request_id) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
EOF < /dev/null 