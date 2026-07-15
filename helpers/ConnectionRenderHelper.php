<?php

namespace humhub\modules\orgmap\helpers;

use yii\helpers\Html;

class ConnectionRenderHelper
{

	public static function renderPathAttributes(
		$connection,
		$dasharray
	) {

		$attributes = [];

			$attributes[] =
				'fill="none"';

		$attributes[] = 'vector-effect="non-scaling-stroke"';
			
		$attributes[] =
		'class="org-connection org-connection-type-'
			. Html::encode($connection->type ?: 'custom')
		. '"';
		
		$attributes[] =
			'data-connection-id="' .
			(int) $connection->id .
			'"';

		$attributes[] = 'data-from-node-id="' . (int) $connection->from_node_id . '"';
		$attributes[] = 'data-to-node-id="' . (int) $connection->to_node_id . '"';
		$attributes[] = 'data-curve="' . (int) ($connection->curve ?: 0) . '"';

		$attributes[] =
			'stroke="' .
				Html::encode($connection->color ?: '#666')
			. '"';

		$attributes[] =
			'stroke-width="' .
				(int) ($connection->width ?: 2)
			. '"';

		if ($dasharray) {

			$attributes[] =
				'stroke-dasharray="' .
				$dasharray
				. '"';
		}

		return implode("\n", $attributes);
	}
	
	public static function renderArrowAttributes(
		$connection
	) {
	
		$attributes = [];
	
		if (
			ConnectionSvgHelper::hasEndArrow(
				$connection->arrow
			)
		) {
	
			$attributes[] =
				'marker-end="url(#arrow-end)"';
		}
	
		if (
			ConnectionSvgHelper::hasStartArrow(
				$connection->arrow
			)
		) {
	
			$attributes[] =
				'marker-start="url(#arrow-start)"';
		}
	
		return implode("\n", $attributes);
	}
	
	public static function renderTextAttributes(
		$connection
	) {
	
		$attributes = [];
	
			$attributes[] =
				'font-size="' .
				max(12, (int) ($connection->font_size ?: 12))
				. '"';
	
		$attributes[] =
			'font-weight="' .
				Html::encode($connection->font_weight ?: 'normal')
			. '"';
	
		$attributes[] =
			'text-anchor="middle"';
	
		$attributes[] =
			'fill="#333"';
	
		$attributes[] =
			'stroke="#fff"';
	
		$attributes[] =
			'stroke-width="4"';
	
		$attributes[] =
			'paint-order="stroke"';
	
		return implode("\n", $attributes);
	}
	
	public static function renderLabel(
		$connection,
		$labelX,
		$labelY,
		$transform,
		$textAttributes
	) {
	
		if (empty($connection->label)) {

	return '';
	}
	
	return '
		<text
			class="
				org-connection-label
					org-connection-label-type-' . Html::encode($connection->type) . '
			"
				data-connection-id="' . (int) $connection->id . '"
			data-offset-x="' . ($connection->label_offset_x ?: 0) . '"
				data-offset-y="' . ($connection->label_offset_y ?: 0) . '"
				data-label-rotation="' . Html::encode($connection->label_rotation ?: 'horizontal') . '"
			x="' . $labelX . '"
			y="' . $labelY . '"
			transform="' . $transform . '"
			' . $textAttributes . '
		>
			' . htmlspecialchars($connection->label) . '
		</text>
	';	
	}
}
