<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110092451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_group (group_id CHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_BB13C9085E237E06 (name), PRIMARY KEY (group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE app_user ADD group_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E9FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (group_id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_88BDF3E9FE54D947 ON app_user (group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE app_group');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E9FE54D947');
        $this->addSql('DROP INDEX IDX_88BDF3E9FE54D947 ON app_user');
        $this->addSql('ALTER TABLE app_user DROP group_id');
    }
}
