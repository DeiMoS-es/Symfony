<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260103084440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY `FK_30EF6577FE54D947`');
        $this->addSql('ALTER TABLE app_group_recommendation ADD stats JSON DEFAULT NULL, DROP avg_script, DROP avg_main_actor, DROP avg_main_actress, DROP avg_secondary_actors, DROP avg_director, CHANGE id id CHAR(36) NOT NULL, CHANGE total_votes total_votes INT DEFAULT NULL, CHANGE final_score average_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF6577FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_review CHANGE recommendation_id recommendation_id CHAR(36) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF6577FE54D947');
        $this->addSql('ALTER TABLE app_group_recommendation ADD avg_script DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD avg_main_actor DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD avg_main_actress DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD avg_secondary_actors DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD avg_director DOUBLE PRECISION DEFAULT \'0\' NOT NULL, DROP stats, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE total_votes total_votes INT DEFAULT 0 NOT NULL, CHANGE average_score final_score DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT `FK_30EF6577FE54D947` FOREIGN KEY (group_id) REFERENCES app_group (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE app_group_review CHANGE recommendation_id recommendation_id INT NOT NULL');
    }
}
