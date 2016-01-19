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
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\TranslatableEntityRepository;

/**
 * Default payment method repository
 *
 * @author Lukáš Mencl
 */
class PaymentMethodRepository extends TranslatableEntityRepository
{
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

	/**
	 * @return string
	 */
	public function getAlias()
	{
		return 'paymentMethod';
	}
}