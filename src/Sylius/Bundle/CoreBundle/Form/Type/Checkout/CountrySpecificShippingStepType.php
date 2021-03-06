<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Form\Type\Checkout;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Addressing\Model\Country;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Model\ZoneMemberCountry;
use Sylius\Component\Core\Model\ShippingMethod;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Checkout shipping step form type.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class CountrySpecificShippingStepType extends AbstractResourceType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('country', 'sylius_country_choice', array(
			'label' => 'sylius.form.address.country',
			'mapped' => false,
			'data' => $options['country'],
		));

		$builder->add('shipments', 'collection', array(
			'type'    => 'sylius_checkout_shipment',
			'options' => array('criteria' => $options['criteria']),
		));

		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
			$form = $event->getForm();

			$form->add('country', 'sylius_country_choice', array(
				'label' => 'sylius.form.address.country',
				'empty_value' => ($form->get('country')->getData() || $options['country']) ? false : 'sylius.form.address.country.empty',
				'mapped' => false,
				'data' => $options['country'],
			));

			/** @var ChoiceListInterface|null $choiceList */
			$choiceList = $form->get('country')->getConfig()->getOption('choice_list');
			if ($choiceList !== null) {
				$choices = array();

				/** @var EntityRepository $repository */
				$repository = $options['zone_repository'];
				/** @var ZoneInterface[] $zones */
				$zones = $repository->findAll();

				/** @var Country $country */
				foreach ($choiceList->getChoices() as $country) {
					foreach ($zones as $zone) {
						foreach ($zone->getMembers() as $member) {
							if ($member instanceof ZoneMemberCountry && $country === $member->getCountry() && $options['country'] === $country) {
								$choices[] = $member->getId();
							}
						}
					}
				}
				if (empty($choices)) {
					$options['criteria'] = array('id' => null);
				} else {
					$options['criteria']['zone'] = $choices;
				}
			}

			$form->add('shipments', 'collection', array(
				'type'    => 'sylius_checkout_shipment',
				'options' => array('criteria' => $options['criteria']),
			));
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);

		$resolver
			->setOptional(array(
				'criteria',
			))
			->setRequired(array(
				'zone_repository',
				'country',
			))
			->setAllowedTypes(array(
				'criteria' => array('array')
			))
		;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$methodType = $form->get('shipments')->get(0)->get('method');
		/** @var ChoiceList $choiceList */
		$choiceList = $methodType->getConfig()->getOption('choice_list');

		$requireAddress = array();
		$generateTickets = array();
		/** @var ShippingMethod $shippingMethod */
		$i = 0;
		foreach ($choiceList->getChoices() as $shippingMethod) {
			if ($shippingMethod->getRequireAddress()) {
				$requireAddress[] = $i;
			}
			if ($shippingMethod->getCategory()->isGenerateTickets()) {
				$generateTickets[] = $i;
			}
			$i++;
		}

		$view->vars['shipping_methods_requiring_address'] = json_encode(array('ids' => $requireAddress));
		$view->vars['shipping_methods_generates_eticket'] = json_encode(array('ids' => $generateTickets));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'sylius_checkout_shipping_country_specific';
	}
}
