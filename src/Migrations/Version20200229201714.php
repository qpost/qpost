<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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
final class Version20200229201714 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE username_history_entry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(16) NOT NULL, time DATETIME NOT NULL, ip VARCHAR(45) DEFAULT NULL, INDEX IDX_14C41C5AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('ALTER TABLE username_history_entry ADD CONSTRAINT FK_14C41C5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE linked_account CHANGE on_profile on_profile TINYINT(1) NOT NULL');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('DROP TABLE username_history_entry');
		$this->addSql('ALTER TABLE linked_account CHANGE on_profile on_profile TINYINT(1) DEFAULT \'1\' NOT NULL');
	}
}
