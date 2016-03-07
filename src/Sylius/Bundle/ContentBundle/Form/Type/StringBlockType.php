<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\ContentBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * String block type.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class StringBlockType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder
            ->add('parentDocument', null, array(
                'label' => 'sylius.block.parent'
            ))
            ->add('name', 'text', array(
                'label' => 'sylius.block.internal_name'
            ))
            ->add('body', 'textarea', array(
                'required' => false,
                'label'    => 'sylius.block.body',
            ))
            ->add('publishable', null, array(
                'label' => 'sylius.block.publishable'
            ))
            ->add('publishStartDate', 'datetime', array(
                'label' => 'sylius.block.start_date',
                'empty_value' =>/** @Ignore */ array('year' => '-', 'month' => '-', 'day' => '-'),
                'time_widget' => 'text',
            ))
            ->add('publishEndDate', 'datetime', array(
                'label' => 'sylius.block.end_date',
                'empty_value' =>/** @Ignore */ array('year' => '-', 'month' => '-', 'day' => '-'),
                'time_widget' => 'text',
            ))
        ;

        $opt = isset($_GET['contentLocale']) ? array('data' => $_GET['contentLocale']): array();
        $builder->add('locale', 'hidden', $opt);

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_string_block';
    }
}
