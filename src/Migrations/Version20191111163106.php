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
final class Version20191111163106 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE trending_hashtag_data (id INT AUTO_INCREMENT NOT NULL, posts_this_week INT NOT NULL, time DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('ALTER TABLE hashtag ADD trending_data_id INT DEFAULT NULL');
		$this->addSql('ALTER TABLE hashtag ADD CONSTRAINT FK_5AB52A6164235010 FOREIGN KEY (trending_data_id) REFERENCES trending_hashtag_data (id)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_5AB52A6164235010 ON hashtag (trending_data_id)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE hashtag DROP FOREIGN KEY FK_5AB52A6164235010');
		$this->addSql('DROP TABLE trending_hashtag_data');
		$this->addSql('DROP INDEX UNIQ_5AB52A6164235010 ON hashtag');
		$this->addSql('ALTER TABLE hashtag DROP trending_data_id');
	}
}
