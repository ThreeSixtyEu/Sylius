<?php

/*
* This file is part of the Sylius package.
*
* (c) Paweł Jędrzejewski
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sylius\Bundle\CoreBundle\Form\Type\Filter;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderFilterType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', 'filter_text', array(
                'label' => 'funlife.eshop.form.order.number',
                'attr' => array(
                    'placeholder' => 'funlife.eshop.form.order.number_hint',
                ),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    $values['value'] = str_replace('#', '', $values['value']);

                    if (empty($values['value'])) {
                        return null;
                    }

                    /** @var QueryBuilder $qb */
                    $qb = $filterQuery->getQueryBuilder();

                    // you can search values like: 100 - 150, 20,13, 15- 150

                    // normalize range oparators
                    $values['value'] = preg_replace('/([\s]+-[\s]+)|(-[\s]+)|([\s]+-)/', '-', $values['value']);
                    // normalize separators
                    $values['value'] = preg_replace('/[,;|\s]+/', ',', $values['value']);

                    if(strpos($values['value'], '-') === false) {
                        // fuzzy search for single values
                        if (strpos($values['value'], ',') === false) {
                            $qb
                                ->andWhere($qb->expr()->like('o.number', ':' . 'orderNumber'))
                                ->setParameter('orderNumber', '%' . (string)trim($values['value']) . '%');
                            return $qb;
                        }
                    } else {
                        //prepare for search between values
                        $values['value'] .= ',';
                    }

                    // exact search for multiple values delimited by comma
                    $numbers = [];
                    foreach (explode(',', $values['value']) as $id) {
                        if(strpos($id, '-') === false) {
                            if (trim($id) !== '') {
                                $numbers[] = str_pad((string)trim($id), 9, '0', STR_PAD_LEFT);
                            }
                        } else {
                            // range search betwen $value1-$value2
                            $stringStart = strpos($id, '-');
                            $idStart = substr($id, 0, $stringStart);
                            $idEnd = substr($id, $stringStart + 1) !== false ? substr($id, $stringStart + 1) : $idStart;
                            if (is_numeric($idStart) && is_numeric($idEnd)){
                                if ($idEnd < $idStart) {
                                    $tmpEnd = $idEnd; $idEnd = $idStart; $idStart = $tmpEnd; unset($tmpEnd);
                                }
                                for ($i = $idStart; $i <= $idEnd; $i++) {
                                    if (trim($id) !== '') {
                                        $numbers[] = str_pad((string)trim($i), 9, '0', STR_PAD_LEFT);
                                    }
                                }
                            }
                        }
                    }

                    // invalid format, force no results
                    if (count($numbers) === 0) {
                        $qb->where($qb->expr()->eq(1, 0));
                        return $qb;
                    }

                    $qb
                        ->andWhere($qb->expr()->in('o.number', ':orderNumbers'))
                        ->setParameter('orderNumbers', $numbers)
                    ;

                    return $qb;
                },
            ))
            ->add('state', 'filter_choice', array(
                'label' => 'funlife.eshop.form.order.state.header',
                'choices' => array(
                    OrderInterface::STATE_ABANDONED => 'funlife.eshop.form.order.state.abandoned',
                    OrderInterface::STATE_CANCELLED => 'funlife.eshop.form.order.state.cancelled',
                    //OrderInterface::STATE_CART => 'funlife.eshop.form.order.state.cart',
                    OrderInterface::STATE_CONFIRMED => 'funlife.eshop.form.order.state.confirmed',
                    OrderInterface::STATE_PENDING => 'funlife.eshop.form.order.state.pending',
                    OrderInterface::STATE_RETURNED => 'funlife.eshop.form.order.state.returned',
                    OrderInterface::STATE_SHIPPED => 'funlife.eshop.form.order.state.shipped',
                ),
            ))
            ->add('paymentType', 'filter_entity', array(
                'label' => 'sylius.form.payment.method',
                'translation_domain' => 'messages',
                'class' => 'Sylius\Component\Payment\Model\PaymentMethod',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('method');
                },
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $queryBuilder = $filterQuery->getQueryBuilder();
                    $queryBuilder
                        ->innerJoin('o.payments', 'payment')
                        ->innerJoin('payment.method', 'method')
                        ->andWhere('method.id = :id')
                        ->setParameter('id', $values['value']->getId());

                    return $queryBuilder;
                },
            ))
            ->add('paymentState', 'filter_choice', array(
                'label' => 'funlife.eshop.form.payment.state.header',
                'choices' => array(
                    PaymentInterface::STATE_COMPLETED   => 'funlife.eshop.form.payment.state.completed',
                    PaymentInterface::STATE_NEW         => 'funlife.eshop.form.payment.state.new',
                ),
            ))
            ->add('shippingState', 'filter_choice', array(
                'label' => 'funlife.eshop.form.shipment.state.header',
                'choices' => array(
                    ShipmentInterface::STATE_SHIPPED   => 'funlife.eshop.form.shipment.state.shipped',
                    ShipmentInterface::STATE_RETURNED   => 'funlife.eshop.form.shipment.state.returned',
                    ShipmentInterface::STATE_PENDING   => 'funlife.eshop.form.shipment.state.pending',
                    ShipmentInterface::STATE_BACKORDERED   => 'funlife.eshop.form.shipment.state.backorder',
                    ShipmentInterface::STATE_CANCELLED   => 'funlife.eshop.form.shipment.state.cancelled',
                    ShipmentInterface::STATE_CHECKOUT   => 'funlife.eshop.form.shipment.state.checkout',
                    ShipmentInterface::STATE_ONHOLD   => 'funlife.eshop.form.shipment.state.onhold',
                    ShipmentInterface::STATE_READY   => 'funlife.eshop.form.shipment.state.ready',
                ),
            ))
            ->add('product', 'filter_entity', array(
                'label' => 'funlife.eshop.form.payment.product',
                //TODO: correcting to Sylius\Component\Core\Model\Product causes translatable error
                'class' => 'Funlife\Bundle\EshopBundle\Entity\Product',
                'property' => 'name',
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('product');
                    $qb->andWhere($qb->expr()->isNull('product.deletedAt'));
                    return $qb;
                },
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $queryBuilder = $filterQuery->getQueryBuilder();
                    $queryBuilder
                        ->innerJoin('o.items', 'item_order')
                        ->innerJoin('item_order.variant', 'variant')
                        ->innerJoin('variant.object', 'product')
                        ->andWhere('product.id = :id')
                        ->setParameter('id', $values['value']->getId());

                    return $queryBuilder;
                },
            ))
            ->add('createdAt', 'filter_date_range', array(
                'label' => 'funlife.eshop.form.filter.date_range',
                'left_date_options' => array(
                    'label' => 'funlife.eshop.form.filter.range.from',
                    'label_attr' => array('class' => 'sr-only'),
                    'attr' => array(
                        'class' => 'datepicker',
                        'placeholder' => 'funlife.eshop.form.filter.range.from'),
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                ),
                'right_date_options' => array(
                    'label' => 'funlife.eshop.form.filter.range.to',
                    'label_attr' => array('class' => 'sr-only'),
                    'attr' => array(
                        'class' => 'datepicker',
                        'placeholder' => 'funlife.eshop.form.filter.range.to'),
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                ),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    $queryBuilder = $filterQuery->getQueryBuilder();
                    if(isset($values['value']['left_date'][0])) {
                        $queryBuilder
                            ->andWhere('o.createdAt > :date_from')
                            ->setParameter('date_from', $values['value']['left_date'][0]);
                    }

                    if(isset($values['value']['right_date'][0])) {
                        /** @var \DateTime $date */
                        $date = $values['value']['right_date'][0];
                        $date->modify('+1 day -1 second');
                        $queryBuilder
                            ->andWhere('o.createdAt < :date_to')
                            ->setParameter('date_to', $date);
                    }

                    return $queryBuilder;
                },
            ))
            ->add('itemsTotal', 'filter_number_range', array(
                'label' => 'funlife.eshop.form.order.price_range',
                'left_number_options' => array(
                    'label' => 'funlife.eshop.form.filter.range.from',
                    'label_attr' => array('class' => 'sr-only'),
                    'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN_EQUAL,
                    'attr' => array(
                        'placeholder' => 'funlife.eshop.form.filter.range.from',
                    ),
                ),
                'right_number_options' => array(
                    'label' => 'funlife.eshop.form.filter.range.to',
                    'label_attr' => array('class' => 'sr-only'),
                    'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL,
                    'attr' => array(
                        'placeholder' => 'funlife.eshop.form.filter.range.to',
                    ),
                ),
            ))
            ->add('user_name', 'filter_text', array(
                'label' => 'funlife.eshop.form.order.user.name',
                'condition_pattern' => FilterOperands::STRING_BOTH,
                'attr' => array(
                    'placeholder' => 'funlife.eshop.form.order.user.name_hint',
                ),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    /** @var QueryBuilder $qb */
                    $qb = $filterQuery->getQueryBuilder();

                    // Concatenate user.firstname, ' ' and user.lastanme and put it in a LIKE %username% query
                    $qb
                        ->leftJoin('o.shippingAddress', 'a')
                        ->leftJoin('o.billingAddress', 'b')
                        ->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->like(
                                    $qb->expr()->concat(
                                        $qb->expr()->concat('user.firstName', $qb->expr()->literal(' ')),
                                        'user.lastName'
                                    ),
                                    ':username'
                                ),
                                $qb->expr()->like(
                                    $qb->expr()->concat(
                                        $qb->expr()->concat('a.firstName', $qb->expr()->literal(' ')),
                                        'a.lastName'
                                    ),
                                    ':username'
                                ),
                                $qb->expr()->like(
                                    $qb->expr()->concat(
                                        $qb->expr()->concat('b.firstName', $qb->expr()->literal(' ')),
                                        'b.lastName'
                                    ),
                                    ':username'
                                )
                            )
                        )
                        ->setParameter('username', '%' . $values['value'] . '%');

                    return $qb;
                },
            ))
            ->add('user_email', 'filter_text', array(
                'label' => 'funlife.eshop.form.order.user.email',
                'condition_pattern' => FilterOperands::STRING_BOTH,
                'attr' => array(
                    'placeholder' => 'funlife.eshop.form.order.user.email_hint',
                ),
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    /** @var QueryBuilder $qb */
                    $qb = $filterQuery->getQueryBuilder();
                    $qb
                        ->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->like('user.email', ':usermail'),
                                $qb->expr()->like('o.email', ':usermail')
                            )
                        )
                        ->setParameter('usermail', '%' . $values['value'] . '%');
                    return $qb;
                },
            ))
            ->add('user_anon', 'filter_checkbox', array(
                'label' => 'funlife.eshop.form.order.user.anonymous',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if ($values['value'] === true) {
                        /** @var QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();
                        $qb->andWhere('user.id is null');
                        return $qb;
                    }
                    return null;
                },
            ))
            ->add('deleted', 'filter_checkbox', array(
                'label' => 'funlife.eshop.form.order.show_deleted',
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if ($values['value'] === false) {
                        /** @var QueryBuilder $qb */
                        $qb = $filterQuery->getQueryBuilder();
                        $qb->andWhere('o.deletedAt is null');
                        return $qb;
                    }
                    return null;
                },
            ))
        ;

        $syliusPriceTransformer = new CallbackTransformer(
            function($originalValue) {
                return ($originalValue === 0 ? 0 : $originalValue / 100);
            },
            function($submittedValue) {
                if ($submittedValue === null) {
                    return null;
                }

                return $submittedValue * 100;
            }
        );

        $builder->get('itemsTotal')->get('left_number')->addModelTransformer($syliusPriceTransformer);
        $builder->get('itemsTotal')->get('right_number')->addModelTransformer($syliusPriceTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'form',
            'csrf_protection' => false,
            'validation_groups' => array('filtering'), // avoid NotBlank() constraint-related message
            'method' => 'get',
        ));
    }

    public function getName()
    {
        return 'sylius_order_filter';
    }
}
