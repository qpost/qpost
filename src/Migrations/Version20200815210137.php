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
final class Version20200815210137 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE media_attachment DROP FOREIGN KEY FK_737A172F9AE3DEE5');
		$this->addSql('ALTER TABLE media_attachment DROP FOREIGN KEY FK_737A172FF21CFF25');
		$this->addSql('ALTER TABLE media_attachment ADD CONSTRAINT FK_737A172F9AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE media_attachment ADD CONSTRAINT FK_737A172FF21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id) ON DELETE CASCADE');
		$this->addSql('CREATE INDEX IDX_737A172F462CE4F5 ON media_attachment (position)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE media_attachment DROP FOREIGN KEY FK_737A172F9AE3DEE5');
		$this->addSql('ALTER TABLE media_attachment DROP FOREIGN KEY FK_737A172FF21CFF25');
		$this->addSql('DROP INDEX IDX_737A172F462CE4F5 ON media_attachment');
		$this->addSql('ALTER TABLE media_attachment ADD CONSTRAINT FK_737A172F9AE3DEE5 FOREIGN KEY (feed_entry_id) REFERENCES feed_entry (id)');
		$this->addSql('ALTER TABLE media_attachment ADD CONSTRAINT FK_737A172FF21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id)');
	}
}
