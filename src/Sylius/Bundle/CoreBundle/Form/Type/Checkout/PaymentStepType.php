<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Form\Type\Checkout;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Checkout payment step form type.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PaymentStepType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $notBlank = new NotBlank();
        $notBlank->message = 'sylius.checkout.payment_method.not_blank';

        $builder
            ->add('paymentMethod', 'sylius_payment_method_choice', array(
                'label'          => 'sylius.form.checkout.payment_method',
                'expanded'       => true,
                'property_path'  => 'lastPayment.method',
                'order_items' => $options['order_items'],
                'constraints'    => array(
                    $notBlank
                )
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(array(
            'order_items' => new ArrayCollection(),
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_checkout_payment';
    }
}
