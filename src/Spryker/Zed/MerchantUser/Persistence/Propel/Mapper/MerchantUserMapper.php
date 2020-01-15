<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\MerchantUserTransfer;
use Orm\Zed\MerchantUser\Persistence\SpyMerchantUser;

class MerchantUserMapper
{
    /**
     * @param \Orm\Zed\MerchantUser\Persistence\SpyMerchantUser $merchantUserEntity
     * @param \Generated\Shared\Transfer\MerchantUserTransfer $merchantUserTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantUserTransfer
     */
    public function mapMerchantUserEntityToMerchantUserTransfer(
        SpyMerchantUser $merchantUserEntity,
        MerchantUserTransfer $merchantUserTransfer
    ): MerchantUserTransfer {
        return $merchantUserTransfer->setIdMerchant($merchantUserEntity->getFkMerchant())
            ->setIdUser($merchantUserEntity->getFkUser())
            ->setIdMerchantUser($merchantUserEntity->getIdMerchantUser());
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantUserTransfer $merchantUserTransfer
     * @param \Orm\Zed\MerchantUser\Persistence\SpyMerchantUser $merchantUserEntity
     *
     * @return \Orm\Zed\MerchantUser\Persistence\SpyMerchantUser
     */
    public function mapMerchantUserTransferToMerchantUserEntity(
        MerchantUserTransfer $merchantUserTransfer,
        SpyMerchantUser $merchantUserEntity
    ): SpyMerchantUser {
        return $merchantUserEntity->setIdMerchantUser($merchantUserTransfer->getIdMerchantUser())
            ->setFkUser($merchantUserTransfer->getIdUser())
            ->setFkMerchant($merchantUserTransfer->getIdMerchant());
    }
}
