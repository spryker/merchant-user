<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\MerchantUser\Business;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\UserBuilder;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Orm\Zed\MerchantUser\Persistence\SpyMerchantUser;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToAuthFacadeInterface;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\MerchantUserDependencyProvider;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group MerchantUser
 * @group Business
 * @group Facade
 * @group MerchantUserFacadeTest
 * Add your own group annotations below this line
 */
class MerchantUserFacadeTest extends Unit
{
    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_BLOCKED
     */
    protected const USER_STATUS_BLOCKED = 'blocked';

    /**
     * @var \Generated\Shared\Transfer\MerchantUserTransfer
     */
    protected $merchantUserTransfer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeBridge
     */
    protected $userFacadeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToAuthFacadeBridge
     */
    protected $authFacadeMock;

    /**
     * @var \SprykerTest\Zed\MerchantUser\MerchantUserBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->authFacadeMock = $this->getMockBuilder(MerchantUserToAuthFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['requestPasswordReset'])
            ->getMockForAbstractClass();

        $this->userFacadeMock = $this->getMockBuilder(MerchantUserToUserFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findUser', 'updateUser', 'createUser'])
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testCreateReturnsTrueIfUserDoesNotExist(): void
    {
        // Arrange
        $userTransfer = (new UserBuilder())->build();
        $merchantTransfer = $this->tester->haveMerchant();
        $merchantUserTransfer = new MerchantUserTransfer();
        $merchantUserTransfer->setIdMerchant($merchantTransfer->getIdMerchant())->setUser($userTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->create($merchantUserTransfer);
        $merchantUserEntity = $this->tester->findMerchantUser(
            (new MerchantUserCriteriaTransfer())->setIdMerchantUser($merchantUserTransfer->getIdMerchantUser())
        );

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
        $this->assertInstanceOf(SpyMerchantUser::class, $merchantUserEntity);
    }

    /**
     * @return void
     */
    public function testCreateReturnsTrueIfUserExist(): void
    {
        // Arrange
        $userTransfer = (new UserBuilder())->build();
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = new MerchantUserTransfer();

        $merchantUserTransfer->setIdMerchant($merchantTransfer->getIdMerchant())
            ->setUser($userTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->create($merchantUserTransfer);
        $merchantUserEntity = $this->tester->findMerchantUser(
            (new MerchantUserCriteriaTransfer())->setIdMerchantUser($merchantUserTransfer->getIdMerchantUser())
        );

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
        $this->assertInstanceOf(SpyMerchantUser::class, $merchantUserEntity);
    }

    /**
     * @return void
     */
    public function testCreateReturnsFalseIfUserAlreadyHasMerchant(): void
    {
        // Arrange
        $newUserTransfer = (new UserBuilder())->build();
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => $newUserTransfer->getUsername(),
        ]);
        $merchantUserTransfer = new MerchantUserTransfer();

        $merchantOneTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantTwoTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_2@spryker.com']);

        $this->tester->haveMerchantUser($merchantOneTransfer, $userTransfer);

        $merchantUserTransfer->setIdMerchant($merchantTwoTransfer->getIdMerchant())
            ->setUser($newUserTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->create($merchantUserTransfer);

        // Assert
        $this->assertFalse($merchantUserResponseTransfer->getIsSuccessful());
        $this->assertSame(
            'A user with the same email is already connected to another merchant.',
            $merchantUserResponseTransfer->getErrors()[0]->getMessage()
        );
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ]);

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);

        $userCriteriaTransfer = (new UserCriteriaTransfer())->setIdUser($userTransfer->getIdUser());
        $this->userFacadeMock->expects($this->once())->method('findUser')
            ->with($userCriteriaTransfer)
            ->willReturn($userTransfer);

        $this->userFacadeMock->expects($this->once())->method('updateUser')
            ->with($userTransfer)
            ->willReturn($userTransfer);

        $this->authFacadeMock->expects($this->never())->method('requestPasswordReset');

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->update($merchantUserTransfer);

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testUpdateWithNewActiveStatus(): void
    {
        // Arrange
        $newUserTransfer = (new UserBuilder())->build();
        $this->initializeFacadeMocks();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ])->setStatus('blocked');

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);

        $userCriteriaTransfer = (new UserCriteriaTransfer())->setIdUser($userTransfer->getIdUser());
        $this->userFacadeMock->expects($this->once())->method('findUser')
            ->with($userCriteriaTransfer)
            ->willReturn($userTransfer);

        $this->userFacadeMock->expects($this->once())->method('updateUser')
            ->with($userTransfer)
            ->willReturn($newUserTransfer->setStatus('active'));

        $this->authFacadeMock->expects($this->once())->method('requestPasswordReset');

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->update($merchantUserTransfer);

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testUpdateWithNewBlockedStatus(): void
    {
        // Arrange
        $newUserTransfer = (new UserBuilder())->build();
        $this->initializeFacadeMocks();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ])->setStatus('blocked');

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);

        $userCriteriaTransfer = (new UserCriteriaTransfer())->setIdUser($userTransfer->getIdUser());
        $this->userFacadeMock->expects($this->once())->method('findUser')
            ->with($userCriteriaTransfer)
            ->willReturn($userTransfer);

        $this->userFacadeMock->expects($this->once())->method('updateUser')
            ->with($userTransfer)
            ->willReturn($newUserTransfer->setStatus('active'));

        $this->authFacadeMock->expects($this->once())->method('requestPasswordReset');

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->update($merchantUserTransfer);

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
    }

    /**
     * @dataProvider getMerchantUserPositiveScenarioDataProvider
     *
     * @param string[] $merchantUserCriteriaKeys
     * @param bool $isUserInCriteria
     *
     * @return void
     */
    public function testFindMerchantUserReturnsTransferWithCorrectCriteria(
        array $merchantUserCriteriaKeys,
        bool $isUserInCriteria
    ): void {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ]);
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        $merchantUserCriteriaData = [
            MerchantUserCriteriaTransfer::ID_MERCHANT_USER => $merchantUserTransfer->getIdMerchantUser(),
            MerchantUserCriteriaTransfer::ID_MERCHANT => $merchantTransfer->getIdMerchant(),
            MerchantUserCriteriaTransfer::ID_USER => $userTransfer->getIdUser(),
            MerchantUserCriteriaTransfer::WITH_USER => true,
        ];
        $merchantUserCriteriaData = array_intersect_key(
            $merchantUserCriteriaData,
            array_flip($merchantUserCriteriaKeys)
        );

        $merchantUserCriteriaTransfer = (new MerchantUserCriteriaTransfer())
            ->fromArray($merchantUserCriteriaData);

        // Act
        $foundMerchantUserTransfer = $this->tester
            ->getFacade()
            ->findMerchantUser($merchantUserCriteriaTransfer);

        // Assert
        $this->assertSame(
            $merchantUserTransfer->getIdMerchantUser(),
            $foundMerchantUserTransfer->getIdMerchantUser()
        );

        if ($isUserInCriteria) {
            $this->assertInstanceOf(UserTransfer::class, $foundMerchantUserTransfer->getUser());
        }
    }

    /**
     * @dataProvider getMerchantUserNegativeScenarioDataProvider
     *
     * @param array $merchantUserCriteriaData
     *
     * @return void
     */
    public function testFindMerchantUserReturnsNullWithWrongCriteria(
        array $merchantUserCriteriaData
    ): void {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ]);
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        $merchantUserCriteriaTransfer = (new MerchantUserCriteriaTransfer())
            ->fromArray($merchantUserCriteriaData);

        // Act
        $foundMerchantUserTransfer = $this->tester
            ->getFacade()
            ->findMerchantUser($merchantUserCriteriaTransfer);

        // Assert
        $this->assertNull($foundMerchantUserTransfer);
    }

    /**
     * @return void
     */
    public function testDisableMerchantUsersByMerchantId(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $userOneTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker_one.com',
        ]);

        $userTwoTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker_two.com',
        ]);

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);

        $this->tester->haveMerchantUser($merchantTransfer, $userOneTransfer);
        $this->tester->haveMerchantUser($merchantTransfer, $userTwoTransfer);

        $this->userFacadeMock->expects($this->exactly(2))->method('deactivateUser');

        // Act
        $this->tester->getFacade()->disableMerchantUsers(
            (new MerchantUserCriteriaTransfer())->setIdMerchant($merchantTransfer->getIdMerchant())
        );
    }

    /**
     * @return void
     */
    protected function initializeFacadeMocks(): void
    {
        $this->tester->setDependency(MerchantUserDependencyProvider::FACADE_AUTH, $this->authFacadeMock);
        $this->tester->setDependency(MerchantUserDependencyProvider::FACADE_USER, $this->userFacadeMock);
    }

    /**
     * @return array
     */
    public function getMerchantUserPositiveScenarioDataProvider(): array
    {
        return [
            'by id merchant user' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT_USER,
                ],
                'isUserInCriteria' => false,
            ],
            'by id merchant' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT,
                ],
                'isUserInCriteria' => false,
            ],
            'by id user' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_USER,
                ],
                'isUserInCriteria' => false,
            ],
            'with user' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT_USER,
                    MerchantUserCriteriaTransfer::ID_USER,
                    MerchantUserCriteriaTransfer::WITH_USER,
                ],
                'isUserInCriteria' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getMerchantUserNegativeScenarioDataProvider(): array
    {
        return [
            'by id merchant user' => [
                'merchantUserCriteriaData' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT_USER => 0,
                ],
            ],
            'by id merchant' => [
                'merchantUserCriteriaData' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT => 0,
                ],
            ],
            'by id user' => [
                'merchantUserCriteriaData' => [
                    MerchantUserCriteriaTransfer::ID_USER => 0,
                ],
            ],
        ];
    }
}
