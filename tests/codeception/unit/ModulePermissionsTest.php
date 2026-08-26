<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use orgmap\OrgMapTestCase;
use Yii;

class ModulePermissionsTest extends OrgMapTestCase
{
	public function testPermissionsAreRegisteredGloballyOnly(): void
	{
		$module = Yii::$app->getModule('orgmap');
		$globalPermissions = $module->getPermissions();

		$this->assertCount(2, $globalPermissions);
		$this->assertInstanceOf(ViewOrgMap::class, $globalPermissions[0]);
		$this->assertInstanceOf(ManageOrgMap::class, $globalPermissions[1]);
		$this->assertSame([], $module->getPermissions(new \stdClass()));
	}
}
