<?php

namespace Sylius\Bundle\CoreBundle\Templating\Helper;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * Class OrderExchangeRateHelper
 * @package Sylius\Bundle\CoreBundle\Templating
 */
class OrderExchangeRateHelper extends Helper
{
	/**
	 * @param int $price
	 * @param OrderInterface $order
	 *
	 * @return int
	 */
	public function calculatePrice($price, OrderInterface $order)
	{
		return (int) round($price * $order->getExchangeRate());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'order_exchange_rate';
	}
}
