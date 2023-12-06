<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231123092052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE abcp_order_custom_fields_entity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE abcp_order_custom_fields_entity (id INT NOT NULL, alfabank_order_id VARCHAR(255) DEFAULT NULL, alfabank_payment_url VARCHAR(255) DEFAULT NULL, abcp_order_number INT DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE abcp_order_custom_fields_entity_id_seq CASCADE');
        $this->addSql('DROP TABLE abcp_order_custom_fields_entity');
    }
}
