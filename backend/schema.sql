-- TableSync Pro - Schema & Seed (POS-ready UI build)
CREATE DATABASE IF NOT EXISTS icei_416760 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE icei_416760;

CREATE TABLE IF NOT EXISTS Roles (
  role_id INT(11) PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL,
  description TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Users (
  user_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  registration_date DATETIME DEFAULT current_timestamp(),
  account_status ENUM('Active','Inactive','Suspended') DEFAULT 'Active',
  role_id INT(11) NOT NULL,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES Roles(role_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Restaurant_Tables (
  table_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  capacity INT(11) NOT NULL,
  status ENUM('Available','Reserved','Occupied') DEFAULT 'Available',
  location VARCHAR(100) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Reservations (
  reservation_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  party_size INT(11) NOT NULL,
  status ENUM('Confirmed','Cancelled','Completed') DEFAULT 'Confirmed',
  special_requests TEXT NULL,
  user_id INT(11) NOT NULL,
  table_id INT(11) NOT NULL,
  CONSTRAINT fk_res_user FOREIGN KEY (user_id) REFERENCES Users(user_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_res_table FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Menu_Items (
  menu_item_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  category VARCHAR(50) NULL,
  price DECIMAL(10,2) NOT NULL,
  is_available TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Extended enum includes Cancelled to support cancelling pending orders
CREATE TABLE IF NOT EXISTS Orders (
  order_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  order_datetime DATETIME DEFAULT current_timestamp(),
  status ENUM('Pending','Preparing','Served','Cancelled') DEFAULT 'Pending',
  total_price DECIMAL(10,2) DEFAULT 0.00,
  table_id INT(11) NOT NULL,
  processed_by INT(11) NULL,
  CONSTRAINT fk_orders_table FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_orders_user FOREIGN KEY (processed_by) REFERENCES Users(user_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Order_Details (
  order_detail_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  quantity INT(11) NOT NULL DEFAULT 1,
  price_charged DECIMAL(10,2) NOT NULL,
  order_id INT(11) NOT NULL,
  menu_item_id INT(11) NOT NULL,
  CONSTRAINT fk_od_order FOREIGN KEY (order_id) REFERENCES Orders(order_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_od_menu FOREIGN KEY (menu_item_id) REFERENCES Menu_Items(menu_item_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Invoices (
  invoice_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  generation_date DATETIME DEFAULT current_timestamp(),
  subtotal DECIMAL(10,2) NOT NULL,
  discount DECIMAL(10,2) DEFAULT 0.00,
  total_amount DECIMAL(10,2) NOT NULL,
  payment_status ENUM('Pending','Paid','Refunded') DEFAULT 'Pending',
  table_id INT(11) NOT NULL,
  generated_by INT(11) NULL,
  CONSTRAINT fk_inv_table FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_inv_user FOREIGN KEY (generated_by) REFERENCES Users(user_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Dining_History (
  history_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  check_in_time DATETIME NOT NULL,
  check_out_time DATETIME NULL,
  duration_minutes INT(11) NULL,
  table_id INT(11) NOT NULL,
  reservation_id INT(11) NULL,
  CONSTRAINT fk_hist_table FOREIGN KEY (table_id) REFERENCES Restaurant_Tables(table_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_hist_res FOREIGN KEY (reservation_id) REFERENCES Reservations(reservation_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Password_Reset_Tokens (
  reset_id INT(11) PRIMARY KEY AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT current_timestamp(),
  CONSTRAINT fk_prt_user FOREIGN KEY (user_id) REFERENCES Users(user_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value TEXT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO Roles (role_id, role_name, description) VALUES
(1,'Customer','Customer can make reservations and view history'),
(2,'Waiter','Waiter can manage tables, orders, invoices'),
(3,'Manager','Manager can manage menu, reports, settings');

-- Demo users password: Password123 (bcrypt hash)
INSERT IGNORE INTO Users (user_id, first_name, last_name, phone, email, password, role_id) VALUES
(1,'Demo','Customer',NULL,'customer@demo.local','$2y$10$QbI8mJtWQjW2g5h8b8m7pOkLqK50WnUuFJgX0vCj8Jny4y4r9Y8zG',1),
(2,'Demo','Waiter',NULL,'waiter@demo.local','$2y$10$QbI8mJtWQjW2g5h8b8m7pOkLqK50WnUuFJgX0vCj8Jny4y4r9Y8zG',2),
(3,'Demo','Manager',NULL,'manager@demo.local','$2y$10$QbI8mJtWQjW2g5h8b8m7pOkLqK50WnUuFJgX0vCj8Jny4y4r9Y8zG',3);

INSERT IGNORE INTO Restaurant_Tables (table_id, capacity, status, location) VALUES
(1,2,'Available','Window'),
(2,4,'Available','Center'),
(3,6,'Available','Terrace'),
(4,4,'Available','Bar');

INSERT IGNORE INTO Menu_Items (menu_item_id, name, description, category, price, is_available) VALUES
(1,'Espresso','Strong coffee shot','Drinks',1.50,1),
(2,'Cappuccino','Espresso with milk foam','Drinks',2.20,1),
(3,'Margherita Pizza','Tomato, mozzarella, basil','Main',7.90,1),
(4,'Caesar Salad','Chicken, lettuce, parmesan','Starter',5.50,1);
