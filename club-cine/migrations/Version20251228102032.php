<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228102032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_groups (user_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, INDEX IDX_953F224DA76ED395 (user_id), INDEX IDX_953F224DFE54D947 (group_id), PRIMARY KEY (user_id, group_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_groups ADD CONSTRAINT FK_953F224DA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_groups ADD CONSTRAINT FK_953F224DFE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_recommendation CHANGE avg_script avg_script DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_main_actor avg_main_actor DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_main_actress avg_main_actress DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_secondary_actors avg_secondary_actors DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE avg_director avg_director DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY `FK_88BDF3E9FE54D947`');
        $this->addSql('DROP INDEX IDX_88BDF3E9FE54D947 ON app_user');
        $this->addSql('ALTER TABLE app_user DROP group_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_groups DROP FOREIGN KEY FK_953F224DA76ED395');
        $this->addSql('ALTER TABLE user_groups DROP FOREIGN KEY FK_953F224DFE54D947');
        $this->addSql('DROP TABLE user_groups');
        $this->addSql('ALTER TABLE app_group_recommendation CHANGE avg_script avg_script DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_main_actor avg_main_actor DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_main_actress avg_main_actress DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_secondary_actors avg_secondary_actors DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE avg_director avg_director DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE app_user ADD group_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT `FK_88BDF3E9FE54D947` FOREIGN KEY (group_id) REFERENCES app_group (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_88BDF3E9FE54D947 ON app_user (group_id)');
    }
}
