<?php

/*
* This file is part of the Sylius package.
*
* (c) Paweł Jędrzejewski
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sylius\Bundle\CoreBundle\Form\Type\Api;

use Sylius\Bundle\OrderBundle\Form\Type\OrderType as BaseOrderType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Order form type for api creation.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class OrderType extends BaseOrderType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('customer', 'sylius_customer_choice', array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('currency', 'sylius_currency_code_choice', array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('note', 'textarea', array(
                'constraints' => array(
                    new Length(array('max' => 255))
                )
            ))
            ->add('channel', 'sylius_channel_choice', array(
                'constraints' => array(
                    new NotBlank()
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_api_order';
    }
}
