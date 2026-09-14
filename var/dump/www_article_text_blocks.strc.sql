CREATE TABLE `www_article_text_blocks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_datetime` datetime NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `item_id` int(11) unsigned NOT NULL,
  `block_type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `public` (`published`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
