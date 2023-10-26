<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010115038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE gmail_access_token_id_seq CASCADE');
        $this->addSql('DROP TABLE gmail_access_token');
        $this->addSql('TRUNCATE TABLE "order" CASCADE');
        $this->addSql('ALTER TABLE "order" ADD payment_status INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE gmail_access_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE gmail_access_token (id INT NOT NULL, access_token TEXT NOT NULL, expires_in INT NOT NULL, scope VARCHAR(255) NOT NULL, token_type VARCHAR(30) NOT NULL, created INT NOT NULL, refresh_token TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE "order" DROP payment_status');
    }
}
