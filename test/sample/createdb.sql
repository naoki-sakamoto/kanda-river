-- データベース作成
-- rootユーザーで実行する。
DROP DATABASE IF EXISTS `sampledb`;
CREATE DATABASE `sampledb` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL ON `sampledb`.* TO 'sample'@'localhost' IDENTIFIED BY 'password';
GRANT ALL ON `sampledb`.* TO 'sample'@'%' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;

-- テーブル作成
USE `sampledb`;
CREATE TABLE `player` (
  `id` int NOT NULL PRIMARY KEY,
  `password` varchar(60) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
