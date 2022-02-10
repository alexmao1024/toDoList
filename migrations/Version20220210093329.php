<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210093329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_377B6C63A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_list ADD CONSTRAINT FK_377B6C63A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25DF0FD358');
        $this->addSql('DROP INDEX IDX_527EDB25DF0FD358 ON task');
        $this->addSql('ALTER TABLE task ADD list_id INT NOT NULL, DROP userr_id, DROP type');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB253DAE168B FOREIGN KEY (list_id) REFERENCES task_list (id)');
        $this->addSql('CREATE INDEX IDX_527EDB253DAE168B ON task (list_id)');
        $this->addSql('ALTER TABLE user CHANGE username name VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB253DAE168B');
        $this->addSql('DROP TABLE task_list');
        $this->addSql('DROP INDEX IDX_527EDB253DAE168B ON task');
        $this->addSql('ALTER TABLE task ADD userr_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP list_id, CHANGE name name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE context context LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25DF0FD358 FOREIGN KEY (userr_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25DF0FD358 ON task (userr_id)');
        $this->addSql('ALTER TABLE `user` ADD username VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP name');
    }
}
