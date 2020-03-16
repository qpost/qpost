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
final class Version20191116223321 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE push_subscription ROW_FORMAT=DYNAMIC');
		$this->addSql('ALTER TABLE push_subscription ADD token_id VARCHAR(255) DEFAULT NULL');
		$this->addSql('ALTER TABLE push_subscription ADD CONSTRAINT FK_562830F341DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id)');
		$this->addSql('CREATE INDEX IDX_562830F341DEE7B9 ON push_subscription (token_id)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

		$this->addSql('ALTER TABLE push_subscription ROW_FORMAT=COMPACT');
		$this->addSql('ALTER TABLE push_subscription DROP FOREIGN KEY FK_562830F341DEE7B9');
		$this->addSql('DROP INDEX IDX_562830F341DEE7B9 ON push_subscription');
		$this->addSql('ALTER TABLE push_subscription DROP token_id');
	}
}
