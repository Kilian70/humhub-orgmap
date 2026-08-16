<?php

namespace humhub\modules\orgmap\helpers;

class NodeStyleHelper
{

	/*
	--------------------------------------------------
	HEX zu RGB
	--------------------------------------------------
	*/

	public static function hexToRgb($hex)
	{

		$hex = ltrim($hex, '#');

		return [

			'r' => hexdec(substr($hex, 0, 2)),

			'g' => hexdec(substr($hex, 2, 2)),

			'b' => hexdec(substr($hex, 4, 2)),
		];
	}

	/*
	--------------------------------------------------
	Hintergrundstil
	--------------------------------------------------
	*/

	public static function buildBackgroundStyle(
		$node,
		$imageToUse = null
	) {
	
		$colorOpacityValue =
			$node->color_opacity !== null
			? $node->color_opacity
			: ($node->opacity ?? 100);
		$colorOpacity = $colorOpacityValue / 100;
	
			$backgroundSize =
				in_array($node->background_size, ['cover', 'contain'], true) ? $node->background_size : 'cover';
	
		$hex =
			$node->color ?: '#6ec6ff';
	
			$rgb =
				self::hexToRgb($hex);

		if ($imageToUse !== null && !preg_match('~^(https?://|/)~i', $imageToUse)) {
			$imageToUse = null;
		}
		$imageToUse = str_replace(["'", '"', "\r", "\n", '\\'], '', (string) $imageToUse);
	
		$r = $rgb['r'];
		$g = $rgb['g'];
		$b = $rgb['b'];

		/*
		--------------------------------------------------
		Nur Bild
		--------------------------------------------------
		*/

		if ($node->display_mode === 'image' && $imageToUse) {
			return "background: transparent;";
		}
		/*
		--------------------------------------------------
		Bild + Farbe
		--------------------------------------------------
		*/
		
		if (
			$node->display_mode === 'mixed'
			&& $imageToUse
		) {
		
			return "background: rgba($r, $g, $b, $colorOpacity);";
		}

		/*
		--------------------------------------------------
		Kein Hintergrund
		--------------------------------------------------
		*/

		if ($node->display_mode === 'none') {

			return "background:transparent;";
		}

		/*
		--------------------------------------------------
		Normale Farbe
		--------------------------------------------------
		*/

		return
			"background: rgba($r, $g, $b, $colorOpacity);";
	}
	
		public static function buildLabelStyle($node)
	{
	
		$style = '';
	
		/*
		--------------------------------------------------
		Label Hintergrund
		--------------------------------------------------
		*/
	
		if ((int)$node->label_background === 1) {
	
			$style .= "
				background: rgba(255,255,255,0.75);
				border-radius: 12px;
				padding: 10px;
			";
		}
	
		/*
		--------------------------------------------------
		Position
		--------------------------------------------------
		*/
	
		$style .= "
			left:
			" . (
				$node->label_x !== null
				? (int)$node->label_x
				: -5
			) . "px;
	
			top:
			" . (
				$node->label_y !== null
				? (int)$node->label_y
				: ($node->radius - 25)
			) . "px;
	
			font-size:
			" . (
				$node->font_size ?: 18
			) . "px;
		";
	
		return $style;
	}
}
