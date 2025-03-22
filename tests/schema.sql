-- Table: users
CREATE TABLE users (
    user_id CHAR(36) NOT NULL PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255),
    phone_number VARCHAR(50),
    address TEXT,
    user_type ENUM('customer','admin') NOT NULL,
    password_reset_token VARCHAR(255) DEFAULT NULL,
    token_expiration DATETIME DEFAULT NULL,
    last_login DATETIME NULL,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: user_oauths
CREATE TABLE user_oauths (
    oauth_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at DATETIME,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_oauths_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY uq_provider_user (provider, provider_user_id)
) ENGINE=InnoDB;

-- Table: artworks
CREATE TABLE artworks (
    artwork_id CHAR(36) NOT NULL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    availability_status ENUM('available','sold') NOT NULL,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: orders
CREATE TABLE orders (
    order_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('bank transfer','credit card') NOT NULL,
    order_status ENUM('pending','confirmed','completed','cancelled') NOT NULL,
    order_date DATETIME NOT NULL,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Table: artwork_orders
CREATE TABLE artwork_orders (
    artwork_order_id CHAR(36) NOT NULL PRIMARY KEY,
    order_id CHAR(36) NOT NULL,
    artwork_id CHAR(36) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_artwork_orders_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_artwork_orders_artwork FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id)
) ENGINE=InnoDB;

-- Table: services
CREATE TABLE services (
    service_id CHAR(36) NOT NULL PRIMARY KEY,
    service_name VARCHAR(255) NOT NULL,
    description TEXT,
    pricing_type ENUM('hourly','per-word') NOT NULL,
    base_rate DECIMAL(10,2),
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: contact_messages
CREATE TABLE contact_messages (
    message_id CHAR(36) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table: payments
CREATE TABLE payments (
    payment_id CHAR(36) NOT NULL PRIMARY KEY,
    order_id CHAR(36) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME NOT NULL,
    payment_method ENUM('bank transfer','credit card') NOT NULL,
    status ENUM('pending','confirmed') NOT NULL,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: carts
CREATE TABLE carts (
    cart_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NULL,          -- Foreign key if the user is logged in
    session_id VARCHAR(255) NULL,   -- For guest users
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_carts_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: artwork_carts
CREATE TABLE artwork_carts (
    artwork_cart_id CHAR(36) NOT NULL PRIMARY KEY,
    cart_id CHAR(36) NOT NULL,
    artwork_id CHAR(36) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT NOT NULL DEFAULT 0,
    CONSTRAINT fk_artwork_carts_cart FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
    CONSTRAINT fk_artwork_carts_artwork FOREIGN KEY (artwork_id) REFERENCES artworks(artwork_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: writing_service_requests
CREATE TABLE writing_service_requests (
    request_id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    service_type ENUM('creative_writing', 'editing', 'proofreading') NOT NULL,
    word_count_range VARCHAR(50) NOT NULL,
    notes VARCHAR(100) DEFAULT NULL,
    estimated_price DECIMAL(10,2) DEFAULT NULL,
    final_price DECIMAL(10,2) DEFAULT NULL,
    request_status enum('pending', 'in_progress', 'expired') NOT NULL DEFAULT 'pending',
    is_deleted TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    document varchar(255) DEFAULT NULL,
    CONSTRAINT fk_writing_service_requests_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
