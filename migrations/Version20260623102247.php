<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260623102247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE birthday_message (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(80) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE donation (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, donor_name VARCHAR(120) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(40) DEFAULT NULL, anonymous TINYINT NOT NULL, method VARCHAR(20) DEFAULT NULL, status VARCHAR(20) NOT NULL, fedapay_transaction_id VARCHAR(60) DEFAULT NULL, raw_customer LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, confirmed_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_31E581A0B0D894ED (fedapay_transaction_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE birthday_message');
        $this->addSql('DROP TABLE donation');
    }
}
