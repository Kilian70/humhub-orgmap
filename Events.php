<?php

namespace humhub\modules\orgmap;

use humhub\modules\ui\menu\MenuLink;
use Yii;

class Events
{

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

		$allowGuestAccess = $settings->get('allowGuestAccess', false);
		
		if (
			Yii::$app->user->isGuest
			&& !$allowGuestAccess
		) {
		
			return;
		}
		
        $event->sender->addEntry(new MenuLink([

            'id' => 'topmenu-orgmap',

            'label' => $settings->get('moduleTitle', 'ORG.'),

            'url' => ['/orgmap/map/index'],

            'icon' => 'fa-circle-o',

            'sortOrder' => $settings->get('topMenuSortOrder', 250),

            'isActive' => Yii::$app->controller?->module?->id === 'orgmap',
        ]));
    }
}
