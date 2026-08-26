<?php

namespace humhub\modules\orgmap;

use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use Yii;
use yii\helpers\FileHelper;
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

	/**
	 * Removes files which are stored outside HumHub's file component before
	 * the default module uninstall migration is executed.
	 */
	public function disable()
	{
		$this->removeUploadedAssets();

		return parent::disable();
	}

	private function removeUploadedAssets(): void
	{
		$moduleUploadPath = Yii::getAlias('@webroot/uploads/orgmap');
		$assetPath = $moduleUploadPath . DIRECTORY_SEPARATOR . 'assets';

		try {
			if (is_link($assetPath)) {
				if (!unlink($assetPath)) {
					Yii::warning('Could not remove OrgMap asset directory symlink.', __METHOD__);
				}
			} elseif (is_dir($assetPath)) {
				FileHelper::removeDirectory($assetPath);
			}

			if (is_dir($moduleUploadPath) && !is_link($moduleUploadPath)) {
				$entries = scandir($moduleUploadPath);
				if ($entries === ['.', '..'] && !rmdir($moduleUploadPath)) {
					Yii::warning('Could not remove empty OrgMap upload directory.', __METHOD__);
				}
			}
		} catch (\Throwable $exception) {
			Yii::warning(
				'Could not completely remove OrgMap asset files: ' . $exception->getMessage(),
				__METHOD__
			);
		}
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
