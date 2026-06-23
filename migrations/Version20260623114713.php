<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623114713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, logo_filename VARCHAR(255) NOT NULL, website_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE birthday_message CHANGE visible visible TINYINT NOT NULL');
        $this->addSql('ALTER TABLE donation CHANGE visible visible TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sponsor');
        $this->addSql('ALTER TABLE birthday_message CHANGE visible visible TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE donation CHANGE visible visible TINYINT DEFAULT 1 NOT NULL');
    }
}
