<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Payment\Model;

use Sylius\Component\Resource\Model\TimestampableInterface;

/**
 * Payment method interface.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
interface PaymentMethodInterface extends TimestampableInterface, PaymentMethodTranslationInterface
{
    /**
     * Check whether the payments method is currently enabled.
     *
     * @return Boolean
     */
    public function isEnabled();

    /**
     * Enable or disable the payments method.
     *
     * @param Boolean $enabled
     */
    public function setEnabled($enabled);

    /**
     * Set the payment gateway to use.
     *
     * @return string
     */
    public function getGateway();

    /**
     * Set gateway.
     *
     * @param string $gateway
     */
    public function setGateway($gateway);

    /**
     * Get the required app environment.
     *
     * @return string
     */
    public function getEnvironment();

    /**
     * Set the environment requirement.
     *
     * @param string $environment
     */
    public function setEnvironment($environment);
}
