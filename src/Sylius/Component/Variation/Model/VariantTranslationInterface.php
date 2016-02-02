<?php

namespace Sylius\Component\Variation\Model;

/**
 * Sylius variant translation interface.
 *
 * @author Lukáš Mencl <tetra.jinak@gmail.com>
 */
interface VariantTranslationInterface
{
	/**
	 * Get presentation.
	 *
	 * This should be generated from option values
	 * when no other is set.
	 *
	 * @return string
	 */
	public function getPresentation();

	/**
	 * Set custom presentation.
	 *
	 * @param string $presentation
	 */
	public function setPresentation($presentation);
}