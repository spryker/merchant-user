<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Communication\Plugin\AclEntity;

use Spryker\Zed\AclEntityExtension\Dependency\Plugin\AclEntityDisablerPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\MerchantUser\Business\MerchantUserFacadeInterface getFacade()
 * @method \Spryker\Zed\MerchantUser\MerchantUserConfig getConfig()
 * @method \Spryker\Zed\MerchantUser\Communication\MerchantUserCommunicationFactory getFactory()
 */
class NoCurrentMerchantUserAclEntityDisablerPlugin extends AbstractPlugin implements AclEntityDisablerPluginInterface
{
    /**
     * {@inheritDoc}
     * - Disables AclEntity unless the current user is a merchant user, so only merchant users are scoped to their merchant.
     *
     * @api
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return !$this->getFacade()->hasCurrentMerchantUser();
    }
}
