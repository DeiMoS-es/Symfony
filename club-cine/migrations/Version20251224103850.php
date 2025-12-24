<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224103850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_group_member (id CHAR(36) NOT NULL, role VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, group_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_61EC3829FE54D947 (group_id), INDEX IDX_61EC3829A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_group_recommendation (id CHAR(36) NOT NULL, created_at DATETIME NOT NULL, deadline DATETIME NOT NULL, status VARCHAR(20) NOT NULL, final_score DOUBLE PRECISION DEFAULT NULL, total_votes INT DEFAULT 0 NOT NULL, group_id CHAR(36) NOT NULL, movie_id CHAR(36) NOT NULL, recommender_id CHAR(36) NOT NULL, INDEX IDX_30EF6577FE54D947 (group_id), INDEX IDX_30EF65778F93B6FC (movie_id), INDEX IDX_30EF65775B00A74 (recommender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_group_review (id CHAR(36) NOT NULL, score_script INT NOT NULL, score_main_actor INT NOT NULL, score_main_actress INT NOT NULL, score_secondary_actors INT NOT NULL, score_director INT NOT NULL, average_score DOUBLE PRECISION NOT NULL, comment VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, recommendation_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_684B4397D173940B (recommendation_id), INDEX IDX_684B4397A76ED395 (user_id), UNIQUE INDEX unique_user_recommendation (recommendation_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_835033F85E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movie (id CHAR(36) NOT NULL, tmdb_id INT NOT NULL, title VARCHAR(255) NOT NULL, original_title VARCHAR(255) DEFAULT NULL, original_language VARCHAR(10) DEFAULT NULL, overview LONGTEXT DEFAULT NULL, poster_path VARCHAR(255) DEFAULT NULL, backdrop_path VARCHAR(255) DEFAULT NULL, release_date DATE DEFAULT NULL, runtime INT DEFAULT NULL, adult TINYINT(1) NOT NULL, popularity DOUBLE PRECISION DEFAULT NULL, vote_average DOUBLE PRECISION DEFAULT NULL, vote_count INT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D5EF26F55BCC5E5 (tmdb_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movie_genres (movie_id CHAR(36) NOT NULL, genre_id INT NOT NULL, INDEX IDX_512C78638F93B6FC (movie_id), INDEX IDX_512C78634296D31F (genre_id), PRIMARY KEY (movie_id, genre_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE app_group_member ADD CONSTRAINT FK_61EC3829FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_member ADD CONSTRAINT FK_61EC3829A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF6577FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF65778F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF65775B00A74 FOREIGN KEY (recommender_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_group_review ADD CONSTRAINT FK_684B4397D173940B FOREIGN KEY (recommendation_id) REFERENCES app_group_recommendation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_review ADD CONSTRAINT FK_684B4397A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE movie_genres ADD CONSTRAINT FK_512C78638F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_genres ADD CONSTRAINT FK_512C78634296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group_member DROP FOREIGN KEY FK_61EC3829FE54D947');
        $this->addSql('ALTER TABLE app_group_member DROP FOREIGN KEY FK_61EC3829A76ED395');
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF6577FE54D947');
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF65778F93B6FC');
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF65775B00A74');
        $this->addSql('ALTER TABLE app_group_review DROP FOREIGN KEY FK_684B4397D173940B');
        $this->addSql('ALTER TABLE app_group_review DROP FOREIGN KEY FK_684B4397A76ED395');
        $this->addSql('ALTER TABLE movie_genres DROP FOREIGN KEY FK_512C78638F93B6FC');
        $this->addSql('ALTER TABLE movie_genres DROP FOREIGN KEY FK_512C78634296D31F');
        $this->addSql('DROP TABLE app_group_member');
        $this->addSql('DROP TABLE app_group_recommendation');
        $this->addSql('DROP TABLE app_group_review');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_genres');
    }
}
