<?php

namespace humhub\modules\orgmap;

use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use Yii;
use humhub\modules\orgmap\services\PreservingMigrationService;
use humhub\services\MigrationService;
use yii\helpers\Url;

class Module extends \humhub\components\Module
{

    public $controllerNamespace = 'humhub\modules\orgmap\controllers';

    public function getName()
    {
        return 'OrgMap';
    }

	public function getDescription()
	{
		return 'Visual organization map.';
	}

	public function getConfigUrl()
	{
		return Url::to(['/orgmap/admin/index']);
	}

    /** Private archive survives runtime/cache cleanup and remains outside public uploads. */
    public $legacyAssetArchivePath = '@app/orgmap-legacy-assets';

    public function enable()
    {
        PreservingMigrationService::assertLayout($this->getBasePath());
        return parent::enable();
    }

    /** Disable registration only; data, settings, migration history and files are retained. */
    public function disable()
    {
        if (method_exists($this, 'getModuleService')) {
            $this->getModuleService()->disable();
        } else {
            Yii::$app->moduleManager->disable($this);
        }
        return true;
    }

    public function getMigrationService(): MigrationService
    {
        PreservingMigrationService::assertLayout($this->getBasePath());
        return new PreservingMigrationService($this);
    }

	/*
	--------------------------------------------------
	Permissions
	--------------------------------------------------
	*/
	
	public function getPermissions(
		$contentContainer = null
	): array
	{
		if ($contentContainer !== null) {
			return [];
		}

		return [
			new ViewOrgMap(),
			new ManageOrgMap(),
		];
	}
}
