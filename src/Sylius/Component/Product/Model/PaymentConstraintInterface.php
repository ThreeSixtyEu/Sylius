<?php

namespace Sylius\Component\Product\Model;

use DateTime;
use Sylius\Component\Payment\Model\PaymentMethodInterface;

/**
 * Interface PaymentConstraintInterface
 * @package Sylius\Component\Product\Model
 */
interface PaymentConstraintInterface
{

    /**
     * Get payment method that this constraint applies to
     *
     * @return PaymentMethodInterface
     */
    public function getPaymentMethod();


    /**
     * Set payment method that this constraint applies to
     *
     * @param PaymentMethodInterface $paymentType
     * @return $this
     */
    public function setPaymentMethod(PaymentMethodInterface $paymentType);

    /**
     * Get date since the constraint frees the payment type
     *
     * @return DateTime
     */
    public function getAllowedSince();

    /**
     * Set date since the constraint frees the payment type
     *
     * @param null|DateTime $allowedSince null for <em>now</em>
     * @return $this
     */
    public function setAllowedSince(DateTime $allowedSince = null);

    /**
     * Get date until which the constraint allows the payment type
     *
     * @return DateTime
     */
    public function getAllowedUntil();

    /**
     * Set date until which the constraint allows the payment type
     *
     * @param null|DateTime $allowedUntil null for <em>now</em>
     * @return $this
     */
    public function setAllowedUntil(DateTime $allowedUntil = null);
}
