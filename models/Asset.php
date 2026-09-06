<?php

namespace humhub\modules\orgmap\models;

use yii\web\UploadedFile;

use Yii;
use yii\db\ActiveRecord;

class Asset extends ActiveRecord
{
	private const MAX_IMAGE_WIDTH = 10000;
	private const MAX_IMAGE_HEIGHT = 10000;
	private const MAX_IMAGE_PIXELS = 40000000;

	public $imageFile;

	public static function tableName()
	{
		return 'orgmap_asset';
	}

	public function getNodes()
	{
		return $this->hasMany(Node::class, ['asset_id' => 'id'])
			->orderBy(['title' => SORT_ASC]);
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
			[['imageFile'], 'validateImageDimensions', 'skipOnError' => true],
		];
	}

	public function validateImageDimensions(string $attribute): void
	{
		$file = $this->{$attribute};
		if (!$file instanceof UploadedFile || !is_file($file->tempName)) {
			return;
		}

		$dimensions = @getimagesize($file->tempName);
		$width = (int) ($dimensions[0] ?? 0);
		$height = (int) ($dimensions[1] ?? 0);

		if (
			$width < 1 || $height < 1
			|| $width > self::MAX_IMAGE_WIDTH
			|| $height > self::MAX_IMAGE_HEIGHT
			|| ($width * $height) > self::MAX_IMAGE_PIXELS
		) {
			$this->addError(
				$attribute,
				Yii::t('OrgmapModule.base', 'Das Bild ist beschädigt oder seine Abmessungen sind zu gross.')
			);
		}
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
