<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\MerchantUser\Communication\Plugin\AclEntity;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\MerchantTransfer;
use Spryker\Zed\MerchantUser\Communication\Plugin\AclEntity\NoCurrentMerchantUserAclEntityDisablerPlugin;
use SprykerTest\Zed\MerchantUser\MerchantUserCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group MerchantUser
 * @group Communication
 * @group Plugin
 * @group AclEntity
 * @group NoCurrentMerchantUserAclEntityDisablerPluginTest
 * Add your own group annotations below this line
 */
class NoCurrentMerchantUserAclEntityDisablerPluginTest extends Unit
{
    protected const string MERCHANT_STATUS_WAITING_FOR_APPROVAL = 'waiting-for-approval';

    protected MerchantUserCommunicationTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->getLocator()->user()->facade()->resetCurrentUser();
    }

    protected function tearDown(): void
    {
        $this->tester->getLocator()->user()->facade()->resetCurrentUser();

        parent::tearDown();
    }

    public function testIsDisabledWhenNoUserIsActing(): void
    {
        // Act
        $isDisabled = (new NoCurrentMerchantUserAclEntityDisablerPlugin())->isDisabled();

        // Assert
        $this->assertTrue($isDisabled);
    }

    public function testIsDisabledWhenTheActingUserHasNoMerchantUser(): void
    {
        // Arrange
        $this->tester->getLocator()->user()->facade()->setCurrentUser($this->tester->haveUser());

        // Act
        $isDisabled = (new NoCurrentMerchantUserAclEntityDisablerPlugin())->isDisabled();

        // Assert
        $this->assertTrue($isDisabled);
    }

    public function testIsEnabledWhenTheActingUserIsAMerchantUser(): void
    {
        // Arrange
        $userTransfer = $this->tester->haveUser();
        $this->tester->haveMerchantUser(
            $this->tester->haveMerchant([MerchantTransfer::STATUS => static::MERCHANT_STATUS_WAITING_FOR_APPROVAL]),
            $userTransfer,
        );
        $this->tester->getLocator()->user()->facade()->setCurrentUser($userTransfer);

        // Act
        $isDisabled = (new NoCurrentMerchantUserAclEntityDisablerPlugin())->isDisabled();

        // Assert
        $this->assertFalse($isDisabled);
    }
}
