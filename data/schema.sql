set foreign_key_checks = 0;

DROP TABLE IF EXISTS `comment_wall`;
CREATE TABLE IF NOT EXISTS `comment_wall` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `by_user_id` int(11) unsigned NOT NULL,
  `text` text NOT NULL,
  `proxy_model_id` int(11) unsigned DEFAULT NULL,
  `proxy_id` int(11) unsigned DEFAULT NULL,
  `is_system` int(1) default 0,
  `language_id` int(11) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_response_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `response_to_id` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `by_user_id` (`by_user_id`),
  KEY `proxy_model_id` (`proxy_model_id`),
  KEY `proxy_id` (`proxy_id`),
  KEY `FK_comment_wall4` (`response_to_id`),
  KEY `FK_comment_wall5` (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `comment_wall`
--


--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `comment_wall`
--
ALTER TABLE `comment_wall`
  ADD CONSTRAINT `FK_comment_wall2` FOREIGN KEY (`by_user_id`) REFERENCES `auth_user` (`id`),
  ADD CONSTRAINT `FK_comment_wall3` FOREIGN KEY (`proxy_model_id`) REFERENCES `centurion_content_type` (`id`),
  ADD CONSTRAINT `FK_comment_wall4` FOREIGN KEY (`response_to_id`) REFERENCES `comment_wall` (`id`);

