<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614132751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delayed_order (id INT AUTO_INCREMENT NOT NULL, order_parent_id INT NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expected_delivery DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6005EBE3CEFDB188 (order_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE delayed_order ADD CONSTRAINT FK_6005EBE3CEFDB188 FOREIGN KEY (order_parent_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE delayed_order');
    }
}
