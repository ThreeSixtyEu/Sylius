<?php

namespace Sylius\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151215154610 extends AbstractMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if (null !== $container) {
            $this->container = $container;
            $this->defaultLocale = $container->getParameter('sylius.locale');
        }
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('CREATE TABLE sylius_product_variant_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, presentation VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_8DC18EDC2C2AC5D3 (translatable_id), UNIQUE INDEX sylius_product_variant_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $variants = $this->connection->fetchAll('SELECT * FROM sylius_product_variant');
        foreach ($variants as $variant) {
            $this->connection->insert('sylius_product_variant_translation', array(
                'presentation' => $variant['presentation'],
                'translatable_id' => $variant['id'],
                'locale' => $this->defaultLocale
            ));
        }

        $this->addSql('ALTER TABLE sylius_product_variant_translation ADD CONSTRAINT FK_8DC18EDC2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_product_variant DROP presentation');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->connection->executeQuery('ALTER TABLE sylius_product_variant ADD presentation VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $variantTranslations = $this->connection->fetchAll('SELECT * FROM sylius_product_variant_translation WHERE locale="' . $this->defaultLocale . '"');;
        foreach ($variantTranslations as $variantTranslation) {
            $this->connection->update(
                'sylius_product_variant',
                array('presentation' => $variantTranslation['presentation']),
                array('id' => $variantTranslation['translatable_id'])
            );
        }

        $this->addSql('DROP TABLE sylius_product_variant_translation');
    }
}
