<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\EmailManager;

use Sylius\Bundle\CoreBundle\Mailer\Emails;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Sends the order confirmation email.
 *
 * @author Hussein Jafferjee <hussein@jafferjee.ca>
 */
class OrderEmailManager
{
    /** @var SenderInterface */
    protected $emailSender;

    /**
     * @param SenderInterface $emailSender
     */
    public function __construct(SenderInterface $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    /**
     * @param GenericEvent $event
     */
    public function sendConfirmationEmail(GenericEvent $event)
    {
        $this->emailSender->send(Emails::ORDER_CONFIRMATION, array($event->getSubject()->getCustomer()->getEmail()), array('order' => $event->getSubject()));
    }
}
