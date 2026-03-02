<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Business\Authenticator;

use DateTime;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;

class MerchantUserAuthenticator implements MerchantUserAuthenticatorInterface
{
    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface
     */
    protected $userFacade;

    public function __construct(MerchantUserToUserFacadeInterface $userFacade)
    {
        $this->userFacade = $userFacade;
    }

    public function authenticateMerchantUser(MerchantUserTransfer $merchantUserTransfer): void
    {
        $userTransfer = $merchantUserTransfer->requireUser()->getUserOrFail();
        $this->userFacade->setCurrentUser($userTransfer);
        $this->updateUserLastLoginDate($userTransfer);
    }

    protected function updateUserLastLoginDate(UserTransfer $userTransfer): void
    {
        $userTransfer->setLastLogin((new DateTime())->format(DateTime::ATOM));
        $this->userFacade->updateUser(clone $userTransfer);
    }
}
