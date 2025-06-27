<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627083301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie DROP FOREIGN KEY FK_FF9C09378F93B6FC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie DROP FOREIGN KEY FK_FF9C0937A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie ADD added_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD rating INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie ADD CONSTRAINT FK_FF9C09378F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie ADD CONSTRAINT FK_FF9C0937A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie DROP FOREIGN KEY FK_FF9C0937A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie DROP FOREIGN KEY FK_FF9C09378F93B6FC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie DROP added_at, DROP rating
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie ADD CONSTRAINT FK_FF9C0937A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_movie ADD CONSTRAINT FK_FF9C09378F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
    }
}
