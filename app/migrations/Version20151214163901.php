<?php

namespace Sylius\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151214163901 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_shipping_category_product (product_id INT NOT NULL, shipping_category_id INT NOT NULL, INDEX IDX_7E1A8FBE4584665A (product_id), INDEX IDX_7E1A8FBE4F6AB213 (shipping_category_id), PRIMARY KEY(product_id, shipping_category_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_shipping_category_product ADD CONSTRAINT FK_7E1A8FBE4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE sylius_shipping_category_product ADD CONSTRAINT FK_7E1A8FBE4F6AB213 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
        $this->addSql('ALTER TABLE sylius_product DROP FOREIGN KEY FK_677B9B749E2D1A41');
        $this->addSql('INSERT INTO sylius_shipping_category_product (product_id, shipping_category_id) SELECT p.id, p.shipping_category_id FROM sylius_product p');
        $this->addSql('DROP INDEX IDX_677B9B749E2D1A41 ON sylius_product');
        $this->addSql('ALTER TABLE sylius_product DROP shipping_category_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product ADD shipping_category_id INT DEFAULT NULL');
        $this->addSql('UPDATE sylius_product p INNER JOIN sylius_shipping_category_product scp ON scp.product_id = p.id SET p.shipping_category_id = scp.shipping_category_id');
        $this->addSql('DROP TABLE sylius_shipping_category_product');
        $this->addSql('ALTER TABLE sylius_product ADD CONSTRAINT FK_677B9B749E2D1A41 FOREIGN KEY (shipping_category_id) REFERENCES sylius_shipping_category (id)');
        $this->addSql('CREATE INDEX IDX_677B9B749E2D1A41 ON sylius_product (shipping_category_id)');
    }
}
