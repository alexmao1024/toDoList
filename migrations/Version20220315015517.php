<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220315015517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE task_list_work_space');
        $this->addSql('ALTER TABLE task_list ADD workspace_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C6382D40A1F FOREIGN KEY (workspace_id) REFERENCES work_space (id)');
        $this->addSql('CREATE INDEX IDX_377B6C6382D40A1F ON task_list (workspace_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_list_work_space (task_list_id INT NOT NULL, work_space_id INT NOT NULL, INDEX IDX_9DD159C6F6E2D91C (work_space_id), INDEX IDX_9DD159C6224F3C61 (task_list_id), PRIMARY KEY(task_list_id, work_space_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE task_list_work_space ADD CONSTRAINT FK_9DD159C6F6E2D91C FOREIGN KEY (work_space_id) REFERENCES work_space (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_list_work_space ADD CONSTRAINT FK_9DD159C6224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE task_list DROP FOREIGN KEY FK_377B6C6382D40A1F');
        $this->addSql('DROP INDEX IDX_377B6C6382D40A1F ON task_list');
        $this->addSql('ALTER TABLE task_list DROP workspace_id, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `user` CHANGE name name VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE token token VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE work_space CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
