<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Business\Updater;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserResponseTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToAuthFacadeInterface;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface;

class MerchantUserUpdater implements MerchantUserUpdaterInterface
{
    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_ACTIVE
     */
    protected const USER_STATUS_ACTIVE = 'active';

    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_BLOCKED
     */
    protected const USER_STATUS_BLOCKED = 'blocked';

    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface
     */
    protected $userFacade;

    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToAuthFacadeInterface
     */
    protected $authFacade;

    /**
     * @var \Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface
     */
    protected $merchantUserRepository;

    /**
     * @param \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface $userFacade
     * @param \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToAuthFacadeInterface $authFacade
     * @param \Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface $merchantUserRepository
     */
    public function __construct(
        MerchantUserToUserFacadeInterface $userFacade,
        MerchantUserToAuthFacadeInterface $authFacade,
        MerchantUserRepositoryInterface $merchantUserRepository
    ) {
        $this->userFacade = $userFacade;
        $this->authFacade = $authFacade;
        $this->merchantUserRepository = $merchantUserRepository;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantUserTransfer $merchantUserTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantUserResponseTransfer
     */
    public function update(MerchantUserTransfer $merchantUserTransfer): MerchantUserResponseTransfer
    {
        $merchantUserTransfer->requireUser();

        $originalUserTransfer = $this->userFacade->getUserById($merchantUserTransfer->getIdUser());

        $userTransfer = $this->userFacade->updateUser($merchantUserTransfer->getUser());

        $this->resetUserPassword($originalUserTransfer, $userTransfer);

        return (new MerchantUserResponseTransfer())
            ->setIsSuccessful(true)
            ->setMerchantUser($merchantUserTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer
     *
     * @return void
     */
    public function disable(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): void
    {
        $merchantUserTransfers = $this->merchantUserRepository->find($merchantUserCriteriaTransfer);

        foreach ($merchantUserTransfers as $merchantUserTransfer) {
            $this->userFacade->deactivateUser($merchantUserTransfer->getIdUser());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\UserTransfer $originalUserTransfer
     * @param \Generated\Shared\Transfer\UserTransfer $updatedUserTransfer
     *
     * @return void
     */
    protected function resetUserPassword(UserTransfer $originalUserTransfer, UserTransfer $updatedUserTransfer): void
    {
        if (
            $updatedUserTransfer->getStatus() === static::USER_STATUS_ACTIVE
            && $originalUserTransfer->getStatus() !== $updatedUserTransfer->getStatus()
        ) {
            $this->authFacade->requestPasswordReset($updatedUserTransfer->getUsername());
        }
    }
}
