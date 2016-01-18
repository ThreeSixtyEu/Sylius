<?php

namespace Sylius\Component\Variation\Model;

use Sylius\Component\Translation\Model\AbstractTranslation;

/**
 * Sylius variant translation model.
 *
 * @author Lukáš Mencl <tetra.jinak@gmail.com>
 */
class VariantTranslation extends AbstractTranslation implements VariantTranslationInterface
{

	/**
	 * Variant id.
	 *
	 * @var mixed
	 */
	protected $id;

	/**
	 * Variant presentation.
	 *
	 * @var string
	 */
	protected $presentation;

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
	public function getPresentation()
	{
		return $this->presentation;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setPresentation($presentation)
	{
		$this->presentation = $presentation;

		return $this;
	}
}
