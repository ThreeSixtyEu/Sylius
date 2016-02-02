<?php


/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\PaymentBundle\Doctrine\ORM;

use Pagerfanta\PagerfantaInterface;
use Sylius\Bundle\TranslationBundle\Doctrine\ORM\TranslatableResourceRepository;
use Sylius\Component\Payment\Repository\PaymentMethodRepositoryInterface;

/**
 * @author Arnaud Langlade <arn0d.dev@gmail.com>
 */
class PaymentMethodRepository extends TranslatableResourceRepository implements PaymentMethodRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQueryBuidlerForChoiceType(array $options)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        if (!$options['disabled']) {
            $queryBuilder->where('method.enabled = true');
        }

        return $queryBuilder;
    }
    
    /**
     * Create filter paginator.
     *
     * @param array $criteria
     * @param array $sorting
     * @param bool  $deleted
     *
     * @return PagerfantaInterface
     */
    public function createFilterPaginator($criteria = array(), $sorting = array(), $deleted = false)
    {
        $queryBuilder = parent::getCollectionQueryBuilder()
            ->select('paymentMethod, translation')
            ->leftJoin('paymentMethod.translations', 'translation')
        ;

        if (empty($sorting)) {
            if (!is_array($sorting)) {
                $sorting = array();
            }
        }

        $this->applySorting($queryBuilder, $sorting);

        if ($deleted) {
            $this->_em->getFilters()->disable('softdeleteable');
        }

        return $this->getPaginator($queryBuilder);
    }

    protected function getAlias()
    {
        return 'method';
    }
}
