CREATE TABLE `www_article_rss_import` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `source` varchar(255) NOT NULL,
  `post_type` int(11) unsigned NOT NULL DEFAULT '0',
  `creator_uid` int(11) DEFAULT '0',
  `changer_uid` int(11) DEFAULT '0',
  `created_date` datetime DEFAULT NULL,
  `changed_date` datetime DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
