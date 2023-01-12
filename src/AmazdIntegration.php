<?php declare(strict_types=1);

namespace Amazd\Integration;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class AmazdIntegration extends Plugin
{
    /**
     * @throws DBALException
     * @throws ConnectionException
     */
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);
    }
}
