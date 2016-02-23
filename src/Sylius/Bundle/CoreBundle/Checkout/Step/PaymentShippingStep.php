<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Checkout\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Core\Model\UserInterface;
use Sylius\Component\Core\SyliusCheckoutEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * The addressing step of checkout.
 * User enters the shipping and shipping address.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PaymentShippingStep extends CheckoutStep
{

	protected $zones;

	/**
	 * {@inheritdoc}
	 */
	public function displayAction(ProcessContextInterface $context)
	{
		return $this->forwardAction($context);
	}

	/**
	 * {@inheritdoc}
	 */
	public function forwardAction(ProcessContextInterface $context)
	{
		$request = $this->getRequest();

		$order = $this->getCurrentCart();
		$this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_SHIPPING_INITIALIZE, $order);

		$this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_INITIALIZE, $order);
		$formPayment = $this->createCheckoutPaymentForm($order);
		$formPayment->handleRequest($request);

		$this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_INITIALIZE, $order);
		$formShippingPre = $this->createCheckoutShippingForm($order, null);
		$formShippingPre->handleRequest($request);

		if ($formShippingPre->get('country')->getData() === null) {
			$choices = $formShippingPre->get('country')->getConfig()->getOption('choice_list')->getChoices();
			$formShippingPre->get('country')->setData(reset($choices));
		}

		$this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_INITIALIZE, $order);
		$formShipping = $this->createCheckoutShippingForm($order, $formShippingPre->get('country')->getData());
		$formShipping->handleRequest($request);

		$this->dispatchCheckoutEvent(SyliusCheckoutEvents::ADDRESSING_INITIALIZE, $order);
		if (is_null($order->getShippingAddress()) || $order->getShippingAddress()->getFirstName() == 'anon.') {
			$order->setShippingAddress(new Address());
		}
		$formAddressing = $this->createCheckoutAddressingForm($order, $this->getUser());
		$country = $formShippingPre->get('country')->getData();
		$formAddressing->get('shippingAddress')->get('country')->setData($country);

		$requestData = $request->get($formAddressing->getName());
		$requestData['shippingAddress']['country'] = $country->getId();
		$request->request->set($formAddressing->getName(), $requestData);

		if ($this->requireAddress($order)) {
			$formAddressing->handleRequest($request);
		}

		$this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_INITIALIZE, $order);
		$formShipping = $this->createCheckoutShippingForm($order, $formShippingPre->get('country')->getData());
		$formShipping->handleRequest($request);

		if ($formPayment->isSubmitted() && $formShipping->isSubmitted()) {
			// custom validation for pandaticket cashOnDelivery and eTicket combination
			// TODO remove when relations between payment and delivery will be available
			if ($order->getLastPayment()->getMethod()->getGateway() == 'cashOnDelivery' && $order->getLastShipment()->getMethod()->getCategory()->isGenerateTickets()) {
				$formPayment->get('paymentMethod')->addError(new FormError('funlife.eshop.form.payment.cash_on_delivery'));
			}
		}

		if ($formPayment->isValid() && $formShipping->isValid() && (!$this->requireAddress($order) || $formAddressing->isValid())) {
			$compareAddr = new Address();

			if (!$this->requireAddress($order) && $order->getShippingAddress() == $compareAddr) {
				$address = new Address();
				$address->setFirstName('anon.');
				$address->setLastName('anon.');
				$address->setStreet('anon.');
				$address->setCity('anon.');
				$address->setPostcode('anon.');

				$country = $formShipping->get('country')->getData();
				$address->setCountry($country);

				$order->setShippingAddress($address);
				$order->setBillingAddress(clone $address);
			}

			$this->dispatchCheckoutEvent(SyliusCheckoutEvents::ADDRESSING_PRE_COMPLETE, $order);
			$this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_PRE_COMPLETE, $order);
			$this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_PRE_COMPLETE, $order);

			$this->getManager()->persist($order);
			$this->getManager()->flush();

			$this->dispatchCheckoutEvent(SyliusCheckoutEvents::ADDRESSING_COMPLETE, $order);
			$this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_COMPLETE, $order);
			$this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_COMPLETE, $order);

			// do not forward ajax form for country change
			if ($request->get('doNotForward') !== 'true') {
				return $this->complete();
			}
		}

		return $this->renderStep($context, $order, $formPayment, $formShipping, $formAddressing);
	}

	protected function renderStep(ProcessContextInterface $context, OrderInterface $order, FormInterface $formPayment, FormInterface $formShipping, FormInterface $formAddressing = null)
	{
		return $this->render($this->container->getParameter(sprintf('sylius.checkout.step.%s.template', $this->getName())), array(
			'order' => $order,
			'formPayment' => $formPayment->createView(),
			'formShipping' => $formShipping->createView(),
			'formAddressing' => $formAddressing ? $formAddressing->createView() : $formAddressing,
			'requireAddress' => $this->requireAddress($order),
			'context' => $context
		));
	}

	protected function createCheckoutAddressingForm(OrderInterface $order, UserInterface $user = null)
	{
		return $this->createForm('sylius_checkout_addressing', $order, array('user' => $user));
	}

	protected function createCheckoutShippingForm(OrderInterface $order, $country)
	{
		$options = array(
			'criteria' => array(
				'zone' => null,
				'enabled' => true,
			),
			'zone_repository' => $this->get('sylius.repository.zone'),
			'country' => $country,
		);

		if ($order->getShippingAddress()) {
			$this->zones = $this->getZoneMatcher()->matchAll($order->getShippingAddress());
		}

		if (!empty($this->zones)) {
			$options['criteria']['zone'] = array_map(function ($zone) {
				return $zone->getId();
			}, $this->zones);
		}

		return $this->createForm('sylius_checkout_shipping_country_specific', $order, $options);
	}

	protected function createCheckoutPaymentForm(OrderInterface $order)
	{
		return $this->createForm('sylius_checkout_payment', $order, array('order_items' => $order->getItems()));
	}

	protected function requireAddress(OrderInterface $order)
	{
		return $order->getShipments()->exists(function ($key, ShipmentInterface $shipment) {
			/** @var ShippingMethod $shippingMethod */
			$shippingMethod = $shipment->getMethod();

			if ($shippingMethod === null) {
				return false;
			}

			return $shippingMethod->getRequireAddress();
		});
	}
}
