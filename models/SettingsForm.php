<?php

namespace humhub\modules\orgmap\models;

use Yii;
use yii\base\Model;

class SettingsForm extends Model
{

	public $showLines;

	public $allowGuestAccess;

	public $topMenuSortOrder;
	
	public $moduleTitle;
	
	public $workspaceWidth;

	public $workspaceHeight;
	public $workspaceSize;

	public function rules()
	{
		return [

			[['showLines'], 'boolean'],
		
			[['allowGuestAccess'], 'boolean'],
		
			[['topMenuSortOrder'], 'integer'],
		
			[
				['workspaceWidth', 'workspaceHeight'],
				'integer',
				'min' => 500,
				'max' => 10000
			],
		
				[['moduleTitle'], 'string', 'max' => 255],
				[['workspaceSize'], 'in', 'range' => ['small', 'medium', 'large', 'custom', 'background']],
		];
	}
	
	public function attributeLabels()
	{
		return [
	
			'showLines' => Yii::t(
				'OrgmapModule.base',
				'Verbindungslinien anzeigen'
			),
	
			'allowGuestAccess' => Yii::t(
				'OrgmapModule.base',
				'Gastzugriff erlauben'
			),
	
			'topMenuSortOrder' => Yii::t(
				'OrgmapModule.base',
				'Position im Hauptmenü'
			),
	
			'moduleTitle' => Yii::t(
				'OrgmapModule.base',
				'Modultitel'
			),
	
			'workspaceWidth' => Yii::t(
				'OrgmapModule.base',
				'Breite der Arbeitsfläche'
			),
	
				'workspaceHeight' => Yii::t(
				'OrgmapModule.base',
				'Höhe der Arbeitsfläche'
				),

				'workspaceSize' => Yii::t(
					'OrgmapModule.base',
					'Grösse der Arbeitsfläche'
				),
		];
	}

	public function init()
	{
		parent::init();

		$settings =
			Yii::$app
				->getModule('orgmap')
				->settings;

		$this->showLines =
			$settings->get(
				'showLines',
				true
			);

		$this->allowGuestAccess =
			$settings->get(
				'allowGuestAccess',
				false
			);

		$this->topMenuSortOrder =
			$settings->get(
				'topMenuSortOrder',
				300
			);
			
		$this->moduleTitle =
			$settings->get(
				'moduleTitle',
				'ORG.'
			);
		$this->workspaceWidth =
			$settings->get(
				'workspaceWidth',
				2400
			);
		
			$this->workspaceHeight =
			$settings->get(
				'workspaceHeight',
				1350
				);
			$this->workspaceSize = $settings->get('workspaceSize', 'medium');
		
	}

	public function save()
	{
		if (!$this->validate()) {
			return false;
		}
		$settings =
			Yii::$app
				->getModule('orgmap')
				->settings;

			$settings->set(
			'showLines',
			$this->showLines
			);
		$settings->set('workspaceSize', $this->workspaceSize);

		$settings->set(
			'allowGuestAccess',
			$this->allowGuestAccess
		);

		$settings->set(
			'topMenuSortOrder',
			$this->topMenuSortOrder
		);
		
		$settings->set(
			'moduleTitle',
			$this->moduleTitle
		);
		
		$settings->set(
			'workspaceWidth',
			$this->workspaceWidth
		);
		
		$settings->set(
			'workspaceHeight',
			$this->workspaceHeight
		);

		return true;
	}
}
