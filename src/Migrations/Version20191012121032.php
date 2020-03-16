<?php
/**
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
final class Version20191012121032 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('CREATE TABLE ip_stack_result (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(45) NOT NULL, type VARCHAR(16) NOT NULL, continent_code VARCHAR(4) DEFAULT NULL, continent_name VARCHAR(24) DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, country_name VARCHAR(64) DEFAULT NULL, region_code VARCHAR(2) DEFAULT NULL, region_name VARCHAR(64) DEFAULT NULL, city VARCHAR(64) DEFAULT NULL, zip_code INT NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, time DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql('ALTER TABLE token ADD ip_stack_result_id INT');
		$this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BE8DB2121 FOREIGN KEY (ip_stack_result_id) REFERENCES ip_stack_result (id)');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_5F37A13BE8DB2121 ON token (ip_stack_result_id)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BE8DB2121');
		$this->addSql('DROP TABLE ip_stack_result');
		$this->addSql('DROP INDEX UNIQ_5F37A13BE8DB2121 ON token');
		$this->addSql('ALTER TABLE token DROP ip_stack_result_id');
	}
}
