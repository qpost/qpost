<?php
/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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
final class Version20200815164313 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722158E0B66');
		$this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A76ED395');
		$this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722158E0B66 FOREIGN KEY (target_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_831B97226F949845 ON block (time)');
		$this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED99AE3DEE5');
		$this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9A76ED395');
		$this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED99AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECC41DEE7B9');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECC727ACA70');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECCA7483798');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECCA76ED395');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC41DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id) ON DELETE SET NULL');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC727ACA70 FOREIGN KEY (parent_id) REFERENCES feed_entry (id) ON DELETE SET NULL');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECCA7483798 FOREIGN KEY (referenced_user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_DEAECECC6F949845 ON feed_entry (time)');
		$this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FCD53EDB6');
		$this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FF624B39D');
		$this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_6562D72F6F949845 ON follow_request (time)');
		$this->addSql('ALTER TABLE follower DROP FOREIGN KEY FK_B9D60946CD53EDB6');
		$this->addSql('ALTER TABLE follower DROP FOREIGN KEY FK_B9D60946F624B39D');
		$this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946F624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_B9D609466F949845 ON follower (time)');
		$this->addSql('ALTER TABLE hashtag DROP FOREIGN KEY FK_5AB52A615DED044A');
		$this->addSql('ALTER TABLE hashtag DROP FOREIGN KEY FK_5AB52A6161220EA6');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A615DED044A FOREIGN KEY (creating_entry_id) REFERENCES feed_entry (id) ON DELETE SET NULL');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A6161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id) ON DELETE SET NULL');
		$this->addSql('ALTER TABLE linked_account DROP FOREIGN KEY FK_167E6E33A76ED395');
		$this->addSql('ALTER TABLE linked_account ADD CONSTRAINT FK_167E6E33A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE media_file DROP FOREIGN KEY FK_4FD8E9C3E1686C5D');
		$this->addSql('ALTER TABLE media_file ADD CONSTRAINT FK_4FD8E9C3E1686C5D FOREIGN KEY (original_uploader_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7E705CC6');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA7483798');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7E705CC6 FOREIGN KEY (referenced_feed_entry_id) REFERENCES feed_entry (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA7483798 FOREIGN KEY (referenced_user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_BF5476CA8CDE5729 ON notification (type)');
		$this->addSql('CREATE INDEX IDX_BF5476CA6F949845 ON notification (time)');
		$this->addSql('ALTER TABLE push_subscription DROP FOREIGN KEY FK_562830F3A76ED395');
		$this->addSql('ALTER TABLE push_subscription ADD CONSTRAINT FK_562830F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE reset_password_token DROP FOREIGN KEY FK_452C9EC5A76ED395');
		$this->addSql('ALTER TABLE reset_password_token ADD CONSTRAINT FK_452C9EC5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE suspension DROP FOREIGN KEY FK_82AF0500158E0B66');
		$this->addSql('ALTER TABLE suspension DROP FOREIGN KEY FK_82AF0500D4D57CD');
		$this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500158E0B66 FOREIGN KEY (target_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500D4D57CD FOREIGN KEY (staff_id) REFERENCES user (id) ON DELETE SET NULL');
		$this->addSql('ALTER TABLE temporary_oauth_credentials DROP FOREIGN KEY FK_856D9457A76ED395');
		$this->addSql('ALTER TABLE temporary_oauth_credentials ADD CONSTRAINT FK_856D9457A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
		$this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE user_appearance_settings DROP FOREIGN KEY FK_93D72DFBA76ED395');
		$this->addSql('ALTER TABLE user_appearance_settings ADD CONSTRAINT FK_93D72DFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE username_history_entry DROP FOREIGN KEY FK_14C41C5AA76ED395');
		$this->addSql('ALTER TABLE username_history_entry ADD CONSTRAINT FK_14C41C5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722A76ED395');
		$this->addSql('ALTER TABLE block DROP FOREIGN KEY FK_831B9722158E0B66');
		$this->addSql('DROP INDEX IDX_831B97226F949845 ON block');
		$this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE block ADD CONSTRAINT FK_831B9722158E0B66 FOREIGN KEY (target_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED9A76ED395');
		$this->addSql('ALTER TABLE favorite DROP FOREIGN KEY FK_68C58ED99AE3DEE5');
		$this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED99AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECCA76ED395');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECCA7483798');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECC727ACA70');
		$this->addSql('ALTER TABLE feed_entry DROP FOREIGN KEY FK_DEAECECC41DEE7B9');
		$this->addSql('DROP INDEX IDX_DEAECECC6F949845 ON feed_entry');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECCA7483798 FOREIGN KEY (referenced_user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC727ACA70 FOREIGN KEY (parent_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE feed_entry ADD CONSTRAINT FK_DEAECECC41DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id)');
		$this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FF624B39D');
		$this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FCD53EDB6');
		$this->addSql('DROP INDEX IDX_6562D72F6F949845 ON follow_request');
		$this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE follower DROP FOREIGN KEY FK_B9D60946F624B39D');
		$this->addSql('ALTER TABLE follower DROP FOREIGN KEY FK_B9D60946CD53EDB6');
		$this->addSql('DROP INDEX IDX_B9D609466F949845 ON follower');
		$this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE hashtag DROP FOREIGN KEY FK_5AB52A6161220EA6');
		$this->addSql('ALTER TABLE hashtag DROP FOREIGN KEY FK_5AB52A615DED044A');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A6161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A615DED044A FOREIGN KEY (creating_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE linked_account DROP FOREIGN KEY FK_167E6E33A76ED395');
		$this->addSql('ALTER TABLE linked_account ADD CONSTRAINT FK_167E6E33A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE media_file DROP FOREIGN KEY FK_4FD8E9C3E1686C5D');
		$this->addSql('ALTER TABLE media_file ADD CONSTRAINT FK_4FD8E9C3E1686C5D FOREIGN KEY (original_uploader_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA7483798');
		$this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7E705CC6');
		$this->addSql('DROP INDEX IDX_BF5476CA8CDE5729 ON notification');
		$this->addSql('DROP INDEX IDX_BF5476CA6F949845 ON notification');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA7483798 FOREIGN KEY (referenced_user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7E705CC6 FOREIGN KEY (referenced_feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE push_subscription DROP FOREIGN KEY FK_562830F3A76ED395');
		$this->addSql('ALTER TABLE push_subscription ADD CONSTRAINT FK_562830F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE reset_password_token DROP FOREIGN KEY FK_452C9EC5A76ED395');
		$this->addSql('ALTER TABLE reset_password_token ADD CONSTRAINT FK_452C9EC5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE suspension DROP FOREIGN KEY FK_82AF0500158E0B66');
		$this->addSql('ALTER TABLE suspension DROP FOREIGN KEY FK_82AF0500D4D57CD');
		$this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500158E0B66 FOREIGN KEY (target_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE suspension ADD CONSTRAINT FK_82AF0500D4D57CD FOREIGN KEY (staff_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE temporary_oauth_credentials DROP FOREIGN KEY FK_856D9457A76ED395');
		$this->addSql('ALTER TABLE temporary_oauth_credentials ADD CONSTRAINT FK_856D9457A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
		$this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE user_appearance_settings DROP FOREIGN KEY FK_93D72DFBA76ED395');
		$this->addSql('ALTER TABLE user_appearance_settings ADD CONSTRAINT FK_93D72DFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE username_history_entry DROP FOREIGN KEY FK_14C41C5AA76ED395');
		$this->addSql('ALTER TABLE username_history_entry ADD CONSTRAINT FK_14C41C5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
	}
}
