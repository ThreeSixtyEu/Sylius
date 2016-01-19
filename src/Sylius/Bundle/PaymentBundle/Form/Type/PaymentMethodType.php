<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\PaymentBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Payment method form type.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PaymentMethodType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', 'a2lix_translationsForms', array(
                'form_type' => 'sylius_payment_method_translation',
                'label'    => 'sylius.form.payment_method.translations'
            ))
            ->add('gateway', 'sylius_payment_gateway_choice', array(
                'label' => 'sylius.form.payment_method.gateway'
            ))
            ->add('groups', 'sylius_group_choice', array(
                'label' => 'sylius.form.payment_method.groups_limit',
                'multiple' => true,
            ))
            ->add('enabled', 'checkbox', array(
                'required' => false,
                'label'    => 'sylius.form.payment_method.enabled'
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_payment_method';
    }
}
