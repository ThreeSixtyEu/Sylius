<?php

namespace Sylius\Bundle\CoreBundle\Twig;

use Sylius\Bundle\CoreBundle\Templating\Helper\OrderExchangeRateHelper;
use Sylius\Bundle\CurrencyBundle\Templating\Helper\MoneyHelper;
use Sylius\Component\Core\Model\OrderInterface;

/**
 * Class OrderExchangeRateExtension
 * @package Sylius\Bundle\CoreBundle\Twig
 */
class OrderExchangeRateExtension extends \Twig_Extension
{
	/**
	 * @var OrderExchangeRateHelper
	 */
	private $orderExchangeRateHelper;

	/**
	 * @var MoneyHelper
	 */
	private $moneyHelper;

	/**
	 * Constructor.
	 *
	 * @param OrderExchangeRateHelper $orderExchangeRateHelper
	 * @param MoneyHelper $moneyHelper
	 */
	public function __construct(OrderExchangeRateHelper $orderExchangeRateHelper, MoneyHelper $moneyHelper)
	{
		$this->orderExchangeRateHelper = $orderExchangeRateHelper;
		$this->moneyHelper = $moneyHelper;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('sylius_order_price', array($this, 'calculatePrice')),
		);
	}

	/**
	 * @param int $price
	 * @param OrderInterface $order
	 * @param bool $decimal
	 *
	 * @return int
	 */
	public function calculatePrice($price, OrderInterface $order, $decimal = false)
	{
		$amount = $this->orderExchangeRateHelper->calculatePrice($price, $order);

		return $this->moneyHelper->formatAmount($amount, $order->getCurrency(), $decimal);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'sylius_order_exchange_rate';
	}
}
