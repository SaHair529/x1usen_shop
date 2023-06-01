<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230601000115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE order_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE order_comment (id INT NOT NULL, parent_order_id INT NOT NULL, sender_id INT NOT NULL, text TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_620EFB271252C1E9 ON order_comment (parent_order_id)');
        $this->addSql('CREATE INDEX IDX_620EFB27F624B39D ON order_comment (sender_id)');
        $this->addSql('ALTER TABLE order_comment ADD CONSTRAINT FK_620EFB271252C1E9 FOREIGN KEY (parent_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_comment ADD CONSTRAINT FK_620EFB27F624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE order_comment_id_seq CASCADE');
        $this->addSql('ALTER TABLE order_comment DROP CONSTRAINT FK_620EFB271252C1E9');
        $this->addSql('ALTER TABLE order_comment DROP CONSTRAINT FK_620EFB27F624B39D');
        $this->addSql('DROP TABLE order_comment');
    }
}
