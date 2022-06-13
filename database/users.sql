CREATE TABLE users
(
    id        BIGINT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(255)  NOT NULL,
    birthday  DATE         NOT NULL,
    telephone VARCHAR(10)  NOT NULL,
    email     VARCHAR(50)  NOT NULL,
    address   VARCHAR(200) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE admins
(
    id       BIGINT      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE = InnoDB;
