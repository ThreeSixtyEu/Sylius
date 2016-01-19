<?php

namespace Sylius\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160119162159 extends AbstractMigration implements ContainerAwareInterface
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

        $this->connection->executeQuery('CREATE TABLE sylius_payment_method_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_966BE3A12C2AC5D3 (translatable_id), UNIQUE INDEX sylius_payment_method_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
	    $this->connection->executeQuery('ALTER TABLE sylius_payment_method_translation ADD CONSTRAINT FK_966BE3A12C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES sylius_payment_method (id) ON DELETE CASCADE');

	    $paymentMethods = $this->connection->fetchAll('SELECT * FROM sylius_payment_method');
        foreach ($paymentMethods as $paymentMethod) {
            $this->connection->insert('sylius_payment_method_translation', array(
	            'name' => $paymentMethod['name'],
                'description' => $paymentMethod['description'],
	            'translatable_id' => $paymentMethod['id'],
	            'locale' => $this->defaultLocale,
            ));
        }

        $this->addSql('ALTER TABLE sylius_payment_method DROP name, DROP description');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

	    $this->connection->executeQuery('ALTER TABLE sylius_payment_method ADD name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, ADD description VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');

	    $paymentMethodTranslations = $this->connection->fetchAll('SELECT * FROM sylius_payment_method_translation WHERE locale = :locale', array('locale' => $this->defaultLocale));
	    foreach ($paymentMethodTranslations as $paymentMethodTranslation) {
		    $this->connection->update('sylius_payment_method', array(
			    'name' => $paymentMethodTranslation['name'],
			    'description' => $paymentMethodTranslation['description'],
		    ), array(
			    'id' => $paymentMethodTranslation['translatable_id'],
		    ));
	    }

	    $this->addSql('DROP TABLE sylius_payment_method_translation');
    }
}
