<?php

namespace Sylius\Bundle\CoreBundle\EventListener;

use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooserInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Content locale listener.
 *
 * @author Lukáš Mencl <l.mencl@3sixty.eu>
 */
class ContentLocaleListener
{
	/**
	 * @var LocaleChooserInterface
	 */
	private $localeChooser;

	/**
	 * Constructor.
	 *
	 * @param LocaleChooserInterface $localeChooser
	 */
	public function __construct(LocaleChooserInterface $localeChooser)
	{
		$this->localeChooser = $localeChooser;
	}

	/**
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();

		if ($request->get('contentLocale')) {
			$this->localeChooser->setLocale($request->get('contentLocale'));
		}
	}
}
