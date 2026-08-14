<?php

namespace humhub\modules\orgmap\models;

use Yii;
use yii\db\ActiveRecord;
use humhub\modules\orgmap\models\Connection;

class Node extends ActiveRecord
{
	public function init()
	{
		parent::init();

		if ($this->opacity === null) {
			$this->opacity = 100;
		}
	}

    public static function tableName()
    {
        return 'orgmap_node';
    }
    
    public static function getShapeOptions()
	{
		return [
	
			'circle' => Yii::t(
				'OrgmapModule.base',
				'Kreis'
			),
	
			'rectangle' => Yii::t(
				'OrgmapModule.base',
				'Rechteck'
			),
	
			'rounded' => Yii::t(
				'OrgmapModule.base',
				'Abgerundet'
			),
	
			'panel' => Yii::t(
				'OrgmapModule.base',
				'Panel'
			),
	
			'legend' => Yii::t(
				'OrgmapModule.base',
				'Legende'
			),
	
		];
	}
	
	public static function getBackgroundSizeOptions()
	{
		return [
	
			'cover' => Yii::t(
				'OrgmapModule.base',
				'Bild füllen'
			),
	
			'contain' => Yii::t(
				'OrgmapModule.base',
				'Ganzes Bild anzeigen'
			),
	
		];
	}

    public function getParent()
    {
        return $this->hasOne(
            self::class,
            ['id' => 'parent_id']
        );
    }
    
	public function getOrgan()
	{
		return $this->hasOne(
			\humhub\modules\orgmap\models\Organ::class,
			['id' => 'organ_id']
		);
	}

	public function getSpace()
	{
		return $this->hasOne(\humhub\modules\space\models\Space::class, ['id' => 'space_id']);
	}

	public function getAsset()
	{
		return $this->hasOne(Asset::class, ['id' => 'asset_id']);
	}
	
	public function getConnections()
	{
		return $this->hasMany(
			Connection::class,
			['from_node_id' => 'id']
		);
	}
	
	public $connectionIds = [];

    public function rules()
    {
		return [
		
			[['title'], 'required'],
			[['connectionIds'], 'safe'],
		
			[[
				'parent_id',
				'organ_id',
				'space_id',
				'asset_id',
				'pos_x',
				'pos_y',
				'radius',
				'width',
				'height',
				'sort_order',
				'label_x',
				'label_y',
				'font_size',
				'opacity',
				'border_width'
			], 'integer'],		
						[[
			
				'is_external',
				'visible',
				'label_background',
				'show_label',
				'open_in_new_tab',
				'is_background',
				'show_parent_line',
				'show_in_tree'
			], 'boolean'],
		
			[[
				'title',
				'subtitle',
				'type',
				'url',
				'color',
				'border_color',
				'link_type',
				'display_mode',
				'background_size',
				'custom_image',
				'image_source',
				'shape',
				'icon_class'
			], 'string', 'max' => 255],
			
				[['content'], 'string'],
				[['shape'], 'in', 'range' => array_keys(self::getShapeOptions())],
				[['background_size'], 'in', 'range' => array_keys(self::getBackgroundSizeOptions())],
				[['display_mode'], 'in', 'range' => ['color', 'image', 'mixed', 'none']],
				[['image_source'], 'in', 'range' => ['none', 'space', 'custom', 'asset']],
				[['link_type'], 'in', 'range' => ['', 'space_home', 'space_about', 'external']],
				[['type'], 'in', 'range' => ['core', 'organ', 'space', 'tool', 'external']],
				[['color', 'border_color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'],
				[['url', 'custom_image'], 'url', 'validSchemes' => ['http', 'https'], 'skipOnEmpty' => true],
				[['radius', 'width', 'height'], 'integer', 'min' => 20, 'max' => 5000],
				[['opacity'], 'integer', 'min' => 0, 'max' => 100],
				[['font_size'], 'integer', 'min' => 8, 'max' => 100],
				[['border_width'], 'integer', 'min' => 0, 'max' => 20],
				[['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => ['parent_id' => 'id'], 'skipOnEmpty' => true],
				[['organ_id'], 'exist', 'targetClass' => Organ::class, 'targetAttribute' => ['organ_id' => 'id'], 'skipOnEmpty' => true],
				[['space_id'], 'exist', 'targetClass' => \humhub\modules\space\models\Space::class, 'targetAttribute' => ['space_id' => 'id'], 'skipOnEmpty' => true],
				[['asset_id'], 'exist', 'targetClass' => Asset::class, 'targetAttribute' => ['asset_id' => 'id'], 'skipOnEmpty' => true],
				[['parent_id'], 'compare', 'compareAttribute' => 'id', 'operator' => '!=', 'skipOnEmpty' => true],
				[['parent_id'], 'validateParentHierarchy', 'skipOnEmpty' => true],
		
		];
		
			}

	public function validateParentHierarchy($attribute): void
	{
		$parentId = (int) $this->$attribute;
		$visited = [];

		while ($parentId > 0) {
			if ($parentId === (int) $this->id || isset($visited[$parentId])) {
				$this->addError($attribute, Yii::t('OrgmapModule.base', 'Zyklische Parent-Beziehung ist nicht erlaubt.'));
				return;
			}
			$visited[$parentId] = true;
			$parentId = (int) static::find()->select('parent_id')->where(['id' => $parentId])->scalar();
		}
	}

		public function attributeLabels()
	{
		return [
	
			'title' => Yii::t(
				'OrgmapModule.base',
				'Titel'
			),
			
			'subtitle' => Yii::t(
				'OrgmapModule.base',
				'Untertitel'
			),
			
			'icon_class' => Yii::t(
				'OrgmapModule.base',
				'Icon'
			),
	
			'type' => Yii::t(
				'OrgmapModule.base',
				'Typ'
			),
	
			'space_id' => Yii::t(
				'OrgmapModule.base',
				'Space'
			),
			
			'asset_id' => Yii::t(
				'OrgmapModule.base',
				'ORG Asset'
			),
	
			'color' => Yii::t(
				'OrgmapModule.base',
				'Farbe'
			),
			
			'border_width' => Yii::t(
				'OrgmapModule.base',
				'Rahmenbreite'
			),
			
			'border_color' => Yii::t(
				'OrgmapModule.base',
				'Rahmenfarbe'
			),
							
			'display_mode' => Yii::t(
				'OrgmapModule.base',
				'Darstellung'
			),
			
			'background_size' => Yii::t(
				'OrgmapModule.base',
				'Bildanpassung'
			),
				
			'custom_image' => Yii::t(
				'OrgmapModule.base',
				'Bild URL'
			),
	
			'opacity' => Yii::t(
				'OrgmapModule.base',
				'Transparenz'
			),
	
			'image_source' => Yii::t(
				'OrgmapModule.base',
				'Bildquelle'
			),
	
			'open_in_new_tab' => Yii::t(
				'OrgmapModule.base',
				'In neuem Fenster öffnen'
			),
	
			'is_background' => Yii::t(
				'OrgmapModule.base',
				'Hintergrundfläche'
			),
			
			'content' => Yii::t(
				'OrgmapModule.base',
				'Inhalt'
			),
			
			'show_parent_line' => Yii::t(
				'OrgmapModule.base',
				'Parent-Linie anzeigen'
			),
			
			'show_in_tree' => Yii::t(
				'OrgmapModule.base',
				'Im Tree anzeigen'
			),
						
		];
	}
}
