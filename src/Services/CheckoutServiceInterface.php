<?php declare(strict_types=1);

namespace Amazd\Integration\Services;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface CheckoutServiceInterface
{
    public function loadCart(Request $request, SalesChannelContext $context);
}
