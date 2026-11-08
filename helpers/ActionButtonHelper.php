<?php

declare(strict_types=1);

namespace humhub\modules\orgmap\helpers;

use Yii;
use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;

class ActionButtonHelper
{

	public static function visibility(
		array $url,
		bool $visible
	): string
	{
	
		return Html::a(
	
			$visible ? Icon::get('eye') : Icon::get('eye-slash'),
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

					'aria-label' => $visible
						? Yii::t('OrgmapModule.base', 'Ausblenden')
						: Yii::t('OrgmapModule.base', 'Einblenden'),
			
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

				Icon::get('pencil'),

			$url,

			[
				'class' => 'btn btn-warning btn-xs orgmap-action-btn',

				'encode' => false,

					'title' => Yii::t(
					'OrgmapModule.base',
					'Bearbeiten'
					),

					'aria-label' => Yii::t('OrgmapModule.base', 'Bearbeiten'),

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
	
				Icon::get('align-center'),
	
			$url,
	
			[
				'class' => 'btn btn-info btn-xs orgmap-action-btn',
	
				'encode' => false,
	
					'title' => Yii::t(
					'OrgmapModule.base',
					'Text zentrieren'
					),

					'aria-label' => Yii::t('OrgmapModule.base', 'Text zentrieren'),
	
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
	
				Icon::get('trash'),
	
			$url,
	
			[
				'class' => 'btn btn-danger btn-xs orgmap-action-btn',
	
				'encode' => false,
	
					'title' => Yii::t(
					'OrgmapModule.base',
					'Entfernen'
					),

					'aria-label' => Yii::t('OrgmapModule.base', 'Entfernen'),
	
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
	
				Icon::get('arrow-left') . ' '
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
	
				Icon::get('image') . ' '
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
	
				Icon::get('cog') . ' '
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
