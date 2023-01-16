<?php declare(strict_types=1);

namespace Amazd\Integration\Services;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
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

    private function object_to_array($obj)
    {
        //only process if it's an object or array being passed to the function
        if (is_object($obj) || is_array($obj)) {
            $ret = (array) $obj;
            foreach ($ret as &$item) {
                //recursively process EACH element regardless of type
                $item = $this->object_to_array($item);
            }
            return $ret;
        }
        //otherwise (i.e. for scalar values) return without modification
        else {
            return $obj;
        }
    }

    public function loadCart(Request $request, SalesChannelContext $salesChannelContext)
    {

        $contextToken = $request->attributes->get('contextToken');
        $accessToken = $request->attributes->get('accessToken');
        $restClient = new \GuzzleHttp\Client();

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
        $body = json_decode($cartResponse->getBody()->getContents());

        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        if (!$body->lineItems || !count($body->lineItems)) {
            throw new \Exception("Cart is empty");
        }

        $currentProductIds = $cart->getLineItems()->getReferenceIds();

        foreach ($body->lineItems as $item) {
            try {
                if (!$item->referencedId || in_array($item->referencedId, $currentProductIds)) continue;

                $lineItem = $this->lineItemFactory->create([
                    'type' => $item->type,
                    'referencedId' => $item->referencedId,
                    'quantity' => $item->quantity,
                ], $salesChannelContext);

                try {
                    $lineItem->setPayload($this->object_to_array($item->payload));
                } catch (\Exception $e) {
                }

                $this->cartService->add($cart, $lineItem, $salesChannelContext);
            } catch (\Exception $e) {
            }
        }

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