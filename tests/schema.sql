-- Table: users
CREATE TABLE users (
    user_id CHAR(36) NOT NULL PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255),
    phone_number VARCHAR(50),
    street_address VARCHAR(255) DEFAULT NULL,
    street_address2 VARCHAR(255) DEFAULT NULL,
    suburb VARCHAR(255) DEFAULT NULL,
    state VARCHAR(255) DEFAULT NULL,
    postcode VARCHAR(20) DEFAULT NULL,
    country CHAR(2) DEFAULT NULL,
    user_type ENUM('customer','admin') NOT NULL,
    password_reset_token VARCHAR(255) DEFAULT NULL,
    token_expiration DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_type (user_type)
) ENGINE=InnoDB;

-- Table: artworks
CREATE TABLE artworks (
    artwork_id CHAR(36) NOT NULL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    availability_status ENUM('available','sold') NOT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: orders
CREATE TABLE orders (
    order_id CHAR(9) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    order_status ENUM('pending','confirmed','completed','cancelled') NOT NULL,
    order_date DATETIME NOT NULL,
    billing_first_name VARCHAR(255) NOT NULL,
    billing_last_name VARCHAR(255) NOT NULL,
    billing_company VARCHAR(255) DEFAULT '',
    billing_email VARCHAR(255) NOT NULL,
    shipping_country CHAR(2) NOT NULL,
    shipping_address1 VARCHAR(255) NOT NULL,
    shipping_address2 VARCHAR(255) DEFAULT '',
    shipping_suburb VARCHAR(255) NOT NULL,
    shipping_state VARCHAR(50) NOT NULL,
    shipping_postcode VARCHAR(20) NOT NULL,
    shipping_phone VARCHAR(50) NOT NULL,
    order_notes TEXT,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status_date (order_status, order_date),
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Table: artwork_orders
CREATE TABLE artwork_orders (
    artwork_order_id CHAR(36) NOT NULL PRIMARY KEY,
    order_id CHAR(9) NOT NULL,
    artwork_id CHAR(36) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    INDEX idx_artwork_orders_order (order_id),
    INDEX idx_artwork_orders_artwork (artwork_id),
    CONSTRAINT fk_artwork_orders_order FOREIGN KEY (order_id) REFERENCES orders(order_id),
    CONSTRAINT fk_artwork_orders_artwork FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id)
) ENGINE=InnoDB;

-- Table: appointments
CREATE TABLE appointments (
    appointment_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    appointment_type ENUM('initial consultation','follow-up') NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    duration INT NOT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL,
    google_calendar_event_id VARCHAR(255),
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_appointments_user (user_id),
    INDEX idx_appointments_event (google_calendar_event_id),
    CONSTRAINT fk_appointments_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Table: payments
CREATE TABLE payments (
    payment_id CHAR(36) NOT NULL PRIMARY KEY,
    order_id CHAR(9) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255) DEFAULT NULL UNIQUE,
    payment_date DATETIME NOT NULL,
    payment_method ENUM('stripe') NOT NULL DEFAULT 'stripe',
    status ENUM('pending','confirmed') NOT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE INDEX idx_payments_order (order_id),
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
) ENGINE=InnoDB;

-- Table: carts
CREATE TABLE carts (
    cart_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NULL,
    session_id VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_carts_user (user_id),
    UNIQUE INDEX idx_carts_session (session_id),
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: artwork_carts
CREATE TABLE artwork_carts (
    artwork_cart_id CHAR(36) NOT NULL PRIMARY KEY,
    cart_id CHAR(36) NOT NULL,
    artwork_id CHAR(36) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    INDEX idx_artwork_carts_cart (cart_id),
    INDEX idx_artwork_carts_artwork (artwork_id),
    CONSTRAINT fk_artwork_carts_cart FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
    CONSTRAINT fk_artwork_carts_artwork FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: writing_service_requests
CREATE TABLE writing_service_requests (
    writing_service_request_id CHAR(9) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    service_title VARCHAR(100) NOT NULL,
    service_type ENUM('creative_writing','editing','proofreading') NOT NULL,
    notes VARCHAR(1000) DEFAULT NULL,
    final_price DECIMAL(10,2) DEFAULT NULL,
    request_status ENUM('pending','in_progress','completed','canceled') NOT NULL DEFAULT 'pending',
    document VARCHAR(255) DEFAULT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_wsr_user (user_id),
    INDEX idx_wsr_status (request_status),
    CONSTRAINT fk_wsr_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Table: request_messages
CREATE TABLE request_messages (
    request_message_id CHAR(36) NOT NULL PRIMARY KEY,
    writing_service_request_id CHAR(9) NOT NULL,
    user_id CHAR(36) NOT NULL,
    message TEXT NOT NULL,
    is_deleted BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rm_request (writing_service_request_id),
    INDEX idx_rm_user (user_id),
    CONSTRAINT fk_request_messages_request FOREIGN KEY (writing_service_request_id) REFERENCES writing_service_requests(writing_service_request_id),
    CONSTRAINT fk_request_messages_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Table: content_blocks
CREATE TABLE content_blocks (
    content_block_id CHAR(36) NOT NULL PRIMARY KEY,
    parent VARCHAR(100) DEFAULT NULL,
    slug VARCHAR(255) NOT NULL,
    label VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('html','text','image','url') NOT NULL,
    value LONGTEXT,
    previous_value LONGTEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_content_blocks_parent (parent),
    UNIQUE INDEX idx_content_blocks_slug (slug)
) ENGINE=InnoDB;
