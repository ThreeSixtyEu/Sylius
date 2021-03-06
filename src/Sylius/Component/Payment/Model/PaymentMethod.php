<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Payment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\GroupInterface;
use Sylius\Component\Translation\Model\AbstractTranslatable;

/**
 * Payments method model.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class PaymentMethod extends AbstractTranslatable implements PaymentMethodInterface
{
    /**
     * Payments method identifier.
     *
     * @var mixed
     */
    protected $id;

    /**
     * Is method enabled?
     *
     * @var Boolean
     */
    protected $enabled = true;

    /**
     * Gateway name.
     *
     * @var string
     */
    protected $gateway;

    /**
     * Required environment.
     *
     * @var string
     */
    protected $environment;

    /**
     * Creation date.
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Last update time.
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var Collection
     */
    protected $groups;

    /**
     * Constructor.
     */
    public function __construct()
    {
	    parent::__construct();
        $this->createdAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (Boolean) $enabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->translate()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->translate()->setName($name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translate()->getDescription();
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->translate()->setDescription($description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

	/**
	 * Returns the groups this payment is assigned to.
	 *
	 * @return Collection
	 */
	public function getGroups()
	{
		return $this->groups ?: $this->groups = new ArrayCollection();
	}

	/**
	 * Returns groups as an array of group names.
	 *
	 * @return array
	 */
	public function getGroupNames()
	{
		$names = array();
		foreach ($this->getGroups() as $group) {
			$names[] = $group->getName();
		}

		return $names;
	}

	/**
	 * Checks whether this payment method is assigned to a group.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasGroup($name)
	{
		if ($this->getGroups()->isEmpty()) {
			return true;
		}

		return in_array($name, $this->getGroupNames());
	}

	/**
	 * @param GroupInterface $group
	 * @return $this
	 */
	public function addGroup(GroupInterface $group)
	{
		if (!$this->getGroups()->contains($group)) {
			$this->getGroups()->add($group);
		}

		return $this;
	}

	/**
	 * @param GroupInterface $group
	 * @return $this
	 */
	public function removeGroup(GroupInterface $group)
	{
		if ($this->getGroups()->contains($group)) {
			$this->getGroups()->removeElement($group);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTranslationEntityClass()
	{
		return get_class().'Translation';
	}
}
