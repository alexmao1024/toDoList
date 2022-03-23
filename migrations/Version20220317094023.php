<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220317094023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list DROP FOREIGN KEY FK_377B6C6382D40A1F');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C6382D40A1F FOREIGN KEY (workspace_id) REFERENCES work_space (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE work_space DROP FOREIGN KEY FK_189BA15632BA02B');
        $this->addSql('ALTER TABLE work_space ADD CONSTRAINT FK_189BA157E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE task_list DROP FOREIGN KEY FK_377B6C6382D40A1F');
        $this->addSql('ALTER TABLE task_list CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C6382D40A1F FOREIGN KEY (workspace_id) REFERENCES work_space (id)');
        $this->addSql('ALTER TABLE `user` CHANGE name name VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE token token VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE work_space DROP FOREIGN KEY FK_189BA157E3C61F9');
        $this->addSql('ALTER TABLE work_space CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE work_space ADD CONSTRAINT FK_189BA15632BA02B FOREIGN KEY (owner_id) REFERENCES user (id)');
    }
}
