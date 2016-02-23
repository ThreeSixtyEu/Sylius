<?php
namespace Sylius\Bundle\WebBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class FormTypeExtension
/**
 * * @package Funlife\Bundle\EshopBundle\Form\Extension
 */
class FormTypeExtensionHelp extends AbstractTypeExtension
{
	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['help'] = $form->getConfig()->getOption('help');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);

		$resolver->setDefaults(array(
			'help' => null,
		));
	}

	/**
	 * Returns the name of the type being extended.
	 *
	 * @return string The name of the type being extended
	 */
	public function getExtendedType()
	{
		return 'form';
	}
}