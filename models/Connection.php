<?php

namespace humhub\modules\orgmap\models;

use Yii;
use yii\db\ActiveRecord;

class Connection extends ActiveRecord
{

	public static function getArrowOptions()
	{
		return [
	
			'none' => Yii::t(
				'OrgmapModule.base',
				'Kein Pfeil'
			),
	
			'start' => Yii::t(
				'OrgmapModule.base',
				'Start'
			),
	
			'end' => Yii::t(
				'OrgmapModule.base',
				'Ende'
			),
	
			'both' => Yii::t(
				'OrgmapModule.base',
				'Beide'
			),
		];
	}
	
	public static function getLabelRotationOptions()
	{
		return [
	
			'auto' => Yii::t(
				'OrgmapModule.base',
				'Automatisch'
			),
	
			'horizontal' => Yii::t(
				'OrgmapModule.base',
				'Horizontal'
			),
		];
	}
	
	public static function getFontWeightOptions()
	{
		return [
	
			'normal' => Yii::t(
				'OrgmapModule.base',
				'Normal'
			),
	
			'bold' => Yii::t(
				'OrgmapModule.base',
				'Fett'
			),
		];
	}
	
	public static function getTypeOptions()
	{
		return [
	
			'' => Yii::t(
				'OrgmapModule.base',
				'Benutzerdefiniert'
			),
	
			'reports_to' => Yii::t(
				'OrgmapModule.base',
				'Berichtet an'
			),
	
			'coordinates' => Yii::t(
				'OrgmapModule.base',
				'Koordiniert'
			),
	
			'supports' => Yii::t(
				'OrgmapModule.base',
				'Unterstützt'
			),
	
			'belongs_to' => Yii::t(
				'OrgmapModule.base',
				'Gehört zu'
			),
	
			'collaborates' => Yii::t(
				'OrgmapModule.base',
				'Arbeitet mit'
			),
			
			'decides' => Yii::t(
				'OrgmapModule.base',
				'Entscheidet'
			),
			
			'informs' => Yii::t(
				'OrgmapModule.base',
				'Informiert'
			),
		];
	}
	
	public static function tableName()
	{
		return 'orgmap_connection';
	}

	public function rules()
	{
		return [
	
			[[
				'from_node_id',
				'to_node_id'
			], 'required'],
	
			[[
				'from_node_id',
				'to_node_id',
				'width',
				'curve',
				'font_size',
				'label_offset_x',
				'label_offset_y'
			], 'integer'],
	
			[[
				'color',
				'style',
				'label',
				'arrow',
				'font_weight',
				'label_rotation',
				'type'
			], 'string', 'max' => 50],
			
				[['arrow'], 'default', 'value' => 'none'],
				[['type'], 'in', 'range' => array_keys(self::getTypeOptions())],
				[['arrow'], 'in', 'range' => array_keys(self::getArrowOptions())],
				[['label_rotation'], 'in', 'range' => array_keys(self::getLabelRotationOptions())],
				[['font_weight'], 'in', 'range' => array_keys(self::getFontWeightOptions())],
				[['style'], 'in', 'range' => ['solid', 'dashed', 'dotted']],
				[['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'],
				[['width'], 'integer', 'min' => 1, 'max' => 20],
				[['font_size'], 'integer', 'min' => 8, 'max' => 100],
				[['to_node_id'], 'compare', 'compareAttribute' => 'from_node_id', 'operator' => '!='],
				[['from_node_id'], 'exist', 'targetClass' => Node::class, 'targetAttribute' => ['from_node_id' => 'id']],
				[['to_node_id'], 'exist', 'targetClass' => Node::class, 'targetAttribute' => ['to_node_id' => 'id']],
		];
	}

	public function getFromNode()
	{
		return $this->hasOne(
			Node::class,
			['id' => 'from_node_id']
		);
	}

	public function getToNode()
	{
		return $this->hasOne(
			Node::class,
			['id' => 'to_node_id']
		);
	}

	public function applyTypeDefaults()
	{
		$defaultLabels =
			self::getDefaultLabels();
	
		switch ($this->type) {
	
			case 'reports_to':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'end';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'solid';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#000000';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['reports_to'];
				}
	
				break;
	
			case 'coordinates':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'both';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'dashed';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#000000';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['coordinates'];
				}
	
				break;
	
			case 'supports':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'both';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'dotted';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#000000';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['supports'];
				}
	
				break;
	
			case 'belongs_to':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'end';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'solid';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#444444';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['belongs_to'];
				}
	
				break;
	
			case 'collaborates':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'both';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'dashed';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#00897b';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['collaborates'];
				}
	
				break;
	
			case 'decides':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'end';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'solid';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#000000';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['decides'];
				}
	
				break;
	
			case 'informs':
	
				if (
					empty($this->arrow)
					|| $this->arrow === 'none'
				) {
	
					$this->arrow = 'end';
				}
	
				if (empty($this->style)) {
	
					$this->style = 'dotted';
				}
	
				if (empty($this->color)) {
	
					$this->color = '#000000';
				}
	
				if (
					empty($this->label)
					|| in_array(
						$this->label,
						$defaultLabels
					)
				) {
	
					$this->label =
						$defaultLabels['informs'];
				}
	
				break;
		}
	}
	
	public static function getDefaultLabels()
	{
		return [
	
			'reports_to' => Yii::t(
				'OrgmapModule.base',
				'berichtet'
			),
	
			'coordinates' => Yii::t(
				'OrgmapModule.base',
				'koordiniert'
			),
	
			'supports' => Yii::t(
				'OrgmapModule.base',
				'unterstützt'
			),
	
			'belongs_to' => Yii::t(
				'OrgmapModule.base',
				'gehört zu'
			),
	
			'collaborates' => Yii::t(
				'OrgmapModule.base',
				'arbeitet mit'
			),
			
			'decides' => Yii::t(
				'OrgmapModule.base',
				'entscheidet'
			),
			
			'informs' => Yii::t(
				'OrgmapModule.base',
				'informiert'
			),
		];
	}
}
