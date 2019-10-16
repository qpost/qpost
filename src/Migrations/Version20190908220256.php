<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190908220256 extends AbstractMigration {
	public function getDescription(): string {
		return 'Initial database structure';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE block (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, target_id INT NOT NULL, time DATETIME NOT NULL, INDEX IDX_831B9722A76ED395 (user_id), INDEX IDX_831B9722158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE favorite (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, feed_entry_id INT NOT NULL, time DATETIME NOT NULL, INDEX IDX_68C58ED9A76ED395 (user_id), INDEX IDX_68C58ED99AE3DEE5 (feed_entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE feed_entry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, referenced_user_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, token_id VARCHAR(255) DEFAULT NULL, text LONGTEXT DEFAULT NULL, type VARCHAR(32) NOT NULL, nsfw TINYINT(1) NOT NULL, time DATETIME NOT NULL, INDEX IDX_DEAECECCA76ED395 (user_id), INDEX IDX_DEAECECCA7483798 (referenced_user_id), INDEX IDX_DEAECECC727ACA70 (parent_id), INDEX IDX_DEAECECC41DEE7B9 (token_id), INDEX IDX_DEAECECC8CDE5729 (type), INDEX IDX_DEAECECC51CCA19B (nsfw), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE feed_entry_media_file (feed_entry_id INT NOT NULL, media_file_id VARCHAR(255) NOT NULL, INDEX IDX_DEA7A44E9AE3DEE5 (feed_entry_id), INDEX IDX_DEA7A44EF21CFF25 (media_file_id), PRIMARY KEY(feed_entry_id, media_file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE follower (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, time DATETIME NOT NULL, INDEX IDX_B9D60946F624B39D (sender_id), INDEX IDX_B9D60946CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE follow_request (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, time DATETIME NOT NULL, INDEX IDX_6562D72FF624B39D (sender_id), INDEX IDX_6562D72FCD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE media_file (id VARCHAR(255) NOT NULL, original_uploader_id INT DEFAULT NULL, sha256 VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, time DATETIME NOT NULL, UNIQUE INDEX UNIQ_4FD8E9C35CC814F7 (sha256), UNIQUE INDEX UNIQ_4FD8E9C3F47645AE (url), INDEX IDX_4FD8E9C3E1686C5D (original_uploader_id), INDEX IDX_4FD8E9C38CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, referenced_user_id INT DEFAULT NULL, referenced_feed_entry_id INT DEFAULT NULL, type VARCHAR(32) NOT NULL, seen TINYINT(1) NOT NULL, notified TINYINT(1) NOT NULL, time DATETIME NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CAA7483798 (referenced_user_id), INDEX IDX_BF5476CA7E705CC6 (referenced_feed_entry_id), INDEX IDX_BF5476CAA4520A18 (seen), INDEX IDX_BF5476CAD23269D4 (notified), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE suspension (id INT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, staff_id INT DEFAULT NULL, reason LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, time DATETIME NOT NULL, INDEX IDX_82AF0500158E0B66 (target_id), INDEX IDX_82AF0500D4D57CD (staff_id), INDEX IDX_82AF05004B1EFC02 (active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE token (id VARCHAR(255) NOT NULL, user_id INT NOT NULL, last_ip VARCHAR(255) NOT NULL, user_agent LONGTEXT NOT NULL, time DATETIME NOT NULL, last_access_time DATETIME NOT NULL, expiry DATETIME NOT NULL, INDEX IDX_5F37A13BA76ED395 (user_id), INDEX IDX_5F37A13B38B0169B (expiry), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, featured_box_id INT DEFAULT NULL, gigadrive_data_id INT DEFAULT NULL, display_name VARCHAR(24) NOT NULL, username VARCHAR(16) NOT NULL, password VARCHAR(60) DEFAULT NULL, email VARCHAR(50) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, bio LONGTEXT DEFAULT NULL, birthday DATE DEFAULT NULL, privacy_level VARCHAR(32) NOT NULL, time DATETIME NOT NULL, email_activated TINYINT(1) NOT NULL, email_activation_token VARCHAR(7) DEFAULT NULL, verified TINYINT(1) NOT NULL, last_username_change DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649C894489E (featured_box_id), UNIQUE INDEX UNIQ_8D93D64950D1D405 (gigadrive_data_id), INDEX IDX_8D93D649D5499347 (display_name), INDEX IDX_8D93D649E7927C74 (email), INDEX IDX_8D93D6494709B432 (birthday), INDEX IDX_8D93D64960A35E64 (privacy_level), INDEX IDX_8D93D649F45FF19 (verified), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE user_featured_box (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE user_featured_box_user (user_featured_box_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8B24DD56DA482411 (user_featured_box_id), INDEX IDX_8B24DD56A76ED395 (user_id), PRIMARY KEY(user_featured_box_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('CREATE TABLE user_gigadrive_data (id INT AUTO_INCREMENT NOT NULL, account_id INT NOT NULL, token VARCHAR(255) NOT NULL, join_date DATETIME NOT NULL, last_update DATETIME NOT NULL, UNIQUE INDEX UNIQ_3F5064EA9B6B5FBA (account_id), INDEX IDX_3F5064EA5F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
		$this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722158E0B66 FOREIGN KEY (target_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED99AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECCA7483798 FOREIGN KEY (referenced_user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC727ACA70 FOREIGN KEY (parent_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC41DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id)');
		$this->addSql('ALTER TABLE feed_entry_media_file ADD CONSTRAINT FK_DEA7A44E9AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE feed_entry_media_file ADD CONSTRAINT FK_DEA7A44EF21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE media_file ADD CONSTRAINT FK_4FD8E9C3E1686C5D FOREIGN KEY (original_uploader_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA7483798 FOREIGN KEY (referenced_user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7E705CC6 FOREIGN KEY (referenced_feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500158E0B66 FOREIGN KEY (target_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500D4D57CD FOREIGN KEY (staff_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C894489E FOREIGN KEY (featured_box_id) REFERENCES user_featured_box (id)');
		$this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64950D1D405 FOREIGN KEY (gigadrive_data_id) REFERENCES user_gigadrive_data (id)');
		$this->addSql('ALTER TABLE user_featured_box_user ADD CONSTRAINT FK_8B24DD56DA482411 FOREIGN KEY (user_featured_box_id) REFERENCES user_featured_box (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE user_featured_box_user ADD CONSTRAINT FK_8B24DD56A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED99AE3DEE5');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECC727ACA70');
		$this->addSql('ALTER TABLE feed_entry_media_file DROP FOREIGN KEY FK_DEA7A44E9AE3DEE5');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7E705CC6');
		$this->addSql('ALTER TABLE feed_entry_media_file DROP FOREIGN KEY FK_DEA7A44EF21CFF25');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECC41DEE7B9');
		$this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A76ED395');
		$this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722158E0B66');
		$this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9A76ED395');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECCA76ED395');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECCA7483798');
		$this->addSql('ALTER TABLE follower DROP FOREIGN KEY FK_B9D60946F624B39D');
		$this->addSql('ALTER TABLE follower DROP FOREIGN KEY FK_B9D60946CD53EDB6');
		$this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FF624B39D');
		$this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FCD53EDB6');
		$this->addSql('ALTER TABLE media_file DROP FOREIGN KEY FK_4FD8E9C3E1686C5D');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA7483798');
		$this->addSql('ALTER TABLE suspension DROP FOREIGN KEY FK_82AF0500158E0B66');
		$this->addSql('ALTER TABLE suspension DROP FOREIGN KEY FK_82AF0500D4D57CD');
		$this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
		$this->addSql('ALTER TABLE user_featured_box_user DROP FOREIGN KEY FK_8B24DD56A76ED395');
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C894489E');
		$this->addSql('ALTER TABLE user_featured_box_user DROP FOREIGN KEY FK_8B24DD56DA482411');
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64950D1D405');
		$this->addSql('DROP TABLE block');
		$this->addSql('DROP TABLE favorite');
		$this->addSql('DROP TABLE feed_entry');
		$this->addSql('DROP TABLE feed_entry_media_file');
		$this->addSql('DROP TABLE follower');
		$this->addSql('DROP TABLE follow_request');
		$this->addSql('DROP TABLE media_file');
		$this->addSql('DROP TABLE notification');
		$this->addSql('DROP TABLE suspension');
		$this->addSql('DROP TABLE token');
		$this->addSql('DROP TABLE user');
		$this->addSql('DROP TABLE user_featured_box');
		$this->addSql('DROP TABLE user_featured_box_user');
		$this->addSql('DROP TABLE user_gigadrive_data');
	}
}
