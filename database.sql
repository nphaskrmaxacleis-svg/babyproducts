CREATE DATABASE IF NOT EXISTS babyproducts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE babyproducts;
CREATE TABLE users (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('customer','admin') NOT NULL DEFAULT 'customer',active BOOLEAN NOT NULL DEFAULT 1,phone VARCHAR(30) NOT NULL DEFAULT '',address TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE categories (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL UNIQUE,description TEXT,active BOOLEAN DEFAULT 1) ENGINE=InnoDB;
CREATE TABLE products (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,category_id INT UNSIGNED NOT NULL,sku VARCHAR(40) NOT NULL UNIQUE,name VARCHAR(190) NOT NULL,description TEXT NOT NULL,price DECIMAL(12,2) NOT NULL CHECK(price>=0),stock INT NOT NULL CHECK(stock>=0),image VARCHAR(255) NOT NULL,featured BOOLEAN DEFAULT 0,active BOOLEAN DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(category_id) REFERENCES categories(id),INDEX(category_id,active),INDEX(name)) ENGINE=InnoDB;
CREATE TABLE orders (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NOT NULL,token CHAR(64) NOT NULL UNIQUE,recipient VARCHAR(120) NOT NULL,phone VARCHAR(30) NOT NULL,address TEXT NOT NULL,province VARCHAR(120) NOT NULL,district VARCHAR(120) NOT NULL,subdistrict VARCHAR(120) NOT NULL,postcode CHAR(5) NOT NULL,note TEXT,subtotal DECIMAL(12,2) NOT NULL,shipping DECIMAL(12,2) NOT NULL,total DECIMAL(12,2) NOT NULL,status ENUM('pending','confirmed','preparing','shipped','delivered','cancelled') DEFAULT 'pending',payment_method VARCHAR(20) DEFAULT 'COD',payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id),INDEX(status,created_at)) ENGINE=InnoDB;
CREATE TABLE order_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id INT UNSIGNED NOT NULL,product_id INT UNSIGNED NOT NULL,name VARCHAR(190) NOT NULL,price DECIMAL(12,2) NOT NULL,quantity INT NOT NULL CHECK(quantity>0),FOREIGN KEY(order_id) REFERENCES orders(id),FOREIGN KEY(product_id) REFERENCES products(id)) ENGINE=InnoDB;
CREATE TABLE pdpa_consents (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NOT NULL,purpose VARCHAR(80) NOT NULL DEFAULT 'marketing',granted BOOLEAN NOT NULL,policy_version VARCHAR(30) NOT NULL DEFAULT '2026-11-03',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB;
CREATE TABLE privacy_requests (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,user_id INT UNSIGNED NOT NULL,type ENUM('access','delete') NOT NULL,details TEXT,status ENUM('pending','completed') DEFAULT 'pending',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB;
CREATE TABLE login_attempts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,identity CHAR(64) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(identity,created_at)) ENGINE=InnoDB;
INSERT INTO categories(name,description) VALUES ('เสื้อผ้าเด็ก','ผ้านุ่มสำหรับทุกวัน'),('นมและอาหารเด็ก','ข้อมูลตัวอย่าง ไม่มีคำกล่าวอ้างทางการแพทย์'),('ของเล่นเด็ก','เติมสีสันให้การเรียนรู้'),('รถเข็นเด็ก','ออกเดินทางด้วยกัน'),('ผ้าอ้อม','ดูแลความสะอาด'),('ผลิตภัณฑ์ดูแลเด็ก','ของใช้สำหรับลูกน้อย');
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (1,'BP1','บอดี้สูทผ้าฝ้าย','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',129,20,'uploads/products/bp1-studio-ai-premium-v2.webp',1);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (1,'BP2','ชุดนอนลายหมี','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',199,21,'uploads/products/bp2-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (1,'BP3','หมวกเด็กสีครีม','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',269,22,'uploads/products/bp3-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (2,'BP4','อาหารเด็กตัวอย่าง A','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',339,23,'uploads/products/bp4-studio-ai-v1.webp',1);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (2,'BP5','อาหารเด็กตัวอย่าง B','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',409,24,'uploads/products/bp5-studio-ai-v1.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (2,'BP6','อาหารเด็กตัวอย่าง C','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',479,25,'uploads/products/bp6-studio-ai-v1.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (3,'BP7','ตุ๊กตาหมีนุ่ม','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',549,26,'uploads/products/bp7-studio-ai-premium-v2.webp',1);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (3,'BP8','บล็อกไม้สีพาสเทล','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',619,27,'uploads/products/bp8-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (3,'BP9','ของเล่นเขย่ามือ','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',689,28,'uploads/products/bp9-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (4,'BP10','รถเข็นพับได้','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',759,29,'uploads/products/bp10-studio-ai-premium-v2.webp',1);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (4,'BP11','รถเข็นรุ่นเดินทาง','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',829,30,'uploads/products/bp11-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (4,'BP12','รถเข็นสีครีม','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',899,31,'uploads/products/bp12-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (5,'BP13','ผ้าอ้อมกลางวัน','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',969,32,'uploads/products/bp13-studio-ai-v1.webp',1);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (5,'BP14','ผ้าอ้อมกลางคืน','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',1039,33,'uploads/products/bp14-studio-ai-v1.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (5,'BP15','ผ้าอ้อมแบบผ้า','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',1109,34,'uploads/products/bp15-studio-ai-v1.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (6,'BP16','แปรงอาบน้ำเด็ก','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',1179,35,'uploads/products/bp16-studio-ai-premium-v2.webp',1);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (6,'BP17','ผ้าขนหนูเด็ก','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',1249,36,'uploads/products/bp17-studio-ai-premium-v2.webp',0);
INSERT INTO products(category_id,sku,name,description,price,stock,image,featured) VALUES (6,'BP18','ชุดหวีแปรงเด็ก','สินค้าตัวอย่างเพื่อการศึกษา ไม่ใช่รายการสินค้าที่มีจำหน่ายจริง กรุณาศึกษาข้อมูลและช่วงวัยที่เหมาะสมก่อนเลือกใช้',1319,37,'uploads/products/bp18-studio-ai-premium-v2.webp',0);


-- Store contact system (additive migration)
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


-- Order and payment extension
ALTER TABLE orders
 MODIFY payment_status ENUM('unpaid','pending_review','paid','rejected','refunded') NOT NULL DEFAULT 'unpaid',
 ADD COLUMN IF NOT EXISTS bank_name VARCHAR(120) NULL,
 ADD COLUMN IF NOT EXISTS bank_account_name VARCHAR(160) NULL,
 ADD COLUMN IF NOT EXISTS bank_account_number VARCHAR(40) NULL,
 ADD COLUMN IF NOT EXISTS carrier VARCHAR(100) NULL,
 ADD COLUMN IF NOT EXISTS tracking_number VARCHAR(100) NULL;
CREATE TABLE IF NOT EXISTS payment_settings (
 id TINYINT UNSIGNED PRIMARY KEY,
 bank_name VARCHAR(120) NOT NULL DEFAULT '',
 account_name VARCHAR(160) NOT NULL DEFAULT '',
 account_number VARCHAR(40) NOT NULL DEFAULT '',
 enabled BOOLEAN NOT NULL DEFAULT 0,
 updated_by INT UNSIGNED NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO payment_settings(id) VALUES(1);
CREATE TABLE IF NOT EXISTS order_slips (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL,
 stored_name VARCHAR(80) NOT NULL UNIQUE,
 status ENUM('pending_review','approved','rejected') NOT NULL DEFAULT 'pending_review',
 rejection_reason VARCHAR(1000) NULL,
 reviewed_by INT UNSIGNED NULL,
 reviewed_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
 INDEX(order_id,id), INDEX(status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS order_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL,
 actor_id INT UNSIGNED NULL,
 event_type VARCHAR(40) NOT NULL,
 order_status VARCHAR(30) NOT NULL,
 payment_status VARCHAR(30) NOT NULL,
 note VARCHAR(1000) NOT NULL DEFAULT '',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(order_id) REFERENCES orders(id),
 FOREIGN KEY(actor_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX(order_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 order_id INT UNSIGNED NOT NULL,
 message VARCHAR(500) NOT NULL,
 read_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id),
 FOREIGN KEY(order_id) REFERENCES orders(id),
 INDEX(user_id,read_at,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Saved shipping addresses
CREATE TABLE IF NOT EXISTS user_addresses (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 label VARCHAR(60) NOT NULL DEFAULT '',
 recipient VARCHAR(120) NOT NULL,
 phone VARCHAR(30) NOT NULL,
 address_line VARCHAR(2000) NOT NULL,
 subdistrict VARCHAR(120) NOT NULL,
 district VARCHAR(120) NOT NULL,
 province VARCHAR(120) NOT NULL,
 postcode CHAR(5) NOT NULL,
 details VARCHAR(1000) NOT NULL DEFAULT '',
 map_url VARCHAR(2000) NOT NULL DEFAULT '',
 is_default BOOLEAN NOT NULL DEFAULT 0,
 default_user_id INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN is_default=1 THEN user_id ELSE NULL END) STORED,
 fingerprint CHAR(64) NOT NULL,
 saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id),
 UNIQUE KEY one_default_per_user(default_user_id),
 UNIQUE KEY unique_address(user_id,fingerprint),
 INDEX(user_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS map_url VARCHAR(2000) NULL,
 ADD COLUMN IF NOT EXISTS address_note VARCHAR(1000) NULL;


-- Shopping checkout, promotions and gateway extension
ALTER TABLE payment_settings
 ADD COLUMN IF NOT EXISTS cod_enabled BOOLEAN NOT NULL DEFAULT 1,
 ADD COLUMN IF NOT EXISTS cod_min DECIMAL(12,2) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS cod_max DECIMAL(12,2) NOT NULL DEFAULT 1000000,
 ADD COLUMN IF NOT EXISTS cod_provinces TEXT NULL,
 ADD COLUMN IF NOT EXISTS promptpay_enabled BOOLEAN NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS promptpay_type ENUM('phone','national_id') NOT NULL DEFAULT 'phone',
 ADD COLUMN IF NOT EXISTS promptpay_recipient VARCHAR(20) NOT NULL DEFAULT '',
 ADD COLUMN IF NOT EXISTS promptpay_name VARCHAR(160) NOT NULL DEFAULT '';
CREATE TABLE IF NOT EXISTS shipping_services (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 fee DECIMAL(12,2) NOT NULL DEFAULT 50,
 free_min DECIMAL(12,2) NULL,
 provinces TEXT NULL,
 cod_allowed BOOLEAN NOT NULL DEFAULT 1,
 active BOOLEAN NOT NULL DEFAULT 0,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO shipping_services(id,name,fee,free_min,active) VALUES(1,'จัดส่งมาตรฐานโดยร้านค้า',50,1000,1);
CREATE TABLE IF NOT EXISTS promotions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(160) NOT NULL,
 code VARCHAR(40) NULL UNIQUE,
 kind ENUM('fixed','percent','shipping','unit_price','flash_price','bundle_price') NOT NULL,
 value INT UNSIGNED NOT NULL DEFAULT 0,
 min_subtotal INT UNSIGNED NOT NULL DEFAULT 0,
 max_discount INT UNSIGNED NOT NULL DEFAULT 0,
 min_quantity INT UNSIGNED NOT NULL DEFAULT 1,
 audience ENUM('all','new','returning') NOT NULL DEFAULT 'all',
 starts_at DATETIME NOT NULL,
 ends_at DATETIME NOT NULL,
 total_limit INT UNSIGNED NULL,
 per_user_limit INT UNSIGNED NULL,
 stackable BOOLEAN NOT NULL DEFAULT 0,
 active BOOLEAN NOT NULL DEFAULT 0,
 updated_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(updated_by) REFERENCES users(id) ON DELETE SET NULL,
 INDEX(active,starts_at,ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS promotion_products (
 promotion_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(promotion_id,product_id),
 FOREIGN KEY(promotion_id) REFERENCES promotions(id), FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS promotion_categories (
 promotion_id INT UNSIGNED NOT NULL, category_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(promotion_id,category_id),
 FOREIGN KEY(promotion_id) REFERENCES promotions(id), FOREIGN KEY(category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS promotion_redemptions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 promotion_id INT UNSIGNED NOT NULL, order_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL,
 discount_amount INT UNSIGNED NOT NULL,
 released_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY(promotion_id,order_id), INDEX(promotion_id,user_id,released_at),
 FOREIGN KEY(promotion_id) REFERENCES promotions(id), FOREIGN KEY(order_id) REFERENCES orders(id), FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE orders
 ADD COLUMN IF NOT EXISTS product_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS coupon_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS shipping_base DECIMAL(12,2) NULL,
 ADD COLUMN IF NOT EXISTS shipping_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS shipping_service_id INT UNSIGNED NULL,
 ADD COLUMN IF NOT EXISTS shipping_service_name VARCHAR(120) NULL,
 ADD COLUMN IF NOT EXISTS price_breakdown LONGTEXT NULL,
 ADD COLUMN IF NOT EXISTS promptpay_type VARCHAR(20) NULL,
 ADD COLUMN IF NOT EXISTS promptpay_recipient VARCHAR(20) NULL,
 ADD COLUMN IF NOT EXISTS promptpay_name VARCHAR(160) NULL;
ALTER TABLE order_items
 ADD COLUMN IF NOT EXISTS line_subtotal DECIMAL(12,2) NULL,
 ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(12,2) NULL,
 ADD COLUMN IF NOT EXISTS line_total DECIMAL(12,2) NULL;
CREATE TABLE IF NOT EXISTS gateway_sessions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 order_id INT UNSIGNED NOT NULL, provider VARCHAR(50) NOT NULL,
 external_id VARCHAR(190) NOT NULL, amount_cents INT UNSIGNED NOT NULL,
 currency CHAR(3) NOT NULL DEFAULT 'THB',
 status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY(provider,external_id), FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS gateway_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 provider VARCHAR(50) NOT NULL, event_id VARCHAR(190) NOT NULL,
 order_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY(provider,event_id), FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Complete commerce extension
ALTER TABLE products ADD COLUMN IF NOT EXISTS name_en VARCHAR(190) NULL,
 ADD COLUMN IF NOT EXISTS description_en TEXT NULL,
 ADD COLUMN IF NOT EXISTS tax_exempt BOOLEAN NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS variants_enabled BOOLEAN NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS name_en VARCHAR(120) NULL, ADD COLUMN IF NOT EXISTS description_en TEXT NULL;
CREATE TABLE IF NOT EXISTS product_variants (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,product_id INT UNSIGNED NOT NULL,
 color VARCHAR(80) NOT NULL DEFAULT '',size VARCHAR(80) NOT NULL DEFAULT '',sku VARCHAR(80) NOT NULL UNIQUE,
 stock INT UNSIGNED NOT NULL DEFAULT 0,active BOOLEAN NOT NULL DEFAULT 1,
 FOREIGN KEY(product_id) REFERENCES products(id),UNIQUE KEY(product_id,color,size)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS inventory_movements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,product_id INT UNSIGNED NOT NULL,variant_id INT UNSIGNED NULL,
 order_id INT UNSIGNED NULL,actor_id INT UNSIGNED NULL,kind VARCHAR(30) NOT NULL,
 quantity INT NOT NULL,balance INT UNSIGNED NOT NULL,product_balance INT UNSIGNED NOT NULL,
 note VARCHAR(500) NOT NULL DEFAULT '',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(product_id) REFERENCES products(id),FOREIGN KEY(variant_id) REFERENCES product_variants(id),
 FOREIGN KEY(order_id) REFERENCES orders(id),FOREIGN KEY(actor_id) REFERENCES users(id),INDEX(product_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE order_items ADD COLUMN IF NOT EXISTS variant_id INT UNSIGNED NULL,
 ADD COLUMN IF NOT EXISTS variant_color VARCHAR(80) NULL,ADD COLUMN IF NOT EXISTS variant_size VARCHAR(80) NULL,
 ADD COLUMN IF NOT EXISTS name_en VARCHAR(190) NULL;
ALTER TABLE user_addresses ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL,ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL,ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL,
 ADD COLUMN IF NOT EXISTS shipment_status ENUM('not_shipped','shipped','delivered','cancelled') NOT NULL DEFAULT 'not_shipped',
 ADD COLUMN IF NOT EXISTS shipped_at DATETIME NULL,ADD COLUMN IF NOT EXISTS delivered_at DATETIME NULL,
 ADD COLUMN IF NOT EXISTS package_weight DECIMAL(10,3) NULL,ADD COLUMN IF NOT EXISTS package_dimensions VARCHAR(120) NULL;
UPDATE orders SET shipment_status=CASE status WHEN 'shipped' THEN 'shipped' WHEN 'delivered' THEN 'delivered' WHEN 'cancelled' THEN 'cancelled' ELSE 'not_shipped' END WHERE shipment_status='not_shipped' AND status IN ('shipped','delivered','cancelled');
ALTER TABLE order_slips ADD COLUMN IF NOT EXISTS content_hash CHAR(64) NULL,ADD COLUMN IF NOT EXISTS duplicate_warning VARCHAR(500) NULL,
 ADD INDEX IF NOT EXISTS idx_slip_hash(content_hash);
CREATE TABLE IF NOT EXISTS tax_settings (
 id TINYINT UNSIGNED PRIMARY KEY,registered BOOLEAN NOT NULL DEFAULT 0,tax_id VARCHAR(13) NOT NULL DEFAULT '',
 legal_name VARCHAR(190) NOT NULL DEFAULT '',legal_address TEXT NULL,branch VARCHAR(80) NOT NULL DEFAULT 'สำนักงานใหญ่',
 price_mode ENUM('inclusive','exclusive') NOT NULL DEFAULT 'inclusive',shipping_taxable BOOLEAN NOT NULL DEFAULT 0,
 invoice_enabled BOOLEAN NOT NULL DEFAULT 0,confirmed_by INT UNSIGNED NULL,confirmed_at DATETIME NULL,
 FOREIGN KEY(confirmed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO tax_settings(id) VALUES(1);
CREATE TABLE IF NOT EXISTS tax_rates (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,rate_bps INT UNSIGNED NOT NULL,
 starts_on DATE NOT NULL,ends_on DATE NOT NULL,active BOOLEAN NOT NULL DEFAULT 0,
 updated_by INT UNSIGNED NULL,FOREIGN KEY(updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS net_before_tax DECIMAL(12,2) NULL,
 ADD COLUMN IF NOT EXISTS vat_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS tax_snapshot LONGTEXT NULL,
 ADD COLUMN IF NOT EXISTS cod_fee DECIMAL(12,2) NOT NULL DEFAULT 0;
CREATE TABLE IF NOT EXISTS order_documents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id INT UNSIGNED NOT NULL,
 kind ENUM('receipt','tax_invoice') NOT NULL,number VARCHAR(60) NOT NULL UNIQUE,snapshot LONGTEXT NOT NULL,
 issued_by INT UNSIGNED NOT NULL,issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY(order_id,kind),FOREIGN KEY(order_id) REFERENCES orders(id),FOREIGN KEY(issued_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE shipping_services ADD COLUMN IF NOT EXISTS cod_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS eta_days VARCHAR(80) NULL,ADD COLUMN IF NOT EXISTS max_weight DECIMAL(10,3) NULL;
CREATE TABLE IF NOT EXISTS shipping_agreements (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,company VARCHAR(160) NOT NULL,contact_name VARCHAR(160) NOT NULL DEFAULT '',
 contact_channel VARCHAR(300) NOT NULL DEFAULT '',proposed_rate TEXT NULL,cod_terms TEXT NULL,cod_remittance TEXT NULL,
 pickup_terms TEXT NULL,insurance_terms TEXT NULL,return_terms TEXT NULL,starts_on DATE NULL,ends_on DATE NULL,
 status ENUM('research','negotiating','proposed','signed','expired','declined') NOT NULL DEFAULT 'research',
 signed_confirm BOOLEAN NOT NULL DEFAULT 0,updated_by INT UNSIGNED NOT NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS map_search_cache (
 query_hash CHAR(64) PRIMARY KEY,response MEDIUMTEXT NOT NULL,expires_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS slip_fingerprints (
 content_hash CHAR(64) PRIMARY KEY,slip_id BIGINT UNSIGNED NULL,
 FOREIGN KEY(slip_id) REFERENCES order_slips(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
