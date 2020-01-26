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
final class Version20200120200708 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE3C947C0F');
		$this->addSql('DROP TABLE poll');
		$this->addSql('DROP TABLE poll_vote');
		$this->addSql('ALTER TABLE user_appearance_settings ADD night_mode TINYINT(1) NOT NULL, ADD autoplay_gifs TINYINT(1) NOT NULL, ADD show_trends TINYINT(1) NOT NULL, ADD show_suggested_users TINYINT(1) NOT NULL, ADD show_birthdays TINYINT(1) NOT NULL, ADD show_mature_warning TINYINT(1) NOT NULL');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE poll (id INT AUTO_INCREMENT NOT NULL, feed_entry_id INT NOT NULL, options LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:array)\', expiry DATETIME NOT NULL, time DATETIME NOT NULL, winner INT DEFAULT NULL, UNIQUE INDEX UNIQ_84BCFA459AE3DEE5 (feed_entry_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
		$this->addSql('CREATE TABLE poll_vote (id INT AUTO_INCREMENT NOT NULL, poll_id INT NOT NULL, user_id INT NOT NULL, selected_option INT NOT NULL, time DATETIME NOT NULL, UNIQUE INDEX UNIQ_ED568EBE3C947C0FA76ED395 (poll_id, user_id), INDEX IDX_ED568EBE3C947C0F (poll_id), INDEX IDX_ED568EBEA62CADF7 (selected_option), INDEX IDX_ED568EBEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
		$this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA459AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
		$this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE user_appearance_settings DROP night_mode, DROP autoplay_gifs, DROP show_trends, DROP show_suggested_users, DROP show_birthdays, DROP show_mature_warning');
	}
}
