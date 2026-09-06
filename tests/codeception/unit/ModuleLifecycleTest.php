<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\Module;
use orgmap\OrgMapTestCase;
use Yii;
use yii\helpers\FileHelper;

class ModuleLifecycleTest extends OrgMapTestCase
{
	public function testUpdateUninstallAndReinstall(): void
	{
		$this->assertIsolatedTestDatabase();

		/** @var Module $module */
		$module = Yii::$app->getModule('orgmap');
		$this->assertInstanceOf(Module::class, $module);

		$originalWebroot = Yii::getAlias('@webroot');
		$tempWebroot = sys_get_temp_dir() . '/orgmap-lifecycle-' . bin2hex(random_bytes(6));
		$assetPath = $tempWebroot . '/uploads/orgmap/assets';
		FileHelper::createDirectory($assetPath);
		file_put_contents($assetPath . '/test.png', 'test');

		try {
			Yii::setAlias('@webroot', $tempWebroot);

			$module->update();
			$this->assertNotNull(Yii::$app->db->schema->getTableSchema('orgmap_node', true));

			$this->assertNotFalse($module->disable());
			Yii::$app->db->schema->refresh();
			$this->assertNull(Yii::$app->db->schema->getTableSchema('orgmap_node', true));
			$this->assertDirectoryDoesNotExist($assetPath);

			$this->assertNotFalse($module->enable());
			Yii::$app->db->schema->refresh();
			$this->assertNotNull(Yii::$app->db->schema->getTableSchema('orgmap_node', true));
		} finally {
			Yii::setAlias('@webroot', $originalWebroot);
			if (is_dir($tempWebroot)) {
				FileHelper::removeDirectory($tempWebroot);
			}
		}
	}

	private function assertIsolatedTestDatabase(): void
	{
		$databaseName = (string) Yii::$app->db->createCommand('SELECT DATABASE()')->queryScalar();

		$this->assertSame(
			'humhub_test',
			$databaseName,
			'Lifecycle tests may only run against the isolated humhub_test database.'
		);
	}
}
