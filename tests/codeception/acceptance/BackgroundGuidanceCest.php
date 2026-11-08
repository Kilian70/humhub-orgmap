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
        $this->clickVisible($I, '#orgmap-show-image');
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
        $this->clickVisible($I, '#center-node');
        $I->dontSeeElement('#orgmap-position-warning');
        $I->fillField('#node-pos_x', '');
        $I->waitForElementVisible('#orgmap-position-warning');
        $this->clickVisible($I, '#center-node');
        $I->dontSeeElement('#orgmap-position-warning');
        $this->clickVisible($I, 'label[for="node-is_background"]');
        $I->seeElement('#orgmap-background-help');
        $this->clickVisible($I, '#fill-workspace');
        $I->dontSeeElement('#orgmap-position-warning');
        $I->makeScreenshot('orgmap-background-guidance');
    }

    /** Scroll like a user and wait until sticky navigation no longer covers the target. */
    private function clickVisible(\orgmap\AcceptanceTester $I, string $selector): void
    {
        $encoded = json_encode($selector, JSON_THROW_ON_ERROR);
        $I->executeJS('window.orgmapClickRect=null; document.querySelector(' . $encoded . ').scrollIntoView({behavior:"instant",block:"center",inline:"nearest"});');
        $I->waitForJS('const e=document.querySelector(' . $encoded . ');'
            . 'const r=e.getBoundingClientRect();'
            . 'const hit=document.elementFromPoint(r.left+r.width/2,r.top+r.height/2);'
            . 'const pos=[r.left,r.top,r.width,r.height].join(",");'
            . 'const stable=window.orgmapClickRect===pos; window.orgmapClickRect=pos;'
            . 'return stable && (e===hit || e.contains(hit));', 10);
        $I->click($selector);
    }

}
