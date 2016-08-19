CREATE TABLE `www_article_indexer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `creator_uid` int(11) DEFAULT NULL,
  `changer_uid` int(11) DEFAULT NULL,
  `record_created_date` datetime DEFAULT NULL,
  `record_changed_date` datetime DEFAULT NULL,
  `changed_date` datetime DEFAULT NULL,
  `published_date` datetime DEFAULT NULL,
  `post_type` tinyint(3) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `source` varchar(200) NOT NULL,
  `brief` text NOT NULL,
  `content` longtext NOT NULL,
  `published` tinyint(1) unsigned NOT NULL,
  `author` varchar(255) NOT NULL,
  `release_date` datetime NOT NULL,
  `categories` text NOT NULL,
  `tags` text NOT NULL,
  `images` text NOT NULL,
  `unique_visitors` mediumint(8) unsigned NOT NULL,
  `total_visitors` mediumint(8) unsigned NOT NULL,
  `comments` text NOT NULL,
  `order` int(11) unsigned NOT NULL DEFAULT '0',
  `like` mediumint(8) unsigned NOT NULL,
  `dislike` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_visitors` (`unique_visitors`),
  KEY `total_visitors` (`total_visitors`),
  KEY `like` (`like`),
  KEY `dislike` (`dislike`),
  KEY `item_id` (`item_id`),
  KEY `release_date` (`release_date`),
  FULLTEXT KEY `tags` (`tags`),
  FULLTEXT KEY `categories` (`categories`)
) ENGINE=MyISAM AUTO_INCREMENT=238 DEFAULT CHARSET=utf8
