<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120042237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD bank_name VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD bank_bik VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD city VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD email VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD inn VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD correspondent_account VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD checking_account VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD region VARCHAR(60) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD organisation_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD juridical_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD juridical_entity_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP bank_name');
        $this->addSql('ALTER TABLE "user" DROP bank_bik');
        $this->addSql('ALTER TABLE "user" DROP city');
        $this->addSql('ALTER TABLE "user" DROP email');
        $this->addSql('ALTER TABLE "user" DROP inn');
        $this->addSql('ALTER TABLE "user" DROP correspondent_account');
        $this->addSql('ALTER TABLE "user" DROP checking_account');
        $this->addSql('ALTER TABLE "user" DROP region');
        $this->addSql('ALTER TABLE "user" DROP organisation_type');
        $this->addSql('ALTER TABLE "user" DROP juridical_address');
        $this->addSql('ALTER TABLE "user" DROP juridical_entity_type');
    }
}
