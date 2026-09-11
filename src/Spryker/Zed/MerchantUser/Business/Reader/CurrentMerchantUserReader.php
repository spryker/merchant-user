<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Business\Reader;

use Generated\Shared\Transfer\MerchantCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Spryker\Zed\MerchantUser\Business\Exception\CurrentMerchantUserNotFoundException;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToMerchantFacadeInterface;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface;

class CurrentMerchantUserReader implements CurrentMerchantUserReaderInterface
{
    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface
     */
    protected $userFacade;

    /**
     * @var \Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface
     */
    protected $merchantUserRepository;

    /**
     * @var \Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToMerchantFacadeInterface
     */
    protected $merchantFacade;

    protected static ?MerchantUserTransfer $merchantUserTransfer = null;

    protected static ?int $idUserOfCachedMerchantUser = null;

    public function __construct(
        MerchantUserToUserFacadeInterface $userFacade,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserToMerchantFacadeInterface $merchantFacade
    ) {
        $this->userFacade = $userFacade;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantFacade = $merchantFacade;
    }

    /**
     * @throws \Spryker\Zed\MerchantUser\Business\Exception\CurrentMerchantUserNotFoundException
     *
     * @return \Generated\Shared\Transfer\MerchantUserTransfer
     */
    public function getCurrentMerchantUser(): MerchantUserTransfer
    {
        $merchantUserTransfer = $this->findCurrentMerchantUser($this->userFacade->getCurrentUser());

        if ($merchantUserTransfer === null) {
            throw new CurrentMerchantUserNotFoundException(
                'Current merchant user was not found',
            );
        }

        return $merchantUserTransfer;
    }

    public function hasCurrentMerchantUser(): bool
    {
        if (!$this->userFacade->hasCurrentUser()) {
            return false;
        }

        return $this->findCurrentMerchantUser($this->userFacade->getCurrentUser()) !== null;
    }

    protected function findCurrentMerchantUser(UserTransfer $userTransfer): ?MerchantUserTransfer
    {
        if (static::$merchantUserTransfer !== null && static::$idUserOfCachedMerchantUser === $userTransfer->getIdUser()) {
            return static::$merchantUserTransfer;
        }

        $merchantUserCriteriaTransfer = (new MerchantUserCriteriaTransfer())
            ->setIdUser($userTransfer->getIdUser());

        $merchantUserTransfers = $this->merchantUserRepository->getMerchantUsers($merchantUserCriteriaTransfer);

        if (count($merchantUserTransfers) === 0) {
            return null;
        }

        $merchantUserTransfer = reset($merchantUserTransfers);
        $merchantUserTransfer->setUser($userTransfer);

        static::$merchantUserTransfer = $this->expandWithMerchant($merchantUserTransfer);
        static::$idUserOfCachedMerchantUser = $userTransfer->getIdUser();

        return static::$merchantUserTransfer;
    }

    protected function expandWithMerchant(MerchantUserTransfer $merchantUserTransfer): MerchantUserTransfer
    {
        $merchantTransfer = $this->merchantFacade->findOne(
            (new MerchantCriteriaTransfer())->setIdMerchant($merchantUserTransfer->getIdMerchant()),
        );

        return $merchantUserTransfer->setMerchant($merchantTransfer);
    }
}
