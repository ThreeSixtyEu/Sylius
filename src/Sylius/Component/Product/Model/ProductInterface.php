<?php

/*
 * This file is part of the Sylius package.
 *
 * (c); Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Product\Model;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Archetype\Model\ArchetypeSubjectInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Model\SlugAwareInterface;
use Sylius\Component\Resource\Model\SoftDeletableInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

/**
 * Base product interface.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Gonzalo Vilaseca <gvilaseca@reiss.co.uk>
 */
interface ProductInterface extends
	ArchetypeSubjectInterface,
	SlugAwareInterface,
	SoftDeletableInterface,
	TimestampableInterface,
	ProductTranslationInterface
{
	/**
	 * Check whether the product is available.
	 *
	 * @return bool
	 */
	public function isAvailable();

	/**
	 * Return available on.
	 *
	 * @return \DateTime
	 */
	public function getAvailableOn();

	/**
	 * Set available on.
	 *
	 * @param null|\DateTime $availableOn
	 */
	public function setAvailableOn(\DateTime $availableOn = null);

	/**
	 * Return available until.
	 *
	 * @return \DateTime
	 */
	public function getAvailableUntil();

	/**
	 * Set available until.
	 *
	 * @param null|\DateTime $availableUntil
	 */
	public function setAvailableUntil(\DateTime $availableUntil = null);

	/**
	 * Get payment constraints.
	 *
	 * @return Collection|PaymentConstraint[]
	 */
	public function getPaymentConstraints();

	/**
	 * Set payment constraints.
	 *
	 * @param Collection $paymentConstraints
	 * @return $this
	 */
	public function setPaymentConstraints(Collection $paymentConstraints);

	/**
	 * Get a list of payment IDs that are supposed to be unavailable for a specific date
	 *
	 * @param \DateTime $forDate the date to check against; defaults to <em>now</em>
	 * @return int[]
	 */
	public function getConstrainedPaymentIds(\DateTime $forDate = null);
}
