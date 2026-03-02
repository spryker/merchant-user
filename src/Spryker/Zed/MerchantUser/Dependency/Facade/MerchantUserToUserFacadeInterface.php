<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantUser\Dependency\Facade;

use Generated\Shared\Transfer\UserCollectionTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserTransfer;

interface MerchantUserToUserFacadeInterface
{
    public function createUser(UserTransfer $userTransfer): UserTransfer;

    public function updateUser(UserTransfer $user): UserTransfer;

    /**
     * @param int $idUser
     *
     * @return bool
     */
    public function deactivateUser($idUser): bool;

    /**
     * @param int $idUser
     *
     * @return \Generated\Shared\Transfer\UserTransfer
     */
    public function removeUser($idUser): UserTransfer;

    public function getCurrentUser(): UserTransfer;

    /**
     * @param \Generated\Shared\Transfer\UserTransfer $user
     *
     * @return mixed
     */
    public function setCurrentUser(UserTransfer $user);

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function isValidPassword($password, $hash): bool;

    public function getUserCollection(UserCriteriaTransfer $userCriteriaTransfer): UserCollectionTransfer;

    public function hasCurrentUser(): bool;
}
