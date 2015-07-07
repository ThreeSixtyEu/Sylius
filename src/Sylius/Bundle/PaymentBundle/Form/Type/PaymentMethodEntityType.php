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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\Group;
use Sylius\Component\Core\Model\User;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Payment method choice type for "doctrine/orm" driver.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PaymentMethodEntityType extends PaymentMethodChoiceType
{

    /**
     * @var mixed
     */
    protected $user;

    public function __construct($className, SecurityContext $securityContext)
    {
        parent::__construct($className);

        if ($securityContext->getToken() !== null) {
            $this->user = $securityContext->getToken()->getUser();
        }
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $queryBuilder = function (Options $options) {
            $groupIds = array();
            if ($this->user instanceof User) {
                /** @var Collection $groups */
                $groups = $this->user->getGroups();

                /** @var Group $group */
                foreach ($groups as $group) {
                    $groupIds[] = $group->getId();
                }
            }

            $getDisabled = $options['disabled'];

            return function (EntityRepository $repository) use ($groupIds, $getDisabled) {
                $qb = $repository->createQueryBuilder('method');
                if (!$getDisabled) {
                    $qb
                        ->andwhere('method.enabled = :enabled')
                        ->setParameter('enabled', true)
                    ;
                }
                $qb->leftJoin('method.groups', 'g');
                if (empty($groupIds)) {
                    $qb->andWhere('g.id is null');
                } else {
                    $qb->andWhere($qb->expr()->orX(
                        $qb->expr()->isNull('g.id'),
                        $qb->expr()->in('g.id', $groupIds)
                    ));
                }
                return $qb;
            };
        };

        $resolver
            ->setDefaults(array(
                'query_builder' => $queryBuilder
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }
}
