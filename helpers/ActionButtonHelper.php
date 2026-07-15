<?php

declare(strict_types=1);

namespace humhub\modules\orgmap\helpers;

use Yii;
use yii\helpers\Html;

class ActionButtonHelper
{

	public static function visibility(
		array $url,
		bool $visible
	): string
	{
	
		return Html::a(
	
			$visible
				? '<i class="fa fa-eye"></i>'
				: '<i class="fa fa-eye-slash"></i>',	
			$url,
	
			[
				'class' => $visible
					? 'btn btn-secondary btn-xs orgmap-action-btn'
					: 'btn btn-success btn-xs orgmap-action-btn',
			
				'encode' => false,
			
				'title' => $visible
					? Yii::t(
						'OrgmapModule.base',
						'Ausblenden'
					)
					: Yii::t(
						'OrgmapModule.base',
						'Einblenden'
					),
			
				'data-bs-toggle' => 'tooltip',
			
				'data-bs-placement' => 'top',
			
				'data-method' => 'post',
			]
		);
	}
	
	public static function edit(
		array $url
	): string
	{

		return Html::a(

			'<i class="fa fa-pencil"></i>',

			$url,

			[
				'class' => 'btn btn-warning btn-xs orgmap-action-btn',

				'encode' => false,

				'title' => Yii::t(
					'OrgmapModule.base',
					'Bearbeiten'
				),

				'data-bs-toggle' => 'tooltip',

				'data-bs-placement' => 'top',
			]
		);
	}
	
	public static function center(
		array $url
	): string
	{
	
		return Html::a(
	
			'<i class="fa fa-align-center"></i>',
	
			$url,
	
			[
				'class' => 'btn btn-info btn-xs orgmap-action-btn',
	
				'encode' => false,
	
				'title' => Yii::t(
					'OrgmapModule.base',
					'Text zentrieren'
				),
	
				'data-bs-toggle' => 'tooltip',
	
				'data-bs-placement' => 'top',
	
				'data-method' => 'post',
			]
		);
	}
	
	
	public static function delete(
		array $url,
		string $confirmMessage
	): string
	{
	
		return Html::a(
	
			'<i class="fa fa-trash"></i>',
	
			$url,
	
			[
				'class' => 'btn btn-danger btn-xs orgmap-action-btn',
	
				'encode' => false,
	
				'title' => Yii::t(
					'OrgmapModule.base',
					'Entfernen'
				),
	
				'data-bs-toggle' => 'tooltip',
	
				'data-bs-placement' => 'top',
	
				'data' => [
					'confirm' => $confirmMessage,
					'method' => 'post',
				]
			]
		);
	}
	
	public static function back(
		array $url,
		array $options = []
	): string
	{
	
		$options = array_merge(
			[
				'class' => 'btn btn-secondary btn-sm',
	
				'encode' => false,
	
				'title' => Yii::t(
					'OrgmapModule.base',
					'Zurück'
				),
	
				'data-bs-toggle' => 'tooltip',
	
				'data-bs-placement' => 'top',
			],
			$options
		);
	
		return Html::a(
	
			'<i class="fa fa-arrow-left"></i> '
			. Yii::t(
				'OrgmapModule.base',
				'Zurück'
			),
	
			$url,
	
			$options
		);
	}
	
	public static function assets(
		array $url
	): string
	{
	
		return Html::a(
	
			'<i class="fa fa-image"></i> '
			. Yii::t(
				'OrgmapModule.base',
				'Assets'
			),
	
			$url,
	
			[
				'class' => 'btn btn-info btn-sm',
	
				'encode' => false,
	
				'title' => Yii::t(
					'OrgmapModule.base',
					'Assets'
				),
	
				'data-bs-toggle' => 'tooltip',
	
				'data-bs-placement' => 'top',
			]
		);
	}
		
	public static function settings(
		array $url
	): string
	{
	
		return Html::a(
	
			'<i class="fa fa-gear"></i> '
			. Yii::t(
				'OrgmapModule.base',
				'Einstellungen'
			),
	
			$url,
	
			[
				'class' => 'btn btn-secondary btn-sm',
	
				'encode' => false,
	
				'title' => Yii::t(
					'OrgmapModule.base',
					'Einstellungen'
				),
	
				'data-bs-toggle' => 'tooltip',
	
				'data-bs-placement' => 'top',
			]
		);
	}
}