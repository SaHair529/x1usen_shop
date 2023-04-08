<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408135729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE "order_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "order" (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phone_number VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "order".created_at IS \'(DC2Type:datetime_immutable)\'', [':datetime_immutable' => '']);
        $this->addSql('ALTER TABLE cart_item ADD parent_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271252C1E9 FOREIGN KEY (parent_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F0FE25271252C1E9 ON cart_item (parent_order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cart_item DROP CONSTRAINT FK_F0FE25271252C1E9');
        $this->addSql('DROP SEQUENCE "order_id_seq" CASCADE');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP INDEX IDX_F0FE25271252C1E9');
        $this->addSql('ALTER TABLE cart_item DROP parent_order_id');
    }
}
