<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210222190559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_type_product (product_id INT NOT NULL, type_product_id INT NOT NULL, INDEX IDX_76CF49334584665A (product_id), INDEX IDX_76CF49335887B07F (type_product_id), PRIMARY KEY(product_id, type_product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_content (id INT AUTO_INCREMENT NOT NULL, type_product_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D6E45F5C5887B07F (type_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_type_product ADD CONSTRAINT FK_76CF49334584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_type_product ADD CONSTRAINT FK_76CF49335887B07F FOREIGN KEY (type_product_id) REFERENCES type_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_content ADD CONSTRAINT FK_D6E45F5C5887B07F FOREIGN KEY (type_product_id) REFERENCES type_product (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_type_product DROP FOREIGN KEY FK_76CF49335887B07F');
        $this->addSql('ALTER TABLE type_content DROP FOREIGN KEY FK_D6E45F5C5887B07F');
        $this->addSql('DROP TABLE product_type_product');
        $this->addSql('DROP TABLE type_content');
        $this->addSql('DROP TABLE type_product');
    }
}
