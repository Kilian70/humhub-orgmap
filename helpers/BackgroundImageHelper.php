<?php

namespace humhub\modules\orgmap\helpers;

use Yii;

class BackgroundImageHelper
{
	public static function optimize(
		string $filePath
	): void
	{
		/*
		----------------------------------------------------
		GD verfügbar?
		----------------------------------------------------
		*/

		if (
			!function_exists(
				'imagecreatetruecolor'
			)
			||
			!function_exists(
				'imagecopyresampled'
			)
		) {
			return;
		}

		/*
		----------------------------------------------------
		Datei vorhanden?
		----------------------------------------------------
		*/

		if (!file_exists($filePath)) {
			return;
		}

		/*
		----------------------------------------------------
		Bildinformationen lesen
		----------------------------------------------------
		*/

		$imageInfo = getimagesize(
			$filePath
		);

		if (!$imageInfo) {
			return;
		}

		$originalWidth =
			$imageInfo[0];

			$originalHeight =
				$imageInfo[1];

			if (
				$originalWidth < 1 || $originalHeight < 1
				|| $originalWidth > 10000 || $originalHeight > 10000
				|| ($originalWidth * $originalHeight) > 40000000
			) {
				return;
			}
			
		/*
		----------------------------------------------------
		Arbeitsfläche lesen
		----------------------------------------------------
		*/
		
		$settings =
			Yii::$app
				->getModule('orgmap')
				->settings;
		
		$workspaceSize =
			$settings->get(
				'workspaceSize',
				'medium'
			);
		
		switch ($workspaceSize) {
		
			case 'small':
		
				$workspaceWidth = 1600;
				$workspaceHeight = 900;
				break;
		
			case 'large':
		
				$workspaceWidth = 3200;
				$workspaceHeight = 1800;
				break;
		
			case 'custom':
		
				$workspaceWidth =
					(int) $settings->get(
						'workspaceWidth',
						2400
					);
		
				$workspaceHeight =
					(int) $settings->get(
						'workspaceHeight',
						1350
					);
		
				break;
		
			default:
		
				$workspaceWidth = 2400;
				$workspaceHeight = 1350;
		}
		
		/*
		----------------------------------------------------
		Keine Optimierung nötig
		----------------------------------------------------
		*/
		
		if (
			$originalWidth <= $workspaceWidth
			&&
			$originalHeight <= $workspaceHeight
		) {
			return;
		}
		
		/*
		----------------------------------------------------
		Skalierung berechnen
		----------------------------------------------------
		*/
		
		$scale = min(
			$workspaceWidth / $originalWidth,
			$workspaceHeight / $originalHeight
		);
		
		$newWidth =
			(int) round(
				$originalWidth * $scale
			);
		
		$newHeight =
			(int) round(
				$originalHeight * $scale
			);
		
		/*
		----------------------------------------------------
		Bild laden
		----------------------------------------------------
		*/
		
		switch ($imageInfo['mime']) {
		
			case 'image/jpeg':
		
				if (!function_exists(
					'imagecreatefromjpeg'
				)) {
					return;
				}
		
				$source =
					imagecreatefromjpeg(
						$filePath
					);
		
				break;
		
			case 'image/png':
		
				if (!function_exists(
					'imagecreatefrompng'
				)) {
					return;
				}
		
				$source =
					imagecreatefrompng(
						$filePath
					);
		
				break;
		
			case 'image/webp':
		
				if (!function_exists(
					'imagecreatefromwebp'
				)) {
					return;
				}
		
				$source =
					imagecreatefromwebp(
						$filePath
					);
		
				break;
		
			default:
		
				return;
		}
		
		if (!$source) {
			return;
		}
	
	/*
	----------------------------------------------------
	Neues Bild erzeugen
	----------------------------------------------------
	*/
	
	$target = imagecreatetruecolor(
		$newWidth,
		$newHeight
	);
	
	if (!$target) {
	
		imagedestroy($source);
	
		return;
	}
	
	/*
	----------------------------------------------------
	PNG/WebP Transparenz erhalten
	----------------------------------------------------
	*/
	
	imagealphablending(
		$target,
		false
	);
	
	imagesavealpha(
		$target,
		true
	);
	
	/*
	----------------------------------------------------
	Bild skalieren
	----------------------------------------------------
	*/
	
	imagecopyresampled(
		$target,
		$source,
		0,
		0,
		0,
		0,
		$newWidth,
		$newHeight,
		$originalWidth,
		$originalHeight
	);
	
	/*
	----------------------------------------------------
	Bild speichern
	----------------------------------------------------
	*/
	
	switch ($imageInfo['mime']) {
	
		case 'image/jpeg':
	
			if (function_exists('imagejpeg')) {
	
				if (!imagejpeg(
					$target,
					$filePath,
					90
				)) {
				
					imagedestroy($source);
					imagedestroy($target);
				
					return;
				}
			}
	
			break;
	
		case 'image/png':
	
			if (function_exists('imagepng')) {
	
				if (!imagepng(
					$target,
					$filePath,
					6
				)) {
				
					imagedestroy($source);
					imagedestroy($target);
				
					return;
				}
			}
	
			break;
	
		case 'image/webp':
	
			if (function_exists('imagewebp')) {
	
				if (!imagewebp(
					$target,
					$filePath,
					85
				)) {
				
					imagedestroy($source);
					imagedestroy($target);
				
					return;
				}
			}
	
			break;
	}
	
	
	/*
	----------------------------------------------------
	Speicher freigeben
	----------------------------------------------------
	*/
	
	imagedestroy($source);
	imagedestroy($target);
			
	}
}
