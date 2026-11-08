<?php

namespace humhub\modules\orgmap;

use humhub\modules\orgmap\permissions\ViewOrgMap;
use humhub\modules\ui\menu\MenuLink;
use Yii;

class Events
{
    public static function onBeforeMigration($event): void
    {
        self::prepareMigrationFiles($event->sender, $event->action->id);
    }

    private static function prepareMigrationFiles($controller, ?string $action): void
    {
        if (!$controller instanceof \humhub\commands\MigrateController
            || !in_array($action, ['up', 'new'], true)) {
            return;
        }
        $modulePath = __DIR__;
        $direct = realpath(Yii::getAlias($controller->migrationPath)) === realpath($modulePath . '/migrations');
        $moduleId = property_exists($controller, 'moduleId') ? $controller->moduleId : null;
        if ($direct || ($controller->includeModuleMigrations && ($moduleId === null || $moduleId === 'orgmap'))) {
            \humhub\modules\orgmap\services\LegacyMigrationArchive::prepare($modulePath);
        }
    }

    public static function onTopMenuInit($event)
    {
		$module = Yii::$app->getModule('orgmap');
		$settings = $module->settings;
		$visibility = $settings->get('topMenuVisibility', 'all');

		if ($visibility === 'hidden') {
			return;
		}

		if (
			$visibility === 'admin'
			&& (Yii::$app->user->isGuest || !Yii::$app->user->isAdmin())
		) {
			return;
		}

		if (Yii::$app->user->isGuest) {
			if (!$settings->get('allowGuestAccess', false)) {
				return;
			}
		} elseif (!Yii::$app->user->can(ViewOrgMap::class)) {
			return;
		}
		
        $event->sender->addEntry(new MenuLink([

            'id' => 'topmenu-orgmap',

            'label' => $settings->get('moduleTitle', 'ORG.'),

            'url' => ['/orgmap/map/index'],

            'icon' => 'circle-o',

            'sortOrder' => $settings->get('topMenuSortOrder', 250),

            'isActive' => Yii::$app->controller?->module?->id === 'orgmap',
        ]));
    }
}
