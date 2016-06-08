<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\PaymentBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * Payment update processing listener.
 *
 * @author Jiří Barouš <amunaks@gmail.com>
 */
class PaymentUpdateListener
{
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$this->supports($entity)) {
            return;
        }

        $this->handleStateUpdate($args);
    }

    protected function handleStateUpdate(PreUpdateEventArgs $args)
    {
        $stateField = 'state';

        if (!$args->hasChangedField($stateField)) {
            return;
        }

        $newValue = $args->getNewValue($stateField);
        if ($newValue === PaymentInterface::STATE_COMPLETED) {
            /** @var PaymentInterface $payment */
            $payment = $args->getEntity();
            $payment->setCompletedAt(new \DateTime());

            // tell UnitOfWork to recalculate changes
            $em = $args->getEntityManager();
            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($payment)), $payment);
        }
    }

    protected function supports($entity)
    {
        return $entity instanceof PaymentInterface;
    }
}
