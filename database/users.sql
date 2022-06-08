CREATE TABLE users
(
    id        BIGINT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(50)  NOT NULL,
    birthday  DATE         NOT NULL,
    telephone VARCHAR(10)  NOT NULL,
    address   VARCHAR(200) NOT NULL
) ENGINE = InnoDB;