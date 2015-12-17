<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Core\Model;

use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Shipping\Model\ShippingMethod as BaseShippingMethod;

/**
 * Shipping method available for selected zone.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Gonzalo Vilaseca <gvilaseca@reiss.co.uk>
 */
class ShippingMethod extends BaseShippingMethod implements ShippingMethodInterface
{
    /**
     * Geographical zone.
     *
     * @var ZoneInterface
     */
    protected $zone;

    /**
     * ShippingMethod require address
     *
     * @var boolean
     */
    protected $requireAddress;

    /**
     * {@inheritdoc}
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * {@inheritdoc}
     */
    public function setZone(ZoneInterface $zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTranslationEntityClass()
    {
        return parent::getTranslationEntityClass();
    }

    /**
     * {@inheritdoc}
     */
    public function setRequireAddress($requireAddress)
    {
        $this->requireAddress = $requireAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequireAddress()
    {
        return $this->requireAddress;
    }
}
