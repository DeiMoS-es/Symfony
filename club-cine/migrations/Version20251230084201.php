<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230084201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_group_invitation (id CHAR(36) NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, status VARCHAR(32) NOT NULL, invited_group_id CHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_B2EEE1B15F37A13B (token), INDEX IDX_B2EEE1B1D45911AD (invited_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE app_group_invitation ADD CONSTRAINT FK_B2EEE1B1D45911AD FOREIGN KEY (invited_group_id) REFERENCES app_group (id)');
        $this->addSql('ALTER TABLE app_group_recommendation CHANGE avg_script avg_script DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_main_actor avg_main_actor DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_main_actress avg_main_actress DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_secondary_actors avg_secondary_actors DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_director avg_director DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group_invitation DROP FOREIGN KEY FK_B2EEE1B1D45911AD');
        $this->addSql('DROP TABLE app_group_invitation');
        $this->addSql('ALTER TABLE app_group_recommendation CHANGE avg_script avg_script DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_main_actor avg_main_actor DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_main_actress avg_main_actress DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_secondary_actors avg_secondary_actors DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_director avg_director DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
