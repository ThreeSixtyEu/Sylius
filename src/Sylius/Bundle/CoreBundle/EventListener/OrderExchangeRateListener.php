<?php

namespace Sylius\Bundle\CoreBundle\EventListener;

use Sylius\Component\Cart\Event\CartEvent;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Currency\Model\CurrencyInterface;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class OrderExchangeRateListener
 * @package Sylius\Bundle\CoreBundle\EventListener
 */
class OrderExchangeRateListener
{
	/**
	 * @var RepositoryInterface
	 */
	private $currencyRepository;

	/**
	 * Cache for the exchange rates.
	 *
	 * @var array
	 */
	private $cache;

	/**
	 * OrderExchangeRateListener constructor.
	 *
	 * @param RepositoryInterface $currencyRepository
	 */
	public function __construct(RepositoryInterface $currencyRepository)
	{
		$this->currencyRepository = $currencyRepository;
	}

	/**
	 * @param CartEvent $event
	 */
	public function cartInitialize(CartEvent $event)
	{
		$this->processOrderExchangeRate($event->getCart());
	}

	/**
	 * @param GenericEvent $event
	 */
	public function checkoutFinalize(GenericEvent $event)
	{
		$this->processOrderExchangeRate($event->getSubject());
	}

	/**
	 * @param mixed $order
	 */
	public function processOrderExchangeRate($order)
	{
		if (!$order instanceof OrderInterface) {
			throw new UnexpectedTypeException(
				$order,
				'Sylius\Component\Core\Model\OrderInterface'
			);
		}

		$currency = $this->getCurrency($order->getCurrency());

		$order->setExchangeRate($currency->getExchangeRate());
	}

	/**
	 * @param string $code
	 *
	 * @return CurrencyInterface
	 */
	private function getCurrency($code)
	{
		if (isset($this->cache[$code])) {
			return $this->cache[$code];
		}

		return $this->cache[$code] = $this->currencyRepository->findOneBy(array('code' => $code));
	}
}
