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
final class Version20200815174504 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE media_attachment (id INT AUTO_INCREMENT NOT NULL, feed_entry_id INT NOT NULL, media_file_id VARCHAR(255) NOT NULL, position INT NOT NULL, time DATETIME NOT NULL, INDEX IDX_737A172F9AE3DEE5 (feed_entry_id), INDEX IDX_737A172FF21CFF25 (media_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE media_attachment ADD CONSTRAINT FK_737A172F9AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE media_attachment ADD CONSTRAINT FK_737A172FF21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id)');
		$this->addSql('INSERT INTO media_attachment (`feed_entry_id`,`media_file_id`,`position`,`time`) SELECT feed_entry_id, media_file_id, 0, CURRENT_TIME() FROM feed_entry_media_file;');
		$this->addSql('DROP TABLE feed_entry_media_file');
		$this->addSql('CREATE INDEX IDX_562830F3E68472A6 ON push_subscription (subscription_hash)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE feed_entry_media_file (feed_entry_id INT NOT NULL, media_file_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_DEA7A44E9AE3DEE5 (feed_entry_id), INDEX IDX_DEA7A44EF21CFF25 (media_file_id), PRIMARY KEY(feed_entry_id, media_file_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
		$this->addSql('ALTER TABLE feed_entry_media_file ADD CONSTRAINT FK_DEA7A44E9AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE feed_entry_media_file ADD CONSTRAINT FK_DEA7A44EF21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id) ON DELETE CASCADE');
		$this->addSql('INSERT INTO feed_entry_media_file (`feed_entry_id`, `media_file_id`) SELECT `feed_entry_id`, `media_attachment_id` FROM media_attachment;');
		$this->addSql('DROP TABLE media_attachment');
		$this->addSql('DROP INDEX IDX_562830F3E68472A6 ON push_subscription');
	}
}
