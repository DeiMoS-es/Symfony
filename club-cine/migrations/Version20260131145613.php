<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260131145613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_group (id CHAR(36) NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, owner_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_BB13C9085E237E06 (name), UNIQUE INDEX UNIQ_BB13C908989D9B62 (slug), INDEX IDX_BB13C9087E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_group_member (id CHAR(36) NOT NULL, role VARCHAR(20) NOT NULL, joined_at DATETIME NOT NULL, group_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_61EC3829FE54D947 (group_id), INDEX IDX_61EC3829A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_group_recommendation (id CHAR(36) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, deadline DATETIME NOT NULL, average_score DOUBLE PRECISION DEFAULT NULL, total_votes INT DEFAULT NULL, stats JSON DEFAULT NULL, group_id CHAR(36) NOT NULL, movie_id CHAR(36) NOT NULL, recommended_by_id CHAR(36) NOT NULL, INDEX IDX_30EF6577FE54D947 (group_id), INDEX IDX_30EF65778F93B6FC (movie_id), INDEX IDX_30EF6577D59A4918 (recommended_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_group_review (id CHAR(36) NOT NULL, score_script INT NOT NULL, score_main_actor INT NOT NULL, score_main_actress INT NOT NULL, score_secondary_actors INT NOT NULL, score_director INT NOT NULL, average_score DOUBLE PRECISION NOT NULL, comment VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, recommendation_id CHAR(36) NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_684B4397D173940B (recommendation_id), INDEX IDX_684B4397A76ED395 (user_id), UNIQUE INDEX unique_user_recommendation (recommendation_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_user (id CHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_88BDF3E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_groups (user_id CHAR(36) NOT NULL, group_id CHAR(36) NOT NULL, INDEX IDX_953F224DA76ED395 (user_id), INDEX IDX_953F224DFE54D947 (group_id), PRIMARY KEY (user_id, group_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_835033F85E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE group_invitations (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, token VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, target_group_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_69F0F6F5F37A13B (token), INDEX IDX_69F0F6F24FF092E (target_group_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movie (id CHAR(36) NOT NULL, tmdb_id INT NOT NULL, title VARCHAR(255) NOT NULL, original_title VARCHAR(255) DEFAULT NULL, original_language VARCHAR(10) DEFAULT NULL, overview LONGTEXT DEFAULT NULL, poster_path VARCHAR(255) DEFAULT NULL, backdrop_path VARCHAR(255) DEFAULT NULL, release_date DATE DEFAULT NULL, runtime INT DEFAULT NULL, adult TINYINT(1) NOT NULL, popularity DOUBLE PRECISION DEFAULT NULL, vote_average DOUBLE PRECISION DEFAULT NULL, vote_count INT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1D5EF26F55BCC5E5 (tmdb_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE movie_genres (movie_id CHAR(36) NOT NULL, genre_id INT NOT NULL, INDEX IDX_512C78638F93B6FC (movie_id), INDEX IDX_512C78634296D31F (genre_id), PRIMARY KEY (movie_id, genre_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, token_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, user_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1D17F50A6 (uuid), INDEX IDX_9BACE7E1A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sessions (sess_id VARBINARY(128) NOT NULL, sess_data LONGBLOB NOT NULL, sess_lifetime INT UNSIGNED NOT NULL, sess_time INT UNSIGNED NOT NULL, INDEX sess_lifetime_idx (sess_lifetime), PRIMARY KEY (sess_id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_group ADD CONSTRAINT FK_BB13C9087E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_group_member ADD CONSTRAINT FK_61EC3829FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_member ADD CONSTRAINT FK_61EC3829A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF6577FE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF65778F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE app_group_recommendation ADD CONSTRAINT FK_30EF6577D59A4918 FOREIGN KEY (recommended_by_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE app_group_review ADD CONSTRAINT FK_684B4397D173940B FOREIGN KEY (recommendation_id) REFERENCES app_group_recommendation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_group_review ADD CONSTRAINT FK_684B4397A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE user_groups ADD CONSTRAINT FK_953F224DA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_groups ADD CONSTRAINT FK_953F224DFE54D947 FOREIGN KEY (group_id) REFERENCES app_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_invitations ADD CONSTRAINT FK_69F0F6F24FF092E FOREIGN KEY (target_group_id) REFERENCES app_group (id)');
        $this->addSql('ALTER TABLE movie_genres ADD CONSTRAINT FK_512C78638F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_genres ADD CONSTRAINT FK_512C78634296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT FK_9BACE7E1A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_group DROP FOREIGN KEY FK_BB13C9087E3C61F9');
        $this->addSql('ALTER TABLE app_group_member DROP FOREIGN KEY FK_61EC3829FE54D947');
        $this->addSql('ALTER TABLE app_group_member DROP FOREIGN KEY FK_61EC3829A76ED395');
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF6577FE54D947');
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF65778F93B6FC');
        $this->addSql('ALTER TABLE app_group_recommendation DROP FOREIGN KEY FK_30EF6577D59A4918');
        $this->addSql('ALTER TABLE app_group_review DROP FOREIGN KEY FK_684B4397D173940B');
        $this->addSql('ALTER TABLE app_group_review DROP FOREIGN KEY FK_684B4397A76ED395');
        $this->addSql('ALTER TABLE user_groups DROP FOREIGN KEY FK_953F224DA76ED395');
        $this->addSql('ALTER TABLE user_groups DROP FOREIGN KEY FK_953F224DFE54D947');
        $this->addSql('ALTER TABLE group_invitations DROP FOREIGN KEY FK_69F0F6F24FF092E');
        $this->addSql('ALTER TABLE movie_genres DROP FOREIGN KEY FK_512C78638F93B6FC');
        $this->addSql('ALTER TABLE movie_genres DROP FOREIGN KEY FK_512C78634296D31F');
        $this->addSql('ALTER TABLE refresh_tokens DROP FOREIGN KEY FK_9BACE7E1A76ED395');
        $this->addSql('DROP TABLE app_group');
        $this->addSql('DROP TABLE app_group_member');
        $this->addSql('DROP TABLE app_group_recommendation');
        $this->addSql('DROP TABLE app_group_review');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE user_groups');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE group_invitations');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_genres');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE sessions');
    }
}
