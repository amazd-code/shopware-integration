<?php declare(strict_types=1);

namespace Amazd\Integration\Storefront\Controller;

use Amazd\Integration\Services\CheckoutServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class AmazdIntegrationController extends StorefrontController
{
    /**
     * @var CheckoutServiceInterface
     */
    private $checkoutService;

    public function __construct(CheckoutServiceInterface $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * @Route("/amazd-integration/checkout/{contextToken}", name="frontend.amazd.integration.checkout", options={"seo"="false"}, methods={"GET"}))
     */
    public function checkout(Request $request, SalesChannelContext $context): Response
    {
        try {
            $this->checkoutService->loadCart($request, $context);
            $this->addFlash('success', $this->trans('amazd-integration.cartLoaded'));
        } catch (\Exception $exception) {
            $this->checkoutService->debugError($request, $exception);
            $this->addFlash('danger', $this->trans('amazd-integration.cartNotFound'));
        }

        return $this->forwardToRoute('frontend.checkout.cart.page');
    }
}