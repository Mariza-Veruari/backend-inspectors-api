<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250115000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create auditor, job, and job_assignment tables';
    }

    public function up(Schema $schema): void
    {
        // Create auditor table
        $this->addSql('CREATE TABLE auditor (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            origin_timezone VARCHAR(64) NOT NULL,
            created_at DATETIME NOT NULL
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DAD38EFE7927C74 ON auditor (email)');

        // Create job table
        $this->addSql('CREATE TABLE job (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description CLOB DEFAULT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL
        )');

        // Create job_assignment table
        $this->addSql('CREATE TABLE job_assignment (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            job_id INTEGER NOT NULL,
            auditor_id INTEGER NOT NULL,
            scheduled_at_utc DATETIME NOT NULL,
            completed_at_utc DATETIME DEFAULT NULL,
            assessment CLOB DEFAULT NULL,
            created_at DATETIME NOT NULL
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_JOB_ASSIGNMENT_JOB ON job_assignment (job_id)');
        $this->addSql('CREATE INDEX IDX_JOB_ASSIGNMENT_AUDITOR ON job_assignment (auditor_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE job_assignment');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE auditor');
    }
}
