<?php

namespace humhub\modules\orgmap\helpers;

use Yii;

class BackgroundImageHelper
{
    public static function fitsMemoryBudget(int $width, int $height, int $targetWidth, int $targetHeight, ?int $limit = null, ?int $used = null): bool
    {
        if ($limit === null) {
            $setting = trim((string) ini_get('memory_limit'));
            if ($setting === '-1') { return true; }
            $unit = strtolower(substr($setting, -1));
            $limit = (int) $setting * match ($unit) { 'g' => 1073741824, 'm' => 1048576, 'k' => 1024, default => 1 };
        }
        // Source decoding, destination buffers and reserve for PHP/core work.
        $estimate = $width * $height * 12 + $targetWidth * $targetHeight * 8 + 32 * 1048576;
        return $limit < 0 || ($used ?? memory_get_usage(true)) + $estimate <= $limit;
    }

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
		
		$workspace = WorkspaceHelper::getDimensions();
		$workspaceWidth = $workspace['width'];
		$workspaceHeight = $workspace['height'];
		
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

        if (!self::fitsMemoryBudget($originalWidth, $originalHeight, $newWidth, $newHeight)) {
            return; // Keep the already stored original; optimization is optional.
        }
		
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
