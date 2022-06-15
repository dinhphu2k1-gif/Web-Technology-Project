CREATE TABLE users
(
    id        BIGINT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    telephone VARCHAR(10)  NOT NULL,
    email     VARCHAR(50)  NOT NULL,
    address   VARCHAR(200) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE admins
(
    id       BIGINT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE = InnoDB;

INSERT INTO admins
VALUES (NULL, "admin", "$2y$10$OwbtJ1bonfTJrnu7B.CQLeg1dzQFHaUQLzcCxuy6/jqxGz2me3W92");

CREATE TABLE products
(
    id          BIGINT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    image       LONGBLOB     NOT NULL,
    description TEXT         NOT NULL,
    price       BIGINT       NOT NULL,
    year        INT          NOT NULL
) ENGINE = InnoDB;

CREATE TABLE carts
(
    id      BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE cart_details
(
    id          BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cart_id     BIGINT NOT NULL,
    product_id  BIGINT NOT NULL,
    quantity    INT    NOT NULL,
    total_price BIGINT NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE bills
(
    id          BIGINT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT       NOT NULL,
    name        VARCHAR(50)  NOT NULL,
    telephone   INT(11)      NOT NULL,
    address      VARCHAR(100) NOT NULL,
    time_create DATETIME     NOT NULL      DEFAULT NOW(),
    status      ENUM ("accept", "pending") DEFAULT "pending",
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

CREATE TABLE bill_details
(
    id          BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bill_id    BIGINT NOT NULL,
    product_id  BIGINT NOT NULL,
    quantity    INT    NOT NULL,
    total_price BIGINT NOT NULL,
    FOREIGN KEY (bill_id) REFERENCES bills (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;
