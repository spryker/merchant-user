<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\MerchantUser\Business\Reader;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserTransfer;
use ReflectionProperty;
use Spryker\Zed\MerchantUser\Business\Reader\CurrentMerchantUserReader;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToMerchantFacadeInterface;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\Persistence\MerchantUserRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group MerchantUser
 * @group Business
 * @group Reader
 * @group CurrentMerchantUserReaderTest
 * Add your own group annotations below this line
 */
class CurrentMerchantUserReaderTest extends Unit
{
    protected const int ID_USER_FIRST = 3;

    protected const int ID_USER_SECOND = 5;

    protected const int ID_MERCHANT_FIRST = 6;

    protected const int ID_MERCHANT_SECOND = 7;

    /**
     * Every criteria the reader queried merchant users with.
     *
     * @var array<\Generated\Shared\Transfer\MerchantUserCriteriaTransfer>
     */
    protected array $merchantUserCriteriaTransfers = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetCache();
    }

    protected function tearDown(): void
    {
        $this->resetCache();

        parent::tearDown();
    }

    public function testGivenTwoUsersInOneProcessWhenReadingCurrentMerchantUserThenEachGetsTheirOwnMerchant(): void
    {
        // Arrange
        $currentIdUser = static::ID_USER_FIRST;
        $reader = $this->createReader($currentIdUser);

        // Act — one PHP process serving two identities: a functional API test, a worker, or two
        // Backend API requests handled by the same worker.
        $firstMerchantUserTransfer = $reader->getCurrentMerchantUser();
        $currentIdUser = static::ID_USER_SECOND;
        $secondMerchantUserTransfer = $reader->getCurrentMerchantUser();

        // Assert
        $this->assertSame(static::ID_MERCHANT_FIRST, $firstMerchantUserTransfer->getIdMerchant());
        $this->assertSame(static::ID_MERCHANT_SECOND, $secondMerchantUserTransfer->getIdMerchant());
    }

    public function testGivenTheSameUserWhenReadingCurrentMerchantUserTwiceThenTheRepositoryIsQueriedOnce(): void
    {
        // Arrange
        $currentIdUser = static::ID_USER_FIRST;
        $queryCount = 0;
        $reader = $this->createReader($currentIdUser, $queryCount);

        // Act
        $reader->getCurrentMerchantUser();
        $reader->getCurrentMerchantUser();

        // Assert
        $this->assertSame(1, $queryCount);
    }

    /**
     * Merchant approval is not a precondition for having a current merchant user: a merchant user
     * whose merchant is still awaiting approval must be able to log in and work with their own
     * merchant's data, which is what onboarding needs. The Merchant Portal's own login provider
     * decides separately whether it admits such a user
     * ({@see \Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\Provider\MerchantUserProvider}),
     * and this reader must not impose a second, wider-reaching gate on top of it.
     */
    public function testReadsCurrentMerchantUserRegardlessOfMerchantStatus(): void
    {
        // Arrange
        $currentIdUser = static::ID_USER_FIRST;
        $reader = $this->createReader($currentIdUser);

        // Act
        $reader->getCurrentMerchantUser();

        // Assert
        $this->assertCount(1, $this->merchantUserCriteriaTransfers);
        $this->assertNull(
            $this->merchantUserCriteriaTransfers[0]->getMerchantStatus(),
            'The lookup must not constrain the merchant status.',
        );
    }

    public function testHasCurrentMerchantUserIsFalseWithoutCurrentUser(): void
    {
        // Arrange
        $currentIdUser = static::ID_USER_FIRST;
        $queryCount = 0;
        $reader = $this->createReader($currentIdUser, $queryCount, false);

        // Act
        $hasCurrentMerchantUser = $reader->hasCurrentMerchantUser();

        // Assert
        $this->assertFalse($hasCurrentMerchantUser);
        $this->assertSame(0, $queryCount);
    }

    public function testHasCurrentMerchantUserIsFalseForUserWithoutMerchantUser(): void
    {
        // Arrange
        $currentIdUser = static::ID_USER_FIRST;
        $queryCount = 0;
        $reader = $this->createReader($currentIdUser, $queryCount, true, false);

        // Act
        $hasCurrentMerchantUser = $reader->hasCurrentMerchantUser();

        // Assert
        $this->assertFalse($hasCurrentMerchantUser);
    }

    public function testHasCurrentMerchantUserIsTrueAndSharesTheMemoWithTheGetter(): void
    {
        // Arrange
        $currentIdUser = static::ID_USER_FIRST;
        $queryCount = 0;
        $reader = $this->createReader($currentIdUser, $queryCount);

        // Act
        $hasCurrentMerchantUser = $reader->hasCurrentMerchantUser();
        $reader->getCurrentMerchantUser();

        // Assert
        $this->assertTrue($hasCurrentMerchantUser);
        $this->assertSame(1, $queryCount);
    }

    protected function createReader(
        int &$currentIdUser,
        int &$queryCount = 0,
        bool $hasCurrentUser = true,
        bool $hasMerchantUser = true
    ): CurrentMerchantUserReader {
        return new CurrentMerchantUserReader(
            Stub::makeEmpty(MerchantUserToUserFacadeInterface::class, [
                'hasCurrentUser' => fn (): bool => $hasCurrentUser,
                // A long closure, not an arrow function: the acting user changes between calls and
                // an arrow function would capture the id by value.
                'getCurrentUser' => function () use (&$currentIdUser): UserTransfer {
                    return (new UserTransfer())->setIdUser($currentIdUser);
                },
            ]),
            Stub::makeEmpty(MerchantUserRepositoryInterface::class, [
                'getMerchantUsers' => function (MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer) use (
                    &$currentIdUser,
                    &$queryCount,
                    $hasMerchantUser,
                ): array {
                    $queryCount++;
                    $this->merchantUserCriteriaTransfers[] = $merchantUserCriteriaTransfer;

                    if (!$hasMerchantUser) {
                        return [];
                    }

                    return [
                        (new MerchantUserTransfer())
                            ->setIdUser($currentIdUser)
                            ->setIdMerchant($this->resolveIdMerchant($currentIdUser)),
                    ];
                },
            ]),
            Stub::makeEmpty(MerchantUserToMerchantFacadeInterface::class, [
                'findOne' => fn (): ?MerchantTransfer => new MerchantTransfer(),
            ]),
        );
    }

    protected function resolveIdMerchant(int $idUser): int
    {
        return $idUser === static::ID_USER_FIRST ? static::ID_MERCHANT_FIRST : static::ID_MERCHANT_SECOND;
    }

    protected function resetCache(): void
    {
        (new ReflectionProperty(CurrentMerchantUserReader::class, 'merchantUserTransfer'))->setValue(null, null);
        (new ReflectionProperty(CurrentMerchantUserReader::class, 'idUserOfCachedMerchantUser'))->setValue(null, null);
    }
}
