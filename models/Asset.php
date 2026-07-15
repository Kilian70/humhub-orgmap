<?php

namespace humhub\modules\orgmap\models;

use yii\web\UploadedFile;

use Yii;
use yii\db\ActiveRecord;

class Asset extends ActiveRecord
{

	public $imageFile;

	public static function tableName()
	{
		return 'orgmap_asset';
	}

	public function rules()
	{
		return [
	
			[['title'], 'required'],
	
			[['title'], 'string', 'max' => 255],
			[['type'], 'in', 'range' => array_keys(self::getTypeOptions())],
	
			[['imageFile'], 'file',
				'extensions' => [
					'jpg',
					'jpeg',
					'png',
					'webp'
				],
					'checkExtensionByMimeType' => true,
					'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
					'maxSize' => 10 * 1024 * 1024,
					'skipOnEmpty' => false,
			],
		];
	}
	
	/*
	----------------------------------------------------
	Asset-Typen
	----------------------------------------------------
	*/
	
	public static function getTypeOptions(): array
	{
		return [
	
			'image' => Yii::t(
				'OrgmapModule.base',
				'Bild'
			),
	
				'background' => Yii::t(
				'OrgmapModule.base',
				'Hintergrund'
				),

				'icon' => Yii::t('OrgmapModule.base', 'Icon'),
				'panel' => Yii::t('OrgmapModule.base', 'Panel'),
		];
	}
	
	/*
	----------------------------------------------------
	Bildgrösse
	----------------------------------------------------
	*/
	
	public function getImageDimensions(): string
	{
		$file =
			Yii::getAlias(
				'@webroot/uploads/orgmap/assets/'
			)
				. basename($this->filename);
	
		if (!file_exists($file)) {
			return '-';
		}
	
		$size = getimagesize($file);
	
		if (!$size) {
			return '-';
		}
	
		return
			$size[0]
			. ' × '
			. $size[1]
			. ' px';
	}
	
	/*
	----------------------------------------------------
	Dateigrösse
	----------------------------------------------------
	*/
	
	public function getFileSize(): string
	{
		$file =
			Yii::getAlias(
				'@webroot/uploads/orgmap/assets/'
			)
				. basename($this->filename);
	
		if (!file_exists($file)) {
			return '-';
		}
	
		$bytes = filesize($file);
	
		if ($bytes === false) {
			return '-';
		}
	
		if ($bytes >= 1024 * 1024) {
	
			return number_format(
				$bytes / 1024 / 1024,
				1
			)
			. ' MB';
		}
	
		return number_format(
			$bytes / 1024,
			0
		)
		. ' KB';
	}
	
}
