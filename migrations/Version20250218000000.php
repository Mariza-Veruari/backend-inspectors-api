<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250218000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create inspector, job, assignment tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE inspector (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(180) NOT NULL, timezone VARCHAR(50) NOT NULL)');
        $this->addSql('CREATE TABLE job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL DEFAULT \'open\')');
        $this->addSql('CREATE TABLE assignment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, job_id INTEGER NOT NULL, inspector_id INTEGER NOT NULL, scheduled_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL DEFAULT \'scheduled\', assessment CLOB DEFAULT NULL, completed_at DATETIME DEFAULT NULL, CONSTRAINT FK_30C544BA8BE04FD9 FOREIGN KEY (job_id) REFERENCES job (id), CONSTRAINT FK_30C544BA41E0B2E4 FOREIGN KEY (inspector_id) REFERENCES inspector (id))');
        $this->addSql('CREATE INDEX IDX_30C544BA8BE04FD9 ON assignment (job_id)');
        $this->addSql('CREATE INDEX IDX_30C544BA41E0B2E4 ON assignment (inspector_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE inspector');
        $this->addSql('DROP TABLE job');
    }
}
