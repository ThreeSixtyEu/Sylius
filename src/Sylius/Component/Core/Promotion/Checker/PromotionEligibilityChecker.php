<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Core\Promotion\Checker;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\CouponInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\User\Model\CustomerAwareInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Promotion\Checker\PromotionEligibilityChecker as BasePromotionEligibilityChecker;
use Sylius\Component\Promotion\Model\PromotionCouponAwareSubjectInterface;
use Sylius\Component\Promotion\Model\PromotionCouponsAwareSubjectInterface;
use Sylius\Component\Promotion\Model\PromotionInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Promotion\SyliusPromotionEvents;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class PromotionEligibilityChecker extends BasePromotionEligibilityChecker
{
    /**
     * @var OrderRepositoryInterface
     */
    private $subjectRepository;

    /**
     * @param OrderRepositoryInterface $subjectRepository
     * @param ServiceRegistryInterface $registry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(OrderRepositoryInterface $subjectRepository, ServiceRegistryInterface $registry, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($registry, $dispatcher);

        $this->subjectRepository = $subjectRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function areCouponsEligibleForPromotion(PromotionSubjectInterface $subject, PromotionInterface $promotion)
    {
        if (!$subject instanceof CustomerAwareInterface) {
            return false;
        }

        $eligible = false;

        // Check to see if there is a per customer usage limit on coupon
        if ($subject instanceof PromotionCouponAwareSubjectInterface) {
            $coupon = $subject->getPromotionCoupon();
            if ($coupon === null) {
                return false;
            }
            if ( $promotion === $coupon->getPromotion()) {
                $eligible = $this->isCouponEligibleToLimit($coupon, $promotion, $subject->getCustomer());
            }
        } elseif ($subject instanceof PromotionCouponsAwareSubjectInterface) {
            /** @var Collection $coupons */
            $coupons = $subject->getPromotionCoupons();
            if ($coupons->isEmpty()) {
                return false;
            }
            foreach ($coupons as $coupon) {
                if ($promotion === $coupon->getPromotion()) {
                    $eligible = $this->isCouponEligibleToLimit($coupon, $promotion, $subject->getCustomer());

                    break;
                }
            }
        } else {
            return false;
        }

        if ($eligible) {
            $this->dispatcher->dispatch(SyliusPromotionEvents::COUPON_ELIGIBLE, new GenericEvent($promotion));
        } else {
            $this->dispatcher->dispatch(SyliusPromotionEvents::COUPON_NOT_ELIGIBLE, new GenericEvent($promotion));
        }

        return $eligible;
    }

    private function isCouponEligibleToLimit(CouponInterface $coupon, PromotionInterface $promotion, CustomerInterface $customer = null)
    {
        if (!$coupon instanceof CouponInterface) {
            return true;
        }

        if (!$this->isCouponEligibleToCouponLimit($coupon)) {
            return false;
        }

        if (!$coupon->getPerCustomerUsageLimit()) {
            return true;
        }

        if (null === $customer && $coupon->getPerCustomerUsageLimit()) {
            return false;
        }

        return $this->isCouponEligible($coupon, $promotion, $customer);
    }

    private function isCouponEligibleToCouponLimit(CouponInterface $coupon)
    {
        if (!$coupon->getUsageLimit()) {
            return true;
        }

        $countPlacedOrders = $this->subjectRepository->countByCoupon($coupon);

        if ($countPlacedOrders < $coupon->getUsageLimit()) {
            return true;
        }

        return false;
    }

    private function isCouponEligible(CouponInterface $coupon, PromotionInterface $promotion, CustomerInterface $customer)
    {
        $countPlacedOrders = $this->subjectRepository->countByCustomerAndCoupon($customer, $coupon);

        if ($countPlacedOrders < $coupon->getPerCustomerUsageLimit()) {
            $this->dispatcher->dispatch(SyliusPromotionEvents::COUPON_ELIGIBLE, new GenericEvent($promotion));

            return true;
        }

        return false;
    }
}
