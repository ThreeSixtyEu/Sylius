<?php

namespace Sylius\Bundle\CoreBundle\Doctrine\ORM;

use Sylius\Bundle\PaymentBundle\Doctrine\ORM\PaymentMethodRepository as BasePaymentMethodRepository;

class PaymentMethodRepository extends BasePaymentMethodRepository
{
    /**
     * {@inheritdoc}
     */
    public function getQueryBuidlerForChoiceType(array $options)
    {
        $queryBuilder = parent::getQueryBuidlerForChoiceType($options);

        if ($options['channel']) {
            $queryBuilder->andWhere('method IN (:methods)')
                ->setParameter('methods', $options['channel']->getPaymentMethods()->toArray());
        }

        if (count($options['order_items'])) {
            $removedPaymentIds = array();

            /** @var \Sylius\Component\Core\Model\OrderItemInterface $item */
            foreach ($options['order_items'] as $item) {
                $removedPaymentIds = array_merge($removedPaymentIds, $item->getVariant()->getProduct()->getConstrainedPaymentIds());
            }

            if (!empty($removedPaymentIds)) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('method', $removedPaymentIds));
            }
        }

        return $queryBuilder;
    }
}
