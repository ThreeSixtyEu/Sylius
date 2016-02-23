<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Mailer\Sender;

use Sylius\Component\Mailer\Event\EmailSendEvent;
use Sylius\Component\Mailer\Provider\DefaultSettingsProviderInterface;
use Sylius\Component\Mailer\Sender\Adapter\AdapterInterface as SenderAdapterInterface;
use Sylius\Component\Mailer\Renderer\Adapter\AdapterInterface as RendererAdapterInterface;
use Sylius\Component\Mailer\Provider\EmailProviderInterface;
use Sylius\Component\Mailer\SyliusMailerEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Basic sender, which uses adapters system.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Jérémy Leherpeur <jeremy@leherpeur.net>
 * @author Gonzalo Vilaseca <gvilaseca@reiss.co.uk>
 */
class Sender implements SenderInterface
{
    /**
     * @var RendererAdapterInterface
     */
    protected $rendererAdapter;

    /**
     * @var SenderAdapterInterface
     */
    protected $senderAdapter;

    /**
     * @var EmailProviderInterface
     */
    protected $provider;

    /**
     * @var DefaultSettingsProviderInterface
     */
    protected $defaultSettingsProvider;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param RendererAdapterInterface $rendererAdapter
     * @param SenderAdapterInterface $senderAdapter
     * @param EmailProviderInterface $provider
     * @param DefaultSettingsProviderInterface $defaultSettingsProvider
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RendererAdapterInterface $rendererAdapter,
        SenderAdapterInterface $senderAdapter,
        EmailProviderInterface $provider,
        DefaultSettingsProviderInterface $defaultSettingsProvider,
        EventDispatcherInterface $dispatcher
    ) {
        $this->senderAdapter            = $senderAdapter;
        $this->rendererAdapter          = $rendererAdapter;
        $this->provider                 = $provider;
        $this->defaultSettingsProvider  = $defaultSettingsProvider;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function send($code, array $recipients, array $data = array())
    {
        $email = $this->provider->getEmail($code);

        if (!$email->isEnabled()) {
            return;
        }

        $senderAddress = $email->getSenderAddress() ?: $this->defaultSettingsProvider->getSenderAddress();
        $senderName = $email->getSenderName() ?: $this->defaultSettingsProvider->getSenderName();

        $emailSendEvent = new EmailSendEvent(null, $email, $data, $recipients);
        $this->dispatcher->dispatch(SyliusMailerEvents::EMAIL_PRE_PROCESS, $emailSendEvent);

        $renderedEmail = $this->rendererAdapter->render($email, $data);

        $this->senderAdapter->send($recipients, $senderAddress, $senderName, $renderedEmail, $email, $data);
    }
}
