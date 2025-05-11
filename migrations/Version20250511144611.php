<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511144611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_7cc7da2c989d9b62
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video ADD uuid VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video DROP slug
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video DROP filename
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video DROP thumbnail
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_7CC7DA2CD17F50A6 ON video (uuid)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_7CC7DA2CD17F50A6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video ADD filename VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video ADD thumbnail VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video RENAME COLUMN uuid TO slug
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_7cc7da2c989d9b62 ON video (slug)
        SQL);
    }
}
