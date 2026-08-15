<?php

declare(strict_types=1);

namespace humhub\modules\orgmap\helpers;

use Yii;

final class WorkspaceHelper
{
	public const DEFAULT_WIDTH = 2400;
	public const DEFAULT_HEIGHT = 1350;

	/** @return array{width:int,height:int} */
	public static function getDimensions(): array
	{
		$settings = Yii::$app->getModule('orgmap')->settings;

		switch ((string) $settings->get('workspaceSize', 'medium')) {
			case 'small':
				return ['width' => 1600, 'height' => 900];
			case 'large':
				return ['width' => 3200, 'height' => 1800];
			case 'custom':
			case 'background':
				return [
					'width' => self::boundedDimension($settings->get('workspaceWidth', self::DEFAULT_WIDTH), self::DEFAULT_WIDTH),
					'height' => self::boundedDimension($settings->get('workspaceHeight', self::DEFAULT_HEIGHT), self::DEFAULT_HEIGHT),
				];
			default:
				return ['width' => self::DEFAULT_WIDTH, 'height' => self::DEFAULT_HEIGHT];
		}
	}

	private static function boundedDimension($value, int $fallback): int
	{
		$value = (int) $value;

		return $value >= 500 && $value <= 10000 ? $value : $fallback;
	}
}
