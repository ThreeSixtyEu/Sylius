<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CurrencyBundle\Twig;

use Sylius\Bundle\CurrencyBundle\Templating\Helper\CurrencyHelper;
use Symfony\Component\Intl\Intl;

/**
 * Sylius currency Twig helper.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class CurrencyExtension extends \Twig_Extension
{
    /**
     * @var CurrencyHelper
     */
    protected $helper;

    /**
     * @param CurrencyHelper $helper
     */
    public function __construct(CurrencyHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sylius_currency', array($this, 'convertAmount')),
            new \Twig_SimpleFilter('sylius_price', array($this, 'convertAndFormatAmount')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
          new \Twig_SimpleFunction('currency', array($this, 'getCurrency')),
          new \Twig_SimpleFunction('currency_symbol', array($this, 'getCurrencySymbol')),
        );    }

    /**
     * Convert amount to target currency.
     *
     * @param integer     $amount
     * @param string|null $currency
     *
     * @return string
     */
    public function convertAmount($amount, $currency = null)
    {
        return $this->helper->convertAmount($amount, $currency);
    }

    /**
     * Convert and format amount.
     *
     * @param integer     $amount
     * @param string|null $currency
     *
     * @return string
     */
    public function convertAndFormatAmount($amount, $currency = null)
    {
        return $this->helper->convertAndFormatAmount($amount, $currency);
    }

    /**
     * Get translated currency symbol
     *
     * @param $currency
     *
     * @return null|string
     */
    public function getCurrencySymbol($currency = null) {
        if (is_null($currency)) {
            $currency = $this->getCurrency();
        }
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency);
    }

    /**
     * Get current currency
     *
     * @return string
     */
    public function getCurrency() {
        return $this->helper->getCurrency();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_currency';
    }
}
