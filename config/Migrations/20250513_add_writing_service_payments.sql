CREATE TABLE writing_service_payments (
    writing_service_payment_id CHAR(36) NOT NULL,
    writing_service_request_id CHAR(9) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) NULL,
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(255) NOT NULL DEFAULT 'stripe',
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    is_deleted BOOLEAN NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (writing_service_payment_id),
    INDEX idx_wsp_request (writing_service_request_id),
    INDEX idx_wsp_transaction (transaction_id),
    CONSTRAINT fk_wsp_request FOREIGN KEY (writing_service_request_id) REFERENCES writing_service_requests (writing_service_request_id) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
EOF < /dev/null