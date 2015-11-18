<?php

namespace Sylius\Bundle\CoreBundle\EventListener;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Content locale listener.
 *
 * @author Lukáš Mencl <l.mencl@3sixty.eu>
 */
class ContentLocaleListener
{
	/**
	 * @var LocaleContextInterface
	 */
	private $localeContext;

	/**
	 * Constructor.
	 *
	 * @param LocaleContextInterface $localeContext
	 */
	public function __construct(LocaleContextInterface $localeContext)
	{
		$this->localeContext = $localeContext;
	}

	/**
	 * @param GetResponseEvent $event
	 */
	public function onRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();

		if ($request->get('contentLocale') && $request->getLocale() !== $request->get('contentLocale')) {
			$this->localeContext->setLocale($request->get('contentLocale'));
			$event->setResponse(new RedirectResponse($request->getRequestUri()));
		}
	}
}
