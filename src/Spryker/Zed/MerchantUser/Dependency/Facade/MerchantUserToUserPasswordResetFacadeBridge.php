<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Dependency\Facade;

use Generated\Shared\Transfer\UserPasswordResetRequestTransfer;

class MerchantUserToUserPasswordResetFacadeBridge implements MerchantUserToUserPasswordResetFacadeInterface
{
    /**
     * @var \Spryker\Zed\UserPasswordReset\Business\UserPasswordResetFacadeInterface
     */
    protected $userPasswordResetFacade;

    /**
     * @param \Spryker\Zed\UserPasswordReset\Business\UserPasswordResetFacadeInterface $userPasswordResetFacade
     */
    public function __construct($userPasswordResetFacade)
    {
        $this->userPasswordResetFacade = $userPasswordResetFacade;
    }

    public function requestPasswordReset(UserPasswordResetRequestTransfer $userPasswordResetRequestTransfer): bool
    {
        return $this->userPasswordResetFacade->requestPasswordReset($userPasswordResetRequestTransfer);
    }

    public function isValidPasswordResetToken(string $token): bool
    {
        return $this->userPasswordResetFacade->isValidPasswordResetToken($token);
    }

    public function setNewPassword(string $token, string $password): bool
    {
        return $this->userPasswordResetFacade->setNewPassword($token, $password);
    }
}
