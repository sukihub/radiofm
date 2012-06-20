CREATE TABLE IF NOT EXISTS `last_played_radiofm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `radiofm_id` int(11) NOT NULL,
  `played_at` datetime NOT NULL,
  `artist` varchar(64) NOT NULL,
  `album` varchar(64) NOT NULL,
  `song` varchar(64) NOT NULL,
  `cover` varchar(64) NOT NULL,
  `program` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
