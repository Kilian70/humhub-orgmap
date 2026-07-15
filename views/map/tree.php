<?php

use yii\helpers\Html;
$nodes = $treeNodes;
$nodesByOrgan = [];
$nodesWithoutOrgan = [];

foreach ($nodes as $treeNode) {
	if (empty($treeNode->organ_id)) {
		$nodesWithoutOrgan[] = $treeNode;
		continue;
	}

	$nodesByOrgan[$treeNode->organ_id][] = $treeNode;
}

$typeLabels = [

	'core' => 'Kern',

	'organ' => 'Organ',

	'space' => 'Space',

	'tool' => 'Tool',

	'external' => 'Extern',
];

?>

<div class="tree-header">

	<div class="tree-header-top">

		<h3>Navigation</h3>

		<div class="tree-actions">

			<button
				type="button"
				class="btn btn-default btn-sm"
				id="tree-expand-all"
				aria-label="<?= Yii::t('OrgmapModule.base', 'Alle Gruppen öffnen') ?>"
			>
				▾
			</button>

			<button
				type="button"
				class="btn btn-default btn-sm"
				id="tree-collapse-all"
				aria-label="<?= Yii::t('OrgmapModule.base', 'Alle Gruppen schliessen') ?>"
			>
				▸
			</button>

		</div>

	</div>

	<input
		type="text"
		id="tree-search"
		class="form-control"
		placeholder="Suche..."
		aria-label="<?= Yii::t('OrgmapModule.base', 'Organisation durchsuchen') ?>"
		aria-describedby="tree-search-status"
	>
	<div id="tree-search-status" class="tree-search-status" aria-live="polite"></div>

</div>


<?php foreach ($organs as $organ): ?>

<?php

$organNodes = $nodesByOrgan[$organ->id] ?? [];
$count = count($organNodes);

if ($count === 0) {
	continue;
}

?>

<h2
	class="tree-organ-title tree-toggle"
	data-organ-id="<?= $organ->id ?>"
	role="button"
	tabindex="0"
	aria-expanded="true"
>

	<span class="tree-toggle-icon">

		▾

	</span>

	<span class="tree-title-text">

		<?= htmlspecialchars($organ->name) ?>
	
	</span>
	
	<span class="tree-count">
	
		(<?= $count ?>)
	
	</span>

</h2>

<div
	class="tree-organ-content"
	data-organ-content="<?= $organ->id ?>"
>

	<div class="tree-group-grid">


	<?php foreach ($organNodes as $node): ?>

		<?php

		$link = '#';

		if (
			$node->link_type === 'external'
			&& !empty($node->url)
		) {

				$link = preg_match('~^https?://~i', $node->url) ? $node->url : '#';
		}

		elseif (
			$node->space_id
			&& $node->link_type === 'space_home'
		) {

			$space = $node->space;

			if ($space) {

				$link = $space->getUrl();
			}
		}

		elseif (
			$node->space_id
			&& $node->link_type === 'space_about'
		) {

			$space = $node->space;

			if ($space) {

				$link =
					rtrim($space->getUrl(), '/')
					. '/about';
			}
		}

		if ($link === '#' && $node->space) {
			$link = $node->space->getUrl();
		}

		?>

<div
	class="tree-card"
		data-search="<?= Html::encode(mb_strtolower(
			$node->title . ' ' .
			$node->subtitle . ' ' .
			$node->type . ' ' .
			($typeLabels[$node->type] ?? '') . ' ' .
			$organ->name
		)) ?>"
	style="
		border-left:
				6px solid <?= Html::encode($node->color ?: '#999') ?>;
	"
>
<a
	href="<?= Html::encode($link) ?>"
	class="tree-link"
	data-node-id="<?= $node->id ?>"
	<?= $node->open_in_new_tab ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
>

	<div class="tree-card-title">

		<?php if (!empty($node->icon_class)): ?>
	
			<i class="fa <?= Html::encode($node->icon_class) ?>"></i>
	
		<?php endif; ?>
	
		<strong>
	
			<?= Html::encode($node->title) ?>
	
		</strong>
	
	</div>
	
	<?php if (!empty($node->subtitle)): ?>
	
		<small class="tree-card-subtitle">
	
			<?= Html::encode($node->subtitle) ?>
	
		</small>
	
	<?php endif; ?>

</a>

		</div>

	<?php endforeach; ?>

	</div>

</div>

<?php endforeach; ?>


<?php if (!empty($nodesWithoutOrgan)): ?>

	<h2 class="tree-organ-title">

		Ohne Organ

	</h2>

	<div class="tree-group-grid">

	<?php foreach ($nodesWithoutOrgan as $node): ?>
	<?php
	$link = '#';
	if ($node->link_type === 'external' && !empty($node->url)) {
		$link = preg_match('~^https?://~i', $node->url) ? $node->url : '#';
	} elseif ($node->space && $node->link_type === 'space_home') {
		$link = $node->space->getUrl();
	} elseif ($node->space && $node->link_type === 'space_about') {
		$link = rtrim($node->space->getUrl(), '/') . '/about';
	}
	if ($link === '#' && $node->space) {
		$link = $node->space->getUrl();
	}
	?>

<div
	class="tree-card"
		data-search="<?= Html::encode(mb_strtolower(
			$node->title . ' ' .
			$node->subtitle . ' ' .
			$node->type . ' ' .
			($typeLabels[$node->type] ?? '')
		)) ?>"
	style="
		border-left:
				6px solid <?= Html::encode($node->color ?: '#999') ?>;
	"
>
	<a
		href="<?= Html::encode($link) ?>"
		class="tree-link"
		data-node-id="<?= (int) $node->id ?>"
		<?= $node->open_in_new_tab ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
	>

			<div class="tree-card-title">

			<?php if (!empty($node->icon_class)): ?>
		
				<i class="fa <?= Html::encode($node->icon_class) ?>"></i>
		
			<?php endif; ?>
		
			<strong>
		
				<?= Html::encode($node->title) ?>
		
			</strong>
		
		</div>
		
		<?php if (!empty($node->subtitle)): ?>
		
			<small class="tree-card-subtitle">
		
				<?= Html::encode($node->subtitle) ?>
		
			</small>
		
		<?php endif; ?>
	</a>

		</div>

	<?php endforeach; ?>

	</div>

<?php endif; ?>
