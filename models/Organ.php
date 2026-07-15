<?php

namespace humhub\modules\orgmap\models;

use Yii;
use yii\db\ActiveRecord;

class Organ extends ActiveRecord
{
    public static function tableName()
    {
        return 'orgmap_organ';
    }

	public function rules()
	{
		return [
	
			[['name'], 'required'],
	
			[['parent_id', 'sort_order'], 'integer'],
	
				[['name', 'color'], 'string', 'max' => 255],
				[['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/', 'skipOnEmpty' => true],
				[['parent_id'], 'compare', 'compareAttribute' => 'id', 'operator' => '!=', 'skipOnEmpty' => true],
				[['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => ['parent_id' => 'id'], 'skipOnEmpty' => true],
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
	
			'name' => Yii::t(
				'OrgmapModule.base',
				'Name'
			),
	
			'parent_id' => Yii::t(
				'OrgmapModule.base',
				'Übergeordnetes Organ'
			),
	
			'sort_order' => Yii::t(
				'OrgmapModule.base',
				'Sortierung'
			),
	
			'color' => Yii::t(
				'OrgmapModule.base',
				'Farbe'
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
	}
