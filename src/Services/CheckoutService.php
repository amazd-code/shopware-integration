<?php declare(strict_types=1);

namespace Amazd\Integration\Services;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Symfony\Component\HttpFoundation\Request;

class CheckoutService implements CheckoutServiceInterface
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var LineItemFactoryRegistry
     */
    private $lineItemFactory;

    public function __construct(
        CartService $cartService,
        LineItemFactoryRegistry $lineItemFactory
    ) {
        $this->cartService = $cartService;
        $this->lineItemFactory = $lineItemFactory;
    }

    public function loadCart(Request $request, SalesChannelContext $salesChannelContext)
    {
        $contextToken = $request->attributes->get('contextToken');
        $accessToken = $salesChannelContext->getSalesChannel()->getAccessKey();
        $restClient = new \GuzzleHttp\Client();

        $this->debugInfo($request, 'Sales channel: ' . $salesChannelContext->getSalesChannel()->getName());

        $cartRequest = new \GuzzleHttp\Psr7\Request(
            'GET',
            getenv('APP_URL') . '/store-api/checkout/cart',
            [
                'Content-Type' => 'application/json',
                'sw-access-key' =>  $accessToken,
                'sw-context-token' => $contextToken
            ],
        );
        $cartResponse = $restClient->send($cartRequest);
        $body = json_decode($cartResponse->getBody()->getContents(), true);

        $this->debugInfo($request, 'Line items count: ' . ($body['lineItems'] && count($body['lineItems'])));

        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        if (!$body['lineItems'] || !count($body['lineItems'])) {
            throw new \Exception("Cart is empty");
        }

        $currentProductIds = $cart->getLineItems()->getReferenceIds();

        foreach ($body['lineItems'] as $item) {
            try {
                if (!$item['referencedId']) continue;

                if (in_array($item['referencedId'], $currentProductIds)) {
                    $itemId = $cart->getLineItems()->filter(function (LineItem $lineItem) use ($item) {
                        return $lineItem->getReferencedId() === $item['referencedId'];
                    })->first()->getId();

                    // Remove to let updated item be re-created in the cart
                    $cart->getLineItems()->remove($itemId);
                }

                $lineItem = $this->lineItemFactory->create([
                    'type' => $item['type'],
                    'referencedId' => $item['referencedId'],
                    'quantity' => $item['quantity'],
                ], $salesChannelContext);

                try {
                    $lineItem->setPayload($item['payload']);
                } catch (\Exception $e) {
                    $this->debugError($request, $e);
                }

                $this->cartService->add($cart, $lineItem, $salesChannelContext);
            } catch (\Exception $e) {
                $this->debugError($request, $e);
            }
        }

        if (!$request->query->has('keepCart')) {
            $deleteRequest = new \GuzzleHttp\Psr7\Request(
                'DELETE',
                getenv('APP_URL') . '/store-api/checkout/cart',
                [
                    'Content-Type' => 'application/json',
                    'sw-access-key' =>  $accessToken,
                    'sw-context-token' => $contextToken
                ],
            );
            $restClient->send($deleteRequest);
        }
    }

    public function debugError(Request $request, $message)
    {
        $isDebug = $request->query->has('debug');
        if (!$isDebug) return;

        $session = $request->getSession();
        $session->getFlashBag()->add('danger', $message);
    }

    public function debugInfo(Request $request, $message)
    {
        $isDebug = $request->query->has('debug');
        if (!$isDebug) return;

        $session = $request->getSession();
        $session->getFlashBag()->add('notice', $message);
    }
}
