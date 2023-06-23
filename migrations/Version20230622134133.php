<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230622134133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, issuer_id INT NOT NULL, target_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3B978F9FBB9D6FEE (issuer_id), UNIQUE INDEX UNIQ_3B978F9F158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9FBB9D6FEE FOREIGN KEY (issuer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9F158E0B66 FOREIGN KEY (target_id) REFERENCES post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9FBB9D6FEE');
        $this->addSql('ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F158E0B66');
        $this->addSql('DROP TABLE request');
    }
}
