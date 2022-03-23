<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220314014329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_list_work_space (task_list_id INT NOT NULL, work_space_id INT NOT NULL, INDEX IDX_9DD159C6224F3C61 (task_list_id), INDEX IDX_9DD159C6F6E2D91C (work_space_id), PRIMARY KEY(task_list_id, work_space_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_work_space (user_id INT NOT NULL, work_space_id INT NOT NULL, INDEX IDX_A6EE7CF9A76ED395 (user_id), INDEX IDX_A6EE7CF9F6E2D91C (work_space_id), PRIMARY KEY(user_id, work_space_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE work_space (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_list_work_space ADD CONSTRAINT FK_9DD159C6224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_list_work_space ADD CONSTRAINT FK_9DD159C6F6E2D91C FOREIGN KEY (work_space_id) REFERENCES work_space (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_work_space ADD CONSTRAINT FK_A6EE7CF9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_work_space ADD CONSTRAINT FK_A6EE7CF9F6E2D91C FOREIGN KEY (work_space_id) REFERENCES work_space (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE task_list_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_list_work_space DROP FOREIGN KEY FK_9DD159C6F6E2D91C');
        $this->addSql('ALTER TABLE user_work_space DROP FOREIGN KEY FK_A6EE7CF9F6E2D91C');
        $this->addSql('CREATE TABLE task_list_user (task_list_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B777C4F1A76ED395 (user_id), INDEX IDX_B777C4F1224F3C61 (task_list_id), PRIMARY KEY(task_list_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE task_list_user ADD CONSTRAINT FK_B777C4F1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_list_user ADD CONSTRAINT FK_B777C4F1224F3C61 FOREIGN KEY (task_list_id) REFERENCES task_list (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE task_list_work_space');
        $this->addSql('DROP TABLE user_work_space');
        $this->addSql('DROP TABLE work_space');
        $this->addSql('ALTER TABLE task CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE task_list CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE `user` CHANGE name name VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE token token VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
