<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\WebBundle\Controller\Backend;

use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Backend dashboard controller.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class DashboardController extends Controller
{
    /**
     * Backend dashboard display action.
     */
    public function mainAction()
    {
        $orderRepository = $this->get('sylius.repository.order');
        $userRepository  = $this->get('sylius.repository.user');

        $date_from = new \DateTime('30 days ago');
        $date_to = new \DateTime();

        return $this->render('SyliusWebBundle:Backend/Dashboard:main.html.twig', array(
            'orders'                     => $orderRepository->findBy(array(), array('updatedAt' => 'desc'), 5),
            'orders_count'               => $orderRepository->countBetweenDates($date_from, $date_to),
            'orders_count_confirmed'     => $orderRepository->countBetweenDates($date_from, $date_to, array(
                    OrderInterface::STATE_CONFIRMED,
                    OrderInterface::STATE_SHIPPED,
                )),
            'sales'                      => $orderRepository->revenueBetweenDates($date_from, $date_to),
            'sales_confirmed'            => $orderRepository->revenueBetweenDates($date_from, $date_to, array(
                    OrderInterface::STATE_CONFIRMED,
                    OrderInterface::STATE_SHIPPED,
                )),
            'users'                      => $userRepository->findBy(array(), array('id' => 'desc'), 5),
        ));
    }
}
