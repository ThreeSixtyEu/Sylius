<?php

namespace Funlife\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150828141113 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_product_payment_constraints_relations (product_id INT NOT NULL, payment_constraint_id INT NOT NULL, INDEX IDX_643E933F4584665A (product_id), UNIQUE INDEX UNIQ_643E933F1E0C7631 (payment_constraint_id), PRIMARY KEY(product_id, payment_constraint_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_product_payment_constraint (id INT AUTO_INCREMENT NOT NULL, payment_method_id INT NOT NULL, allowed_since DATETIME NOT NULL, allowed_until DATETIME NOT NULL, INDEX IDX_554582515AA1164F (payment_method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_product_payment_constraints_relations ADD CONSTRAINT FK_643E933F4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_payment_constraints_relations ADD CONSTRAINT FK_643E933F1E0C7631 FOREIGN KEY (payment_constraint_id) REFERENCES sylius_product_payment_constraint (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_payment_constraint ADD CONSTRAINT FK_554582515AA1164F FOREIGN KEY (payment_method_id) REFERENCES sylius_payment_method (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_product_payment_constraints_relations DROP FOREIGN KEY FK_643E933F1E0C7631');
        $this->addSql('DROP TABLE sylius_product_payment_constraints_relations');
        $this->addSql('DROP TABLE sylius_product_payment_constraint');
    }
}
