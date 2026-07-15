<?php

namespace humhub\modules\orgmap\helpers;

class ConnectionSvgHelper
{

	public static function buildPath(
		$geometry,
		$x1,
		$y1,
		$x2,
		$y2
	) {

		$dx = $x2 - $x1;

		$dy = $y2 - $y1;

		$distance =
			sqrt($dx * $dx + $dy * $dy);

		if ($distance == 0) {

			return '';
		}

		$controlX = $geometry['controlX'];
		
		$controlY = $geometry['controlY'];

		return "
			M {$x1} {$y1}
			Q {$controlX} {$controlY}
			  {$x2} {$y2}
		";
	}
	
	public static function buildLabelPosition(
		$geometry,
		$offsetX = 0,
		$offsetY = 0
	) {
	
		$midX = $geometry['midX'];
	
		$midY = $geometry['midY'];
	
		if (
			(int)$offsetX === 0
			&& (int)$offsetY === 0
		) {
	
				return [
		
					'x' => ($midX + $geometry['controlX']) / 2,
		
					'y' => ($midY + $geometry['controlY']) / 2
				];
		}
	
		return [
	
			'x' => $offsetX,
	
			'y' => $offsetY
		];
	}

	public static function buildAngle(
		$dx,
		$dy
	) {
	
		$angle =
			rad2deg(
				atan2($dy, $dx)
			);
	
		if ($angle > 90 || $angle < -90) {
	
			$angle += 180;
		}
	
		return $angle;
	}
	
	public static function buildDistance(
		$dx,
		$dy
	) {
	
		return sqrt(
			($dx * $dx)
			+ ($dy * $dy)
		);
	}
	
	public static function buildMidpoint(
		$x1,
		$y1,
		$x2,
		$y2
	) {
	
		return [
	
			'x' => ($x1 + $x2) / 2,
	
			'y' => ($y1 + $y2) / 2
		];
	}
	
	public static function buildConnectionGeometry(
		$x1,
		$y1,
		$x2,
		$y2,
		$curve
	) {
	
		$dx = $x2 - $x1;
	
		$dy = $y2 - $y1;
	
		$distance = self::buildDistance(
			$dx,
			$dy
		);
	
		if ($distance == 0) {
	
			return null;
		}
	
		$midpoint = self::buildMidpoint(
			$x1,
			$y1,
			$x2,
			$y2
		);
	
		$midX = $midpoint['x'];
	
		$midY = $midpoint['y'];
	
		$normalX = -($dy / $distance);
	
		$normalY = ($dx / $distance);
	
		$controlX =
			$midX + ($normalX * $curve);
	
		$controlY =
			$midY + ($normalY * $curve);
	
		return [
	
			'dx' => $dx,
			'dy' => $dy,
		
			'distance' => $distance,
		
			'midX' => $midX,
			'midY' => $midY,
		
			'controlX' => $controlX,
			'controlY' => $controlY,
		
			'curve' => $curve
		];
	}
	
	public static function buildEdgePoints(
		$fromX,
		$fromY,
		$fromRadiusX,
		$toX,
		$toY,
		$toRadiusX,
		$fromRadiusY = null,
		$toRadiusY = null
	) {
		$fromRadiusY = $fromRadiusY ?: $fromRadiusX;
		$toRadiusY = $toRadiusY ?: $toRadiusX;
	
		$dx = $toX - $fromX;
	
		$dy = $toY - $fromY;
	
		$distance = self::buildDistance(
			$dx,
			$dy
		);
	
		if ($distance == 0) {
	
			return null;
		}
	
		$fromScale = 1 / sqrt(
			($dx * $dx) / ($fromRadiusX * $fromRadiusX)
			+ ($dy * $dy) / ($fromRadiusY * $fromRadiusY)
		);
		$toScale = 1 / sqrt(
			($dx * $dx) / ($toRadiusX * $toRadiusX)
			+ ($dy * $dy) / ($toRadiusY * $toRadiusY)
		);

		$x1 = $fromX + ($dx * $fromScale);
		$y1 = $fromY + ($dy * $fromScale);
		$x2 = $toX - ($dx * $toScale);
		$y2 = $toY - ($dy * $toScale);
	
		return [
	
			'x1' => $x1,
			'y1' => $y1,
	
			'x2' => $x2,
			'y2' => $y2,
	
			'dx' => $dx,
			'dy' => $dy,
	
			'distance' => $distance
		];
	}
	
	public static function buildStrokeDasharray(
		$style
	) {
	
		if ($style === 'dashed') {
	
			return '8 4';
		}
	
		if ($style === 'dotted') {
	
			return '2 6';
		}
	
		return null;
	}
	
	public static function hasStartArrow(
		$arrow
	) {
	
		return (
			$arrow === 'start'
			|| $arrow === 'both'
		);
	}
	
	public static function hasEndArrow(
		$arrow
	) {
	
		return (
			$arrow === 'end'
			|| $arrow === 'both'
		);
	}
	
	public static function buildLabelTransform(
		$rotation,
		$angle,
		$labelX,
		$labelY
	) {
	
		if ($rotation !== 'auto') {
			return '';
		}
	
		return '
			rotate(
				' . $angle . ',
				' . $labelX . ',
				' . $labelY . '
			)
		';
	}
	
	public static function findConnectionNodes(
		$connection,
		$nodes
	) {
		if (
			isset($nodes[$connection->from_node_id], $nodes[$connection->to_node_id])
			&& (int) $nodes[$connection->from_node_id]->id === (int) $connection->from_node_id
			&& (int) $nodes[$connection->to_node_id]->id === (int) $connection->to_node_id
		) {
			return [
				'from' => $nodes[$connection->from_node_id],
				'to' => $nodes[$connection->to_node_id],
			];
		}
	
		$from = null;
	
		$to = null;
	
		foreach ($nodes as $n) {
	
			if ($n->id == $connection->from_node_id) {
	
				$from = $n;
			}
	
			if ($n->id == $connection->to_node_id) {
	
				$to = $n;
			}
		}
	
		if (!$from || !$to) {
	
			return null;
		}
	
		return [
	
			'from' => $from,
	
			'to' => $to
		];
	}
	
	public static function buildConnectionData(
		$connection,
		$nodes
	) {
	
		$connectionNodes =
			self::findConnectionNodes(
				$connection,
				$nodes
			);
	
		if (!$connectionNodes) {
	
			return null;
		}
	
		$from = $connectionNodes['from'];
	
		$to = $connectionNodes['to'];
	
			$edgePoints =
			self::buildEdgePoints(
				$from->pos_x,
					$from->pos_y,
					($from->width ?: ($from->radius * 2)) / 2,
					$to->pos_x,
					$to->pos_y,
					($to->width ?: ($to->radius * 2)) / 2,
					($from->height ?: ($from->radius * 2)) / 2,
					($to->height ?: ($to->radius * 2)) / 2
				);

			if ($edgePoints === null) {
				return null;
			}
	
		$dx = $edgePoints['dx'];
	
		$dy = $edgePoints['dy'];
	
		$distance = $edgePoints['distance'];
	
		$angle = self::buildAngle(
			$dx,
			$dy
		);
	
		return [
	
			'from' => $from,
			'to' => $to,
	
			'x1' => $edgePoints['x1'],
			'y1' => $edgePoints['y1'],
	
			'x2' => $edgePoints['x2'],
			'y2' => $edgePoints['y2'],
	
			'dx' => $dx,
			'dy' => $dy,
	
			'distance' => $distance,
	
			'angle' => $angle
		];
	}
		
}
