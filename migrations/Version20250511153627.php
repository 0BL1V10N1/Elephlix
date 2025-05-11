<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511153627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE video_reaction (id SERIAL NOT NULL, video_id INT NOT NULL, reactor_id INT NOT NULL, type INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C55354B29C1004E ON video_reaction (video_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C55354B723AD41B ON video_reaction (reactor_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_reaction ADD CONSTRAINT FK_C55354B29C1004E FOREIGN KEY (video_id) REFERENCES video (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_reaction ADD CONSTRAINT FK_C55354B723AD41B FOREIGN KEY (reactor_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_reaction DROP CONSTRAINT FK_C55354B29C1004E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE video_reaction DROP CONSTRAINT FK_C55354B723AD41B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE video_reaction
        SQL);
    }
}
