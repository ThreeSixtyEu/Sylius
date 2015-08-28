<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ProductBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PaymentConstraintType
 * @package Sylius\Bundle\ProductBundle\Form\Type
 */
class PaymentConstraintType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paymentMethod', 'sylius_payment_method_choice', array(
                'label' => 'sylius.form.payment.method'
            ))
            ->add('allowedSince', 'datetime', array(
                'label' => 'sylius.form.payment_constraint.allowed_since',
                'empty_value' => array('year' => '-', 'month' => '-', 'day' => '-'),
                'time_widget' => 'text',
            ))
            ->add('allowedUntil', 'datetime', array(
                'label' => 'sylius.form.payment_constraint.allowed_until',
                'empty_value' => array('year' => '-', 'month' => '-', 'day' => '-'),
                'time_widget' => 'text',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_product_payment_constraint';
    }
}
