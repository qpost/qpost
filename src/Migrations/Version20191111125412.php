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
final class Version20191111125412 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE feed_entry_hashtag (feed_entry_id INT NOT NULL, hashtag_id VARCHAR(64) NOT NULL, INDEX IDX_8A05CC149AE3DEE5 (feed_entry_id), INDEX IDX_8A05CC14FB34EF56 (hashtag_id), PRIMARY KEY(feed_entry_id, hashtag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('CREATE TABLE hashtag (id VARCHAR(64) NOT NULL, creator_id INT DEFAULT NULL, creating_entry_id INT DEFAULT NULL, time DATETIME NOT NULL, INDEX IDX_5AB52A6161220EA6 (creator_id), INDEX IDX_5AB52A615DED044A (creating_entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('ALTER TABLE feed_entry_hashtag ADD CONSTRAINT FK_8A05CC149AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE feed_entry_hashtag ADD CONSTRAINT FK_8A05CC14FB34EF56 FOREIGN KEY (hashtag_id) REFERENCES hashtag (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A6161220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A615DED044A FOREIGN KEY (creating_entry_id) REFERENCES feed_entry (id)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE feed_entry_hashtag DROP FOREIGN KEY FK_8A05CC14FB34EF56');
		$this->addSql('DROP TABLE feed_entry_hashtag');
		$this->addSql('DROP TABLE hashtag');
	}
}
