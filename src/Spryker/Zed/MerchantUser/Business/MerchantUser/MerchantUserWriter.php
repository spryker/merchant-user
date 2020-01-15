<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Business\MerchantUser;

use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\MerchantUserResponseTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Orm\Zed\User\Persistence\Map\SpyUserTableMap;
use Spryker\Service\UtilText\UtilTextService;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\Persistence\MerchantUserEntityManagerInterface;
use Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface;

class MerchantUserWriter implements MerchantUserWriterInterface
{
    protected const PASSWORD_LENGTH = 8;

    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface
     */
    protected $userFacade;

    /**
     * @var \Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface
     */
    protected $merchantUserRepository;

    /**
     * @var \Spryker\Zed\MerchantUser\Persistence\MerchantUserEntityManagerInterface
     */
    protected $merchantUserEntityManager;

    /**
     * @param \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface $userFacade
     * @param \Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface $merchantUserRepository
     * @param \Spryker\Zed\MerchantUser\Persistence\MerchantUserEntityManagerInterface $merchantUserEntityManager
     */
    public function __construct(
        MerchantUserToUserFacadeInterface $userFacade,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserEntityManagerInterface $merchantUserEntityManager
    ) {
        $this->userFacade = $userFacade;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantUserEntityManager = $merchantUserEntityManager;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantUserResponseTransfer
     */
    public function createMerchantUserByMerchant(MerchantTransfer $merchantTransfer): MerchantUserResponseTransfer
    {
        $merchantTransfer->requireEmail()
            ->requireMerchantProfile();

        $merchantUserResponseTransfer = new MerchantUserResponseTransfer();

        $merchantUserTransferByMerchant = $this->merchantUserRepository->findOne(
            (new MerchantUserCriteriaFilterTransfer())->setIdMerchant($merchantTransfer->getIdMerchant())
        );
        if ($merchantUserTransferByMerchant) {
            $userTransfer = $this->userFacade->getUserById($merchantUserTransferByMerchant->getIdUser());
            $this->userFacade->updateUser($this->fillUserTransferFromMerchantTransfer($userTransfer, $merchantTransfer));

            return $merchantUserResponseTransfer
                ->setIsSuccess(true)
                ->setMerchantUser($merchantUserTransferByMerchant->setUser($userTransfer));
        }

        $userTransfer = $this->getUserTransferByMerchantTransfer($merchantTransfer);

        $merchantUserTransferByUser = $this->merchantUserRepository->findOne(
            (new MerchantUserCriteriaFilterTransfer())->setIdUser($userTransfer->getIdUser())
        );
        if (!$merchantUserTransferByUser) {
            $merchantUserTransferByUser = $this->merchantUserEntityManager->createMerchantUser(
                (new MerchantUserTransfer())
                    ->setIdMerchant($merchantTransfer->getIdMerchant())
                    ->setIdUser($userTransfer->getIdUser())
            );
        }

        if ($merchantUserTransferByUser->getIdMerchant() !== $merchantTransfer->getIdMerchant()) {
            return $merchantUserResponseTransfer
                ->setIsSuccess(false)
                ->addError(
                    (new MessageTransfer())
                        ->setMessage(sprintf('A user with email %s is already connected with another merchant', $merchantTransfer->getEmail()))
                );
        }

        return $merchantUserResponseTransfer
            ->setIsSuccess(true)
            ->setMerchantUser($merchantUserTransferByUser->setUser($userTransfer));
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return \Generated\Shared\Transfer\UserTransfer
     */
    protected function getUserTransferByMerchantTransfer(MerchantTransfer $merchantTransfer): UserTransfer
    {
        if (!$this->userFacade->hasUserByUsername($merchantTransfer->getEmail())) {
            return $this->createUserByMerchant($merchantTransfer);
        }

        $userTransfer = $this->userFacade->getUserByUsername($merchantTransfer->getEmail());
        $this->userFacade->updateUser($this->fillUserTransferFromMerchantTransfer($userTransfer, $merchantTransfer));

        return $userTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return \Generated\Shared\Transfer\UserTransfer
     */
    protected function createUserByMerchant(MerchantTransfer $merchantTransfer): UserTransfer
    {
        $utilTextService = new UtilTextService();
        $userTransfer = $this->fillUserTransferFromMerchantTransfer(new UserTransfer(), $merchantTransfer)
            ->setPassword($utilTextService->generateRandomString(static::PASSWORD_LENGTH))
            ->setStatus(SpyUserTableMap::COL_STATUS_BLOCKED);

        return $this->userFacade->createUser($userTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\UserTransfer $userTransfer
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return \Generated\Shared\Transfer\UserTransfer
     */
    protected function fillUserTransferFromMerchantTransfer(UserTransfer $userTransfer, MerchantTransfer $merchantTransfer): UserTransfer
    {
        return $userTransfer
            ->setFirstName($merchantTransfer->getMerchantProfile()->getContactPersonFirstName())
            ->setLastName($merchantTransfer->getMerchantProfile()->getContactPersonLastName())
            ->setUsername($merchantTransfer->getEmail());
    }
}
