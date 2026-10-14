<?php

namespace humhub\modules\orgmap\models;

use yii\web\UploadedFile;

use Yii;
use humhub\modules\orgmap\components\ActiveRecord;

class Asset extends ActiveRecord implements \humhub\interfaces\ViewableInterface
{
	private const MAX_IMAGE_WIDTH = 10000;
	private const MAX_IMAGE_HEIGHT = 10000;
	private const MAX_IMAGE_PIXELS = 40000000;

	public $imageFile;


	public static function tableName()
	{
		return \humhub\modules\orgmap\helpers\TableNameHelper::resolve('orgmap_asset');
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
	
    public function getImageUrl(): string
    {
        $file = \humhub\modules\orgmap\services\AssetStorage::file($this);
        return $file ? $file->getUrl() : '';
    }

    public function canView($user = null): bool
    {
        if (!$this->hasAttribute('deleted_at') || $this->deleted_at !== null
            || !static::find()->where(['id' => $this->id, 'deleted_at' => null])->exists()
            || !\humhub\modules\orgmap\helpers\AccessHelper::canView($user)
                && !\humhub\modules\orgmap\helpers\AccessHelper::canManage($user)) {
            return false;
        }
        $nodes = $this->getNodes()->all();
        if (\humhub\modules\orgmap\helpers\AccessHelper::canManage($user)) {
            return !$nodes || (bool) \humhub\modules\orgmap\helpers\AccessHelper::filterNodes($nodes, $user);
        }
        $visible = array_values(array_filter($nodes, static fn(Node $node) => (bool) $node->visible));
        return (bool) \humhub\modules\orgmap\helpers\AccessHelper::filterNodes($visible, $user);
    }

    public function getImageDimensions(): string
    {
        $file = \humhub\modules\orgmap\services\AssetStorage::file($this);
        if ($file === null) {
            return '-';
        }
        try {
            $dimensions = @getimagesizefromstring(\humhub\modules\orgmap\services\AssetStorage::read($file));
        } catch (\RuntimeException $exception) {
            return '-';
        }
        return $dimensions ? $dimensions[0] . ' × ' . $dimensions[1] . ' px' : '-';
    }

    public function getFileSize(): string
    {
        $file = \humhub\modules\orgmap\services\AssetStorage::file($this);
        if ($file === null) {
            return '-';
        }
        $bytes = (int) $file->size;
        return $bytes >= 1024 * 1024
            ? number_format($bytes / 1024 / 1024, 1) . ' MB'
            : number_format($bytes / 1024, 0) . ' KB';
    }
}
