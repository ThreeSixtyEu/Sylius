<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Context;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\SettingsBundle\Manager\SettingsManagerInterface;
use Sylius\Component\Cart\Provider\CartProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Currency\Context\CurrencyContext as BaseCurrencyContext;
use Sylius\Component\Storage\StorageInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CurrencyContext extends BaseCurrencyContext
{
    protected $securityContext;
    protected $settingsManager;
    protected $userManager;

	/**
	 * @var CartProviderInterface
	 */
	private $cartProvider;

	public function __construct(
        StorageInterface $storage,
        SecurityContextInterface $securityContext,
        SettingsManagerInterface $settingsManager,
        ObjectManager $userManager,
		CartProviderInterface $cartProvider
    ) {
        $this->securityContext = $securityContext;
        $this->settingsManager = $settingsManager;
        $this->userManager = $userManager;
		$this->cartProvider = $cartProvider;

        parent::__construct($storage, $this->getDefaultCurrency());
	}

    public function getDefaultCurrency()
    {
        return $this->settingsManager->loadSettings('general')->get('currency');
    }

    public function getCurrency()
    {
        if ((null !== $user = $this->getUser()) && null !== $user->getCurrency()) {
            return $user->getCurrency();
        }

        return parent::getCurrency();
    }

    public function setCurrency($currency)
    {
        if (null === $user = $this->getUser()) {
            return parent::setCurrency($currency);
        }

        $user->setCurrency($currency);
	    $this->updateCart($currency);

        $this->userManager->persist($user);
        $this->userManager->flush();
    }

    protected function getUser()
    {
        if ($this->securityContext->getToken() && $this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->securityContext->getToken()->getUser();
        }
    }

	/**
	 * Change cart currency if is in state cart
	 *
	 * @param string $currency
	 */
    protected function updateCart($currency)
    {
    	if (!$this->cartProvider->hasCart()) {
    		return;
	    }

	    $cart = $this->cartProvider->getCart();

	    if ($cart instanceof OrderInterface && $cart->getState() === OrderInterface::STATE_CART) {
		    $cart->setCurrency($currency);
	    }
    }
}
