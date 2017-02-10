SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `categories`
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `likes`
-- ----------------------------
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  #изменение имени пользователя и его аватарки происходит редко, поэтому есть смысл провести денормализацию,
  # чтобы избежать ненужные JOIN'ы при получении списка лайков
  `display_name` varchar(100) DEFAULT NULL,
  `img_folder` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`), #индекс необходим для выборки лайков по конкретному посту
  KEY `user_id` (`user_id`) #индекс необходим для выборки лайков по конкретному пользователю,
  # а также для обновления полей display_name и img_folder при обновлении в таблице users
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `posts`
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL, #51 байт
  `content` varchar(140) DEFAULT NULL,#+141 байт
  `img_folder` varchar(50) DEFAULT NULL,#+51 байт = 243 байта под контент
  `likes` int(11) DEFAULT NULL, #отображение количества лайков происходит гораздо чаще чем добавление новых,
  #поэтому имеет смысл избавиться от дорогостоящей операции подсчета лайков за счет добавления счетчика
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`) #индекс необходим для фильтрации по категории
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `img_folder` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
