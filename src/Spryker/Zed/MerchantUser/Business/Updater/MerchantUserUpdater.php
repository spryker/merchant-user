<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Business\Updater;

use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserResponseTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserConditionsTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserPasswordResetRequestTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserPasswordResetFacadeInterface;
use Spryker\Zed\MerchantUser\MerchantUserConfig;
use Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface;

class MerchantUserUpdater implements MerchantUserUpdaterInterface
{
    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_ACTIVE
     *
     * @var string
     */
    protected const USER_STATUS_ACTIVE = 'active';

    /**
     * @var string
     */
    protected const RESET_RASSWORD_PATH = '/security-merchant-portal-gui/password/reset';

    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_BLOCKED
     *
     * @var string
     */
    protected const USER_STATUS_BLOCKED = 'blocked';

    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface
     */
    protected $userFacade;

    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserPasswordResetFacadeInterface
     */
    protected $userPasswordResetFacade;

    /**
     * @var \Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface
     */
    protected $merchantUserRepository;

    /**
     * @var \Spryker\Zed\MerchantUser\MerchantUserConfig
     */
    protected $merchantUserConfig;

    public function __construct(
        MerchantUserToUserFacadeInterface $userFacade,
        MerchantUserToUserPasswordResetFacadeInterface $userPasswordResetFacade,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserConfig $merchantUserConfig
    ) {
        $this->userFacade = $userFacade;
        $this->userPasswordResetFacade = $userPasswordResetFacade;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserConfig = $merchantUserConfig;
    }

    public function update(MerchantUserTransfer $merchantUserTransfer): MerchantUserResponseTransfer
    {
        $merchantUserTransfer->requireUser();
        $merchantUserResponseTransfer = (new MerchantUserResponseTransfer())
            ->setIsSuccessful(true)
            ->setMerchantUser($merchantUserTransfer);

        $originalUserTransfer = $merchantUserTransfer->getIdUser()
            ? $this->findUserTransfer($merchantUserTransfer->getIdUserOrFail())
            : null;
        if (!$originalUserTransfer) {
            return $merchantUserResponseTransfer->setIsSuccessful(false);
        }

        $userTransfer = $this->userFacade->updateUser($merchantUserTransfer->getUserOrFail());

        $this->resetUserPassword($originalUserTransfer, $userTransfer);

        return $merchantUserResponseTransfer;
    }

    public function disable(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): void
    {
        $merchantUserTransfers = $this->merchantUserRepository->find($merchantUserCriteriaTransfer);

        foreach ($merchantUserTransfers as $merchantUserTransfer) {
            $this->userFacade->deactivateUser($merchantUserTransfer->getIdUserOrFail());
        }
    }

    protected function resetUserPassword(UserTransfer $originalUserTransfer, UserTransfer $updatedUserTransfer): void
    {
        if (
            $updatedUserTransfer->getStatus() === static::USER_STATUS_ACTIVE
            && $originalUserTransfer->getStatus() !== $updatedUserTransfer->getStatus()
        ) {
            $email = $updatedUserTransfer->getUsernameOrFail();
            $this->userPasswordResetFacade->requestPasswordReset(
                (new UserPasswordResetRequestTransfer())
                    ->setEmail($email)
                    ->setResetPasswordBaseUrl($this->merchantUserConfig->getMerchantPortalBaseUrl())
                    ->setResetPasswordPath(static::RESET_RASSWORD_PATH),
            );
        }
    }

    protected function findUserTransfer(int $idUser): ?UserTransfer
    {
        $userCriteriaTransfer = $this->createUserCriteriaTransfer($idUser);
        $userCollectionTransfer = $this->userFacade->getUserCollection($userCriteriaTransfer);

        return $userCollectionTransfer->getUsers()->getIterator()->current();
    }

    protected function createUserCriteriaTransfer(int $idUser): UserCriteriaTransfer
    {
        $userConditionsTransfer = (new UserConditionsTransfer())->addIdUser($idUser);

        return (new UserCriteriaTransfer())->setUserConditions($userConditionsTransfer);
    }
}
