CREATE TABLE `www_article` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `creator_uid` int(11) unsigned DEFAULT NULL,
  `changer_uid` int(11) unsigned DEFAULT NULL,
  `record_changed_date` datetime DEFAULT NULL,
  `record_created_date` datetime DEFAULT NULL,
  `release_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `changed_date` datetime DEFAULT NULL,
  `published_date` datetime DEFAULT NULL,
  `post_type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) NOT NULL DEFAULT '',
  `service_comment` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `source` varchar(255) NOT NULL,
  `brief` text NOT NULL,
  `content` longtext NOT NULL,
  `uri` varchar(255) NOT NULL DEFAULT '',
  `unique_visitors` mediumint(8) unsigned NOT NULL,
  `total_visitors` mediumint(8) unsigned NOT NULL,
  `like` mediumint(8) unsigned NOT NULL,
  `dislike` mediumint(8) unsigned NOT NULL,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `order` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `unique_visitors` (`unique_visitors`),
  KEY `total_visitors` (`total_visitors`),
  KEY `like` (`like`),
  KEY `dislike` (`dislike`)
) ENGINE=MyISAM AUTO_INCREMENT=238 DEFAULT CHARSET=utf8
