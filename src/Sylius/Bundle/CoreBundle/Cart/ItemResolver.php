<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Cart;

use Sylius\Component\Addressing\Checker\RestrictedZoneCheckerInterface;
use Sylius\Component\Cart\Model\CartItemInterface;
use Sylius\Component\Cart\Provider\CartProviderInterface;
use Sylius\Component\Cart\Resolver\ItemResolverInterface;
use Sylius\Component\Cart\Resolver\ItemResolvingException;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Pricing\Calculator\DelegatingCalculatorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Item resolver for cart bundle.
 * Returns proper item objects for cart add and remove actions.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 * @author Saša Stamenković <umpirsky@gmail.com>
 */
class ItemResolver implements ItemResolverInterface
{
    /**
     * Cart provider.
     *
     * @var CartProviderInterface
     */
    protected $cartProvider;

    /**
     * Prica calculator.
     *
     * @var DelegatingCalculatorInterface
     */
    protected $priceCalculator;

    /**
     * Product repository.
     *
     * @var RepositoryInterface
     */
    protected $productRepository;

    /**
     * Form factory.
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Stock availability checker.
     *
     * @var AvailabilityCheckerInterface
     */
    protected $availabilityChecker;

    /**
     * Restricted zone checker.
     *
     * @var RestrictedZoneCheckerInterface
     */
    protected $restrictedZoneChecker;

    /**
     * Constructor.
     *
     * @param CartProviderInterface          $cartProvider
     * @param RepositoryInterface            $productRepository
     * @param FormFactoryInterface           $formFactory
     * @param AvailabilityCheckerInterface   $availabilityChecker
     * @param RestrictedZoneCheckerInterface $restrictedZoneChecker
     * @param DelegatingCalculatorInterface  $priceCalculator
     */
    public function __construct(
        CartProviderInterface          $cartProvider,
        RepositoryInterface            $productRepository,
        FormFactoryInterface           $formFactory,
        AvailabilityCheckerInterface   $availabilityChecker,
        RestrictedZoneCheckerInterface $restrictedZoneChecker,
        DelegatingCalculatorInterface  $priceCalculator
    )
    {
        $this->cartProvider = $cartProvider;
        $this->productRepository = $productRepository;
        $this->formFactory = $formFactory;
        $this->availabilityChecker = $availabilityChecker;
        $this->restrictedZoneChecker = $restrictedZoneChecker;
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(CartItemInterface $item, $data, $iteration = 0)
    {
        $id = $this->resolveItemIdentifier($data);

        if (!$product = $this->productRepository->find($id)) {
            throw new ItemResolvingException('Requested product was not found.');
        }

        if ($this->restrictedZoneChecker->isRestricted($product)) {
            throw new ItemResolvingException('Selected item is not available in your country.');
        }

        // We use forms to easily set the quantity and pick variant but you can do here whatever is required to create the item.
        $form = $this->formFactory->create('sylius_cart_item', $item, array('product' => $product));

        // When iterating over multiple variants we need to transform each iteration to single item for the form to work
        $data = $this->transformIterationToRegularData($data, $iteration);

        $form->submit($data);

        // If our product has no variants, we simply set the master variant of it.
        if (!$product->hasVariants()) {
            $item->setVariant($product->getMasterVariant());
        }

        $variant = $item->getVariant();

        // If all is ok with form, quantity and other stuff, simply return the item.
        if (!$form->isValid() || null === $variant) {
            throw new ItemResolvingException('Submitted form is invalid.');
        }

        $cart = $this->cartProvider->getCart();
        $quantity = $item->getQuantity();

        $context = array('quantity' => $quantity);

        if (null !== $user = $cart->getUser()) {
            $context['groups'] = $user->getGroups()->toArray();
        }

        $item->setUnitPrice($this->priceCalculator->calculate($variant, $context));

        foreach ($cart->getItems() as $cartItem) {
            if ($cartItem->equals($item)) {
                $quantity += $cartItem->getQuantity();
                break;
            }
        }

        if (!$this->availabilityChecker->isStockSufficient($variant, $quantity)) {
            throw new ItemResolvingException('Selected item is out of stock.');
        }

        return $item;
    }

    /**
     * Here we resolve the item identifier that is going to be added into the cart.
     *
     * @param mixed $request
     *
     * @return string|integer
     *
     * @throws ItemResolvingException
     */
    public function resolveItemIdentifier($request)
    {
        if (!$request instanceof Request) {
            throw new ItemResolvingException('Invalid request data.');
        }

        if (!$request->isMethod('POST') && !$request->isMethod('PUT')) {
            throw new ItemResolvingException('Invalid request method.');
        }

        /*
         * We're getting here product id via query but you can easily override route
         * pattern and use attributes, which are available through request object.
         */
        if (!$id = $request->get('id')) {
            throw new ItemResolvingException('Error while trying to add item to cart.');
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function countIterations($request)
    {
        if (!$request instanceof Request) {
            throw new ItemResolvingException('Invalid request data.');
        }

        if (!$this->isMultipleRequest($request)) {
            return 1;
        }

        $submittedVariants = $request->request->get('sylius_cart_item[variant]', null, true);
        if ($submittedVariants === null || !is_array($submittedVariants)) {
            throw new ItemResolvingException('Submitted variants missing from request or it has unexpected format.');
        }

        return count($submittedVariants);
    }

    /**
     * @param Request $data
     * @param $iteration
     * @return Request
     * @throws ItemResolvingException
     */
    protected function transformIterationToRegularData(Request $data, $iteration)
    {
        if (!$this->isMultipleRequest($data)) {
            return $data;
        }

        $submittedVariants = $data->request->get('sylius_cart_item[variant]', null, true);
        reset($submittedVariants);
        for ($i = 0; $i < $iteration; $i++) {
            if (next($submittedVariants) === false) {
                throw new ItemResolvingException('There are more iterations than submitted variants.');
            }
        }

        return $this->transformData($data, key($submittedVariants));
    }


    /**
     * Checks whether we will be resolving multiple variants from a request
     *
     * @param Request $request
     * @return bool
     */
    protected function isMultipleRequest(Request $request)
    {
        return $request->request->getAlnum('variant_types', 'default') === 'multiple';
    }

    /**
     * Transforms request with multiple variants to a request with single variant (based on $variantId)
     *
     * The new request <b>must</b> be cloned first if it changes any value.
     *
     * @param Request $original the original request
     * @param int $variantId
     * @return Request
     */
    protected function transformData(Request $original, $variantId)
    {
        $clone = clone $original;
        $clone->request->set('sylius_cart_item', array(
            'variant' => $variantId,
            'quantity' => $original->request->getInt('sylius_cart_item[variant][' . $variantId . '][quantity]', 1, true),
            '_token' => $original->request->get('sylius_cart_item[_token]', null, true),
        ));
        return $clone;
    }
}
