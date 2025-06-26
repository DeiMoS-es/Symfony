<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624103804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1D5EF26F55BCC5E5 ON movie (tmdb_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD nombre VARCHAR(255) DEFAULT NULL, ADD apellidos VARCHAR(255) DEFAULT NULL, ADD user_name VARCHAR(255) DEFAULT NULL, ADD img_usuario VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1D5EF26F55BCC5E5 ON movie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP nombre, DROP apellidos, DROP user_name, DROP img_usuario
        SQL);
    }
}
