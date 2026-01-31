<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260131085914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE group_invitations (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, token VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, target_group_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_69F0F6F5F37A13B (token), INDEX IDX_69F0F6F24FF092E (target_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE group_invitations ADD CONSTRAINT FK_69F0F6F24FF092E FOREIGN KEY (target_group_id) REFERENCES app_group (id)');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARBINARY(128) NOT NULL, CHANGE sess_data sess_data LONGBLOB NOT NULL');
        $this->addSql('CREATE INDEX sess_lifetime_idx ON sessions (sess_lifetime)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_invitations DROP FOREIGN KEY FK_69F0F6F24FF092E');
        $this->addSql('DROP TABLE group_invitations');
        $this->addSql('DROP INDEX sess_lifetime_idx ON sessions');
        $this->addSql('ALTER TABLE sessions CHANGE sess_id sess_id VARCHAR(128) NOT NULL, CHANGE sess_data sess_data BLOB NOT NULL');
    }
}
