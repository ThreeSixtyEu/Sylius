<?php

/*
* This file is part of the Sylius package.
*
* (c) Paweł Jędrzejewski
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sylius\Bundle\PayumBundle\Payum\Dummy\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentStatusAction extends PaymentAwareAction
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $container;

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

    /**
     * {@inheritDoc}
     *
     * @param $request GetStatusInterface
     */
    public function execute($request)
    {
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();
        $paymentDetails = $payment->getDetails();

        if (empty($paymentDetails)) {
            $request->markNew();

            return;
        }

        if (isset($paymentDetails['captured'])) {
			$factory = $this->container->get('sm.factory');
			/** @var $order \Funlife\Bundle\EshopBundle\Entity\Order */
			$order = $payment->getOrder();
			$orderSM = $factory->get($order, OrderTransitions::GRAPH);
			if($orderSM->can(OrderTransitions::SYLIUS_CONFIRM)) {
				$orderSM->apply(OrderTransitions::SYLIUS_CONFIRM);
				if($orderSM->can(OrderTransitions::SYLIUS_SHIP)) {
					$orderSM->apply(OrderTransitions::SYLIUS_SHIP);
				}
			} else { // if state machine won't work the information about sold tickets will not be updated but at least the client will not be affected
				$order->getLastShipment()->setState(ShipmentInterface::STATE_SHIPPED);
				$order->setState(OrderInterface::STATE_SHIPPED);
			}
            $request->markCaptured();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface
        ;
    }
}
