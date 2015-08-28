<?php

namespace Sylius\Component\Product\Model;

use DateTime;
use Sylius\Component\Payment\Model\PaymentMethodInterface;

class PaymentConstraint implements PaymentConstraintInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var PaymentMethodInterface
     */
    protected $paymentMethod;

    /**
     * @var DateTime
     */
    protected $allowedSince;

    /**
     * @var DateTime()
     */
    protected $allowedUntil;

    /**
     *
     */
    public function __construct()
    {
        $this->allowedSince = new DateTime();
        $this->allowedUntil = new DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod(PaymentMethodInterface $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedSince()
    {
        return $this->allowedSince;
    }

    /**
     * @inheritdoc
     */
    public function setAllowedSince(DateTime $allowedSince = null)
    {
        $this->allowedSince = ($allowedSince === null) ? new DateTime() : $allowedSince;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedUntil()
    {
        return $this->allowedUntil;
    }

    /**
     * @inheritdoc
     */
    public function setAllowedUntil(DateTime $allowedUntil = null)
    {
        $this->allowedUntil = ($allowedUntil === null) ? new DateTime() : $allowedUntil;
        return $this;
    }
}
