CREATE USER 'adminTinder'@'localhost' IDENTIFIED BY 'admin123';
GRANT ALL PRIVILEGES ON *.* TO 'adminTinder'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

DROP DATABASE IF EXISTS IETinder;
CREATE DATABASE IETinder;

USE IETinder;

CREATE TABLE users (
    user_ID INT AUTO_INCREMENT PRIMARY KEY,         
    email VARCHAR(255) NOT NULL UNIQUE,              
    password VARCHAR(255) NOT NULL,                 
    name VARCHAR(100) NOT NULL,                     
    surname VARCHAR(100) NOT NULL,                   
    alias VARCHAR(100) NOT NULL,                             
    birth_date DATE NOT NULL,                                 
    latitude FLOAT NOT NULL,                                
    longitude FLOAT NOT NULL,                                
    sex ENUM('home', 'dona', 'no binari') NOT NULL,  
    sexual_orientation ENUM('heterosexual', 'homosexual', 'bisexual') NOT NULL, 
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    last_login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    distance_user_preference INT DEFAULT 200, 
    min_age_user_preference INT DEFAULT 18, 
    max_age_user_preference INT DEFAULT 50
);

-- Creación de la tabla de fotos
CREATE TABLE photos (
    photo_ID INT AUTO_INCREMENT PRIMARY KEY,        
    user_ID INT NOT NULL,                            
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    type ENUM('jpg', 'png', 'jpeg', 'gif') NOT NULL, 
    path VARCHAR(255) NOT NULL,                     
    FOREIGN KEY (user_ID) REFERENCES users(user_ID) ON DELETE CASCADE 
);

CREATE TABLE interactions (
    interaction_ID INT AUTO_INCREMENT PRIMARY KEY,  
    interaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    `from` INT NOT NULL,                             
    `to` INT NOT NULL,                              
    state ENUM('like', 'dislike') NOT NULL, -- Estado de la interacción
    FOREIGN KEY (`from`) REFERENCES users(user_ID) ON DELETE CASCADE, 
    FOREIGN KEY (`to`) REFERENCES users(user_ID) ON DELETE CASCADE   
);

CREATE TABLE matches (
    match_ID INT AUTO_INCREMENT PRIMARY KEY,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    participant1 INT NOT NULL,
    participant2 INT NOT NULL,
    FOREIGN KEY (`participant1`) REFERENCES users(user_ID) ON DELETE CASCADE, 
    FOREIGN KEY (`participant2`) REFERENCES users(user_ID) ON DELETE CASCADE    
);

CREATE TABLE conversations (
    message_ID INT AUTO_INCREMENT PRIMARY KEY,
    match_ID INT,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sender_id INT,  -- Foreign key to associate the sender
    content TEXT NOT NULL,
    FOREIGN KEY (match_id) REFERENCES matches(match_ID) ON DELETE SET NULL,
    FOREIGN KEY (sender_id) REFERENCES users(user_ID) ON DELETE SET NULL  
);

INSERT INTO users (email, password, name, surname, alias, birth_date, latitude, longitude, sex, sexual_orientation, last_login_date, creation_date)
VALUES
('johndoe1@ieti.site', SHA2('password1', 512), 'John', 'Doe', 'johndoe1', '1995-01-01', 40.7128, -74.0060, 'home', 'heterosexual', '2025-01-01 08:15:32', '2025-01-01 08:15:32'),
('janesmith2@ieti.site', SHA2('password2', 512), 'Jane', 'Smith', 'janesmith2', '1994-02-02', 41.9028, 12.4964, 'dona', 'homosexual', '2025-01-02 09:20:14', '2025-01-02 09:20:14'),
('michaelbrown3@ieti.site', SHA2('password3', 512), 'Michael', 'Brown', 'michaelbrown3', '1990-03-03', 34.0522, -118.2437, 'home', 'bisexual', '2025-01-03 10:25:45', '2025-01-03 10:25:45'),
('emilyjones4@ieti.site', SHA2('password4', 512), 'Emily', 'Jones', 'emilyjones4', '1992-04-04', 51.5074, -0.1278, 'dona', 'heterosexual', '2025-01-04 11:30:21', '2025-01-04 11:30:21'),
('davidwhite5@ieti.site', SHA2('password5', 512), 'David', 'White', 'davidwhite5', '1989-05-05', 48.8566, 2.3522, 'home', 'bisexual', '2025-01-05 12:35:11', '2025-01-05 12:35:11'),
('sophiadavis6@ieti.site', SHA2('password6', 512), 'Sophia', 'Davis', 'sophiadavis6', '1993-06-06', 37.7749, -122.4194, 'dona', 'heterosexual', '2025-01-06 13:40:00', '2025-01-06 13:40:00'),
('jamesmiller7@ieti.site', SHA2('password7', 512), 'James', 'Miller', 'jamesmiller7', '1991-07-07', 40.7306, -73.9352, 'home', 'homosexual', '2025-01-07 14:45:22', '2025-01-07 14:45:22'),
('oliviagarcia8@ieti.site', SHA2('password8', 512), 'Olivia', 'Garcia', 'oliviagarcia8', '1990-08-08', 39.9526, -75.1652, 'dona', 'bisexual', '2025-01-08 15:50:35', '2025-01-08 15:50:35'),
('williammartinez9@ieti.site', SHA2('password9', 512), 'William', 'Martinez', 'williammartinez9', '1988-09-09', 52.5200, 13.4050, 'home', 'heterosexual', '2025-01-09 16:55:18', '2025-01-09 16:55:18'),
('avahernandez10@ieti.site', SHA2('password10', 512), 'Ava', 'Hernandez', 'avahernandez10', '1996-10-10', 34.0522, -118.2437, 'dona', 'bisexual', '2025-01-10 17:00:12', '2025-01-10 17:00:12'),
('liamlopez11@ieti.site', SHA2('password11', 512), 'Liam', 'Lopez', 'liamlopez11', '1992-11-11', 40.7306, -73.9352, 'home', 'heterosexual', '2025-01-11 18:05:23', '2025-01-11 18:05:23'),
('isabellagonzalez12@ieti.site', SHA2('password12', 512), 'Isabella', 'Gonzalez', 'isabellagonzalez12', '1989-12-12', 41.9028, 12.4964, 'dona', 'homosexual', '2025-01-12 19:10:34', '2025-01-12 19:10:34'),
('ethanwilson13@ieti.site', SHA2('password13', 512), 'Ethan', 'Wilson', 'ethanwilson13', '1995-01-13', 37.7749, -122.4194, 'home', 'bisexual', '2025-01-13 20:15:45', '2025-01-13 20:15:45'),
('miaanderson14@ieti.site', SHA2('password14', 512), 'Mia', 'Anderson', 'miaanderson14', '1991-02-14', 40.7128, -74.0060, 'dona', 'heterosexual', '2025-01-14 21:20:56', '2025-01-14 21:20:56'),
('noahthomas15@ieti.site', SHA2('password15', 512), 'Noah', 'Thomas', 'noahthomas15', '1987-03-15', 34.0522, -118.2437, 'home', 'bisexual', '2025-01-15 22:25:02', '2025-01-15 22:25:02'),
('aidenjackson16@ieti.site', SHA2('password16', 512), 'Aiden', 'Jackson', 'aidenjackson16', '1994-04-16', 41.9028, 12.4964, 'home', 'heterosexual', '2025-01-16 23:30:13', '2025-01-16 23:30:13'),
('ameliamartinez17@ieti.site', SHA2('password17', 512), 'Amelia', 'Martinez', 'ameliamartinez17', '1993-05-17', 37.7749, -122.4194, 'dona', 'homosexual', '2025-01-17 00:35:24', '2025-01-17 00:35:24'),
('lucasbrown18@ieti.site', SHA2('password18', 512), 'Lucas', 'Brown', 'lucasbrown18', '1996-06-18', 51.5074, -0.1278, 'home', 'bisexual', '2025-01-18 01:40:35', '2025-01-18 01:40:35'),
('charlottegarcia19@ieti.site', SHA2('password19', 512), 'Charlotte', 'Garcia', 'charlottegarcia19', '1995-07-19', 40.7306, -73.9352, 'dona', 'heterosexual', '2025-01-19 02:45:46', '2025-01-19 02:45:46'),
('sebastianlee20@ieti.site', SHA2('password20', 512), 'Sebastian', 'Lee', 'sebastianlee20', '1990-08-20', 39.9526, -75.1652, 'home', 'bisexual', '2025-01-20 03:50:57', '2025-01-20 03:50:57'),
('jackharris21@ieti.site', SHA2('password21', 512), 'Jack', 'Harris', 'jackharris21', '1988-09-21', 40.7128, -74.0060, 'home', 'heterosexual', '2025-01-21 04:55:08', '2025-01-21 04:55:08'),
('harperclark22@ieti.site', SHA2('password22', 512), 'Harper', 'Clark', 'harperclark22', '1997-10-22', 34.0522, -118.2437, 'dona', 'homosexual', '2025-01-22 05:00:19', '2025-01-22 05:00:19'),
('benjaminlewis23@ieti.site', SHA2('password23', 512), 'Benjamin', 'Lewis', 'benjaminlewis23', '1991-11-23', 41.9028, 12.4964, 'home', 'heterosexual', '2025-01-23 06:05:30', '2025-01-23 06:05:30'),
('lilywalker24@ieti.site', SHA2('password24', 512), 'Lily', 'Walker', 'lilywalker24', '1994-12-24', 37.7749, -122.4194, 'dona', 'bisexual', '2025-01-24 07:10:41', '2025-01-24 07:10:41'),
('danielyoung25@ieti.site', SHA2('password25', 512), 'Daniel', 'Young', 'danielyoung25', '1987-01-25', 40.7306, -73.9352, 'home', 'bisexual', '2025-01-25 08:15:52', '2025-01-25 08:15:52'),
('evelynking26@ieti.site', SHA2('password26', 512), 'Evelyn', 'King', 'evelynking26', '1990-02-26', 51.5074, -0.1278, 'dona', 'heterosexual', '2025-01-26 09:20:03', '2025-01-26 09:20:03'),
('jacobscott27@ieti.site', SHA2('password27', 512), 'Jacob', 'Scott', 'jacobscott27', '1992-03-27', 34.0522, -118.2437, 'home', 'homosexual', '2025-01-27 10:25:14', '2025-01-27 10:25:14'),
('chloegreen28@ieti.site', SHA2('password28', 512), 'Chloe', 'Green', 'chloegreen28', '1993-04-28', 40.7306, -73.9352, 'dona', 'bisexual', '2025-01-28 11:30:25', '2025-01-28 11:30:25'),
('masonadams29@ieti.site', SHA2('password29', 512), 'Mason', 'Adams', 'masonadams29', '1991-05-29', 39.9526, -75.1652, 'home', 'heterosexual', '2025-01-29 12:35:36', '2025-01-29 12:35:36'),
('zoebaker30@ieti.site', SHA2('password30', 512), 'Zoe', 'Baker', 'zoebaker30', '1988-06-30', 41.9028, 12.4964, 'dona', 'bisexual', '2025-01-30 13:40:47', '2025-01-30 13:40:47'),
('williamnelson31@ieti.site', SHA2('password31', 512), 'William', 'Nelson', 'williamnelson31', '1990-07-01', 40.7128, -74.0060, 'no binari', 'heterosexual', '2025-01-31 14:45:58', '2025-01-31 14:45:58'),
('amoscarter32@ieti.site', SHA2('password32', 512), 'Amos', 'Carter', 'amoscarter32', '1994-08-02', 34.0522, -118.2437, 'no binari', 'bisexual', '2025-02-01 15:50:09', '2025-02-01 15:50:09'),
('isabellamorris33@ieti.site', SHA2('password33', 512), 'Isabella', 'Morris', 'isabellamorris33', '1991-09-03', 51.5074, -0.1278, 'no binari', 'homosexual', '2025-02-02 16:55:20', '2025-02-02 16:55:20'),
('henrymitchell34@ieti.site', SHA2('password34', 512), 'Henry', 'Mitchell', 'henrymitchell34', '1992-10-04', 37.7749, -122.4194, 'no binari', 'heterosexual', '2025-02-03 17:00:31', '2025-02-03 17:00:31'),
('charlotteperez35@ieti.site', SHA2('password35', 512), 'Charlotte', 'Perez', 'charlotteperez35', '1996-11-05', 40.7306, -73.9352, 'no binari', 'bisexual', '2025-02-04 18:05:42', '2025-02-04 18:05:42'),
('elijahroberts36@ieti.site', SHA2('password36', 512), 'Elijah', 'Roberts', 'elijahroberts36', '1989-12-06', 39.9526, -75.1652, 'no binari', 'heterosexual', '2025-02-05 19:10:53', '2025-02-05 19:10:53'),
('auroraramirez37@ieti.site', SHA2('password37', 512), 'Aurora', 'Ramirez', 'auroraramirez37', '1994-01-07', 41.9028, 12.4964, 'no binari', 'bisexual', '2025-02-06 20:15:04', '2025-02-06 20:15:04'),
('jackharris38@ieti.site', SHA2('password38', 512), 'Jack', 'Harris', 'jackharris38', '1992-02-08', 40.7128, -74.0060, 'no binari', 'heterosexual', '2025-02-07 21:20:15', '2025-02-07 21:20:15'),
('liammorris39@ieti.site', SHA2('password39', 512), 'Liam', 'Morris', 'liammorris39', '1993-03-09', 34.0522, -118.2437, 'no binari', 'bisexual', '2025-02-08 22:25:26', '2025-02-08 22:25:26'),
('ellakim40@ieti.site', SHA2('password40', 512), 'Ella', 'Kim', 'ellakim40', '1995-04-10', 51.5074, -0.1278, 'no binari', 'heterosexual', '2025-02-09 23:30:37', '2025-02-09 23:30:37'),
('sophiahughes41@ieti.site', SHA2('password41', 512), 'Sophia', 'Hughes', 'sophiahughes41', '1988-05-11', 37.7749, -122.4194, 'no binari', 'bisexual', '2025-02-10 00:35:48', '2025-02-10 00:35:48'),
('oliverbennett42@ieti.site', SHA2('password42', 512), 'Oliver', 'Bennett', 'oliverbennett42', '1996-06-12', 40.7306, -73.9352, 'no binari', 'heterosexual', '2025-02-11 01:40:59', '2025-02-11 01:40:59'),
('ameliabaker43@ieti.site', SHA2('password43', 512), 'Amelia', 'Baker', 'ameliabaker43', '1990-07-13', 39.9526, -75.1652, 'no binari', 'bisexual', '2025-02-12 02:45:10', '2025-02-12 02:45:10'),
('logancook44@ieti.site', SHA2('password44', 512), 'Logan', 'Cook', 'logancook44', '1992-08-14', 41.9028, 12.4964, 'no binari', 'heterosexual', '2025-02-13 03:50:21', '2025-02-13 03:50:21'),
('emilywalker45@ieti.site', SHA2('password45', 512), 'Emily', 'Walker', 'emilywalker45', '1995-09-15', 40.7128, -74.0060, 'no binari', 'bisexual', '2025-02-14 04:55:32', '2025-02-14 04:55:32'),
('samuelwilliams46@ieti.site', SHA2('password46', 512), 'Samuel', 'Williams', 'samuelwilliams46', '1993-10-16', 34.0522, -118.2437, 'no binari', 'heterosexual', '2025-02-15 05:00:43', '2025-02-15 05:00:43');

INSERT INTO photos (user_ID, type, path)
VALUES
(1, 'jpg', '/images/user1_photo1.jpg'),
(1, 'jpg', '/images/user1_photo2.jpg'),
(2, 'jpg', '/images/user2_photo1.jpg'),
(2, 'jpg', '/images/user2_photo2.jpg'),
(3, 'jpg', '/images/user3_photo1.jpg'),
(3, 'jpg', '/images/user3_photo2.jpg'),
(4, 'jpg', '/images/user4_photo1.jpg'),
(4, 'jpg', '/images/user4_photo2.jpg'),
(5, 'jpg', '/images/user5_photo1.jpg'),
(5, 'jpg', '/images/user5_photo2.jpg'),
(6, 'jpg', '/images/user6_photo1.jpg'),
(6, 'jpg', '/images/user6_photo2.jpg'),
(7, 'jpg', '/images/user7_photo1.jpg'),
(7, 'jpg', '/images/user7_photo2.jpg'),
(8, 'jpg', '/images/user8_photo1.jpg'),
(8, 'jpg', '/images/user8_photo2.jpg'),
(9, 'jpg', '/images/user9_photo1.jpg'),
(9, 'jpg', '/images/user9_photo2.jpg'),
(10, 'jpg', '/images/user10_photo1.jpg'),
(10, 'jpg', '/images/user10_photo2.jpg'),
(11, 'jpg', '/images/user11_photo1.jpg'),
(11, 'jpg', '/images/user11_photo2.jpg'),
(12, 'jpg', '/images/user12_photo1.jpg'),
(12, 'jpg', '/images/user12_photo2.jpg'),
(13, 'jpg', '/images/user13_photo1.jpg'),
(13, 'jpg', '/images/user13_photo2.jpg'),
(14, 'jpg', '/images/user14_photo1.jpg'),
(14, 'jpg', '/images/user14_photo2.jpg'),
(15, 'jpg', '/images/user15_photo1.jpg'),
(15, 'jpg', '/images/user15_photo2.jpg'),
(16, 'jpg', '/images/user16_photo1.jpg'),
(16, 'jpg', '/images/user16_photo2.jpg'),
(17, 'jpg', '/images/user17_photo1.jpg'),
(17, 'jpg', '/images/user17_photo2.jpg'),
(18, 'jpg', '/images/user18_photo1.jpg'),
(18, 'jpg', '/images/user18_photo2.jpg'),
(19, 'jpg', '/images/user19_photo1.jpg'),
(19, 'jpg', '/images/user19_photo2.jpg'),
(20, 'jpg', '/images/user20_photo1.jpg'),
(20, 'jpg', '/images/user20_photo2.jpg'),
(21, 'jpg', '/images/user21_photo1.jpg'),
(21, 'jpg', '/images/user21_photo2.jpg'),
(22, 'jpg', '/images/user22_photo1.jpg'),
(22, 'jpg', '/images/user22_photo2.jpg'),
(23, 'jpg', '/images/user23_photo1.jpg'),
(23, 'jpg', '/images/user23_photo2.jpg'),
(24, 'jpg', '/images/user24_photo1.jpg'),
(24, 'jpg', '/images/user24_photo2.jpg'),
(25, 'jpg', '/images/user25_photo1.jpg'),
(25, 'jpg', '/images/user25_photo2.jpg'),
(26, 'jpg', '/images/user26_photo1.jpg'),
(26, 'jpg', '/images/user26_photo2.jpg'),
(27, 'jpg', '/images/user27_photo1.jpg'),
(27, 'jpg', '/images/user27_photo2.jpg'),
(28, 'jpg', '/images/user28_photo1.jpg'),
(28, 'jpg', '/images/user28_photo2.jpg'),
(29, 'jpg', '/images/user29_photo1.jpg'),
(29, 'jpg', '/images/user29_photo2.jpg'),
(30, 'jpg', '/images/user30_photo1.jpg'),
(30, 'jpg', '/images/user30_photo2.jpg'),
(31, 'jpg', '/images/user31_photo1.jpg'),
(31, 'jpg', '/images/user31_photo2.jpg'),
(32, 'jpg', '/images/user32_photo1.jpg'),
(32, 'jpg', '/images/user32_photo2.jpg'),
(33, 'jpg', '/images/user33_photo1.jpg'),
(33, 'jpg', '/images/user33_photo2.jpg'),
(34, 'jpg', '/images/user34_photo1.jpg'),
(34, 'jpg', '/images/user34_photo2.jpg'),
(35, 'jpg', '/images/user35_photo1.jpg'),
(35, 'jpg', '/images/user35_photo2.jpg'),
(36, 'jpg', '/images/user36_photo1.jpg'),
(36, 'jpg', '/images/user36_photo2.jpg'),
(37, 'jpg', '/images/user37_photo1.jpg'),
(37, 'jpg', '/images/user37_photo2.jpg'),
(38, 'jpg', '/images/user38_photo1.jpg'),
(38, 'jpg', '/images/user38_photo2.jpg'),
(39, 'jpg', '/images/user39_photo1.jpg'),
(39, 'jpg', '/images/user39_photo2.jpg'),
(40, 'jpg', '/images/user40_photo1.jpg'),
(40, 'jpg', '/images/user40_photo2.jpg'),
(41, 'jpg', '/images/user41_photo1.jpg'),
(41, 'jpg', '/images/user41_photo2.jpg'),
(42, 'jpg', '/images/user42_photo1.jpg'),
(42, 'jpg', '/images/user42_photo2.jpg'),
(43, 'jpg', '/images/user43_photo1.jpg'),
(43, 'jpg', '/images/user43_photo2.jpg'),
(44, 'jpg', '/images/user44_photo1.jpg'),
(44, 'jpg', '/images/user44_photo2.jpg'),
(45, 'jpg', '/images/user45_photo1.jpg'),
(45, 'jpg', '/images/user45_photo2.jpg'),
(46, 'jpg', '/images/user46_photo1.jpg'),
(46, 'jpg', '/images/user46_photo2.jpg');

INSERT INTO interactions (`from`, `to`, `state`) VALUES (1, 4, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (4, 1, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (1, 14, "dislike");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (1, 14, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (14, 1, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (19, 1, "dislike");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (19, 1, "dislike");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (19, 1, "dislike");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (19, 1, "dislike");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (1, 19, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (19, 1, "like");

INSERT INTO interactions (`from`, `to`, `state`) VALUES (10, 1, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (24, 1, "like");
INSERT INTO interactions (`from`, `to`, `state`) VALUES (8, 1, "like");

INSERT INTO matches (participant1, participant2) VALUES (1, 4);
INSERT INTO matches (participant1, participant2) VALUES (1, 14);
INSERT INTO matches (participant1, participant2) VALUES (1, 19);

INSERT INTO conversations (match_ID, sender_id, content, creation_date) 
VALUES 
(1, 1, "Hola, me llamo John", '2025-01-30 08:00:00'),
(1, 4, "Soy Emily, encantada", '2025-01-30 08:15:00'),
(1, 4, "Llevas mucho por aqui?", '2025-01-30 08:30:00'),
(1, 4, "Igual podríamos tomar un café", '2025-01-30 08:45:00'),
(1, 1, "Me parece perfecto, que día?", '2025-01-30 09:00:00');

