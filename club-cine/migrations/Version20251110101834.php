<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110101834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_group (id CHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, owner_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_BB13C9085E237E06 (name), UNIQUE INDEX UNIQ_BB13C908989D9B62 (slug), INDEX IDX_BB13C9087E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, token_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, user_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1D17F50A6 (uuid), INDEX IDX_9BACE7E1A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE app_group ADD CONSTRAINT FK_BB13C9087E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT FK_9BACE7E1A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_user ADD group_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E9FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_88BDF3E9FE54D947 ON app_user (group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group DROP FOREIGN KEY FK_BB13C9087E3C61F9');
        $this->addSql('ALTER TABLE refresh_tokens DROP FOREIGN KEY FK_9BACE7E1A76ED395');
        $this->addSql('DROP TABLE app_group');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E9FE54D947');
        $this->addSql('DROP INDEX IDX_88BDF3E9FE54D947 ON app_user');
        $this->addSql('ALTER TABLE app_user DROP group_id');
    }
}
