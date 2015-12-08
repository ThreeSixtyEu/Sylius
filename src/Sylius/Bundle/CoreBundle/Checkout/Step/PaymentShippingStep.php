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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\UserInterface;
use Sylius\Component\Core\SyliusCheckoutEvents;
use Symfony\Component\Form\FormInterface;

/**
 * The addressing step of checkout.
 * User enters the shipping and shipping address.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PaymentShippingStep extends CheckoutStep
{
    /**
     * @var null|ZoneInterface
     */
    private $zones;

    /**
     * {@inheritdoc}
     */
    public function displayAction(ProcessContextInterface $context)
    {
        $order = $this->getCurrentCart();
        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_SHIPPING_INITIALIZE, $order);

//        if (empty($this->zones)) {
//            return $this->proceed($context->getPreviousStep()->getName());
//        }

        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::ADDRESSING_INITIALIZE, $order);
        $formAddressing = $this->createCheckoutAddressingForm($order, $this->getUser());

        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_INITIALIZE, $order);
        $formPayment = $this->createCheckoutPaymentForm($order);

        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_INITIALIZE, $order);
        $formShipping = $this->createCheckoutShippingForm($order);

        return $this->renderStep($context, $order, $formAddressing, $formShipping,$formPayment);
    }

    /**
     * {@inheritdoc}
     */
    public function forwardAction(ProcessContextInterface $context)
    {
        $request = $this->getRequest();

        $order = $this->getCurrentCart();
        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_SHIPPING_INITIALIZE, $order);

        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::ADDRESSING_INITIALIZE, $order);
        $formAddressing = $this->createCheckoutAddressingForm($order, $this->getUser());

        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_INITIALIZE, $order);
        $formPayment = $this->createCheckoutPaymentForm($order);

        $this->dispatchCheckoutEvent(SyliusCheckoutEvents::SHIPPING_INITIALIZE, $order);
        $formShipping = $this->createCheckoutShippingForm($order);

        // TODO
//        if ($form->handleRequest($request)->isValid()) {
//            $this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_SHIPPING_PRE_COMPLETE, $order);
//
//            $this->getManager()->persist($order);
//            $this->getManager()->flush();
//
//            $this->dispatchCheckoutEvent(SyliusCheckoutEvents::PAYMENT_SHIPPING_COMPLETE, $order);
//
//            return $this->complete();
//        }

        return $this->renderStep($context, $order, $formAddressing, $formShipping,$formPayment);
    }

    protected function renderStep(ProcessContextInterface $context, OrderInterface $order, FormInterface $formAddressing, FormInterface $formShipping, FormInterface $formPayment)
    {
        return $this->render($this->container->getParameter(sprintf('sylius.checkout.step.%s.template', $this->getName())), array(
            'order'   => $order,
            'formAddressing'    => $formAddressing->createView(),
            'formShipping'    => $formShipping->createView(),
            'formPayment'    => $formPayment->createView(),
            'context' => $context
        ));
    }

    protected function createCheckoutAddressingForm(OrderInterface $order, UserInterface $user = null)
    {
        return $this->createForm('sylius_checkout_addressing', $order, array('user' => $user));
    }

    protected function createCheckoutShippingForm(OrderInterface $order)
    {
        $this->zones = $this->getZoneMatcher()->matchAll($order->getShippingAddress());

        if (empty($this->zones)) {
            $this->get('session')->getFlashBag()->add('error', 'sylius.checkout.shipping.error');
        }

        return $this->createForm('sylius_checkout_shipping', $order, array(
          'criteria' => array(
            'zone' => !empty($this->zones) ? array_map(function ($zone) {
                return $zone->getId();
            }, $this->zones) : null,
            'enabled' => true,
          )
        ));
    }

    protected function createCheckoutPaymentForm(OrderInterface $order)
    {
        return $this->createForm('sylius_checkout_payment', $order, array('order_items' => $order->getItems()));
    }
}
