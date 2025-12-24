<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224091741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_group_member (id CHAR(36) NOT NULL, role VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, group_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_61EC3829FE54D947 (group_id), INDEX IDX_61EC3829A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE app_group_member ADD CONSTRAINT FK_61EC3829FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_member ADD CONSTRAINT FK_61EC3829A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group_member DROP FOREIGN KEY FK_61EC3829FE54D947');
        $this->addSql('ALTER TABLE app_group_member DROP FOREIGN KEY FK_61EC3829A76ED395');
        $this->addSql('DROP TABLE app_group_member');
    }
}
