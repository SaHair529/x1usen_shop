<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231017225357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dellin_place ALTER code TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE dellin_place ALTER search_string TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE dellin_place ALTER region TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE dellin_place ALTER region_code TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE dellin_place ALTER zone_name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE dellin_place ALTER zone_code TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dellin_place ALTER code TYPE VARCHAR(60)');
        $this->addSql('ALTER TABLE dellin_place ALTER search_string TYPE VARCHAR(60)');
        $this->addSql('ALTER TABLE dellin_place ALTER region TYPE VARCHAR(40)');
        $this->addSql('ALTER TABLE dellin_place ALTER region_code TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE dellin_place ALTER zone_name TYPE VARCHAR(40)');
        $this->addSql('ALTER TABLE dellin_place ALTER zone_code TYPE VARCHAR(30)');
    }
}
