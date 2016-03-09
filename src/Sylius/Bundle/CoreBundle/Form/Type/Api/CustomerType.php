<?php

namespace Sylius\Bundle\CoreBundle\Form\Type\Api;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CustomerType
 * @package Sylius\Bundle\CoreBundle\Form\Type\Api
 */
class CustomerType extends AbstractResourceType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('firstName', 'text', array(
				'constraints' => array(
					new NotBlank(),
				),
			))
			->add('lastName', 'text', array(
				'constraints' => array(
					new NotBlank(),
				),
			))
			->add('email', 'email', array(
				'constraints' => array(
					new NotBlank(),
					new Email(),
				),
			))
			->add('plainPassword', 'text', array(
				'constraints' => array(
					new NotBlank(),
				),
			))
			->add('enabled', 'checkbox')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'sylius_api_customer';
	}
}
