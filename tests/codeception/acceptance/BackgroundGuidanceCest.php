<?php

use humhub\libs\BasePermission;
use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\Group;
use PHPUnit\Framework\Assert;

class BackgroundGuidanceCest
{
    public function testHiddenImageAndClippedPositionCanBeCorrectedExplicitly(\orgmap\AcceptanceTester $I): void
    {
        $pm = new PermissionManager();
        foreach (Group::find()->where(['is_admin_group' => 1])->all() as $group) {
            $pm->setGroupState($group->id, ManageOrgMap::class, BasePermission::STATE_ALLOW);
        }
        $I->amAdmin();
        $I->amOnPage('/orgmap/admin/create');
        $I->waitForElementVisible('#node-title', 15);
        $I->selectOption('#node-image_source', 'custom');
        $I->fillField('#node-custom_image', 'https://example.com/background.png');
        $I->selectOption('#node-display_mode', 'color');
        $I->waitForElementVisible('#orgmap-image-warning');
        Assert::assertSame('color', $I->grabValueFrom('#node-display_mode'));
        $I->scrollTo('#orgmap-image-warning', 0, -120);
        $I->makeScreenshot('orgmap-hidden-image-warning');
        $I->click('#orgmap-show-image');
        Assert::assertSame('image', $I->grabValueFrom('#node-display_mode'));
        $I->dontSeeElement('#orgmap-image-warning');
        $I->selectOption('#node-display_mode', 'mixed');
        $I->dontSeeElement('#orgmap-image-warning');
        $I->fillField('#node-custom_image', '');
        $I->selectOption('#node-display_mode', 'color');
        $I->dontSeeElement('#orgmap-image-warning');

        $I->fillField('#node-pos_x', '-50');
        $I->fillField('#node-pos_y', '0');
        $I->waitForElementVisible('#orgmap-position-warning');
        Assert::assertSame('-50', $I->grabValueFrom('#node-pos_x'));
        $I->click('#center-node');
        $I->dontSeeElement('#orgmap-position-warning');
        $I->fillField('#node-pos_x', '');
        $I->waitForElementVisible('#orgmap-position-warning');
        $I->click('#center-node');
        $I->dontSeeElement('#orgmap-position-warning');
        $I->checkOption('#node-is_background');
        $I->seeElement('#orgmap-background-help');
        $I->click('#fill-workspace');
        $I->dontSeeElement('#orgmap-position-warning');
        $I->makeScreenshot('orgmap-background-guidance');
    }
}
