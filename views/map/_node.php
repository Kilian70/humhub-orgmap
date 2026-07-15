<?php

use humhub\modules\orgmap\helpers\NodeStyleHelper;

use yii\helpers\Html;

/*
--------------------------------------------------
Link & Bilddaten
--------------------------------------------------
*/

$link = '#';

$spaceImage = null;

if ($node->space_id) {

	$space = $node->space;

	if ($space) {

		if ($node->link_type === 'space_about') {

			$link =
				rtrim($space->getUrl(), '/')
				. '/about';

		} elseif (
			$node->link_type === 'space_home'
		) {

			$link = $space->getUrl();
		}

		try {

			$spaceImage = $space
				->getProfileImage()
				->getUrl();

		} catch (\Exception $e) {

			$spaceImage = null;
		}
	}
}

if (
	$node->link_type === 'external'
	&& !empty($node->url)
) {
	$link = preg_match('~^https?://~i', $node->url) ? $node->url : '#';
}

if ($link === '#' && isset($space) && $space) {
	$link = $space->getUrl();
}

/*
--------------------------------------------------
Bildquelle
--------------------------------------------------
*/

$imageToUse = null;

if (
	$node->image_source === 'space'
	&& $spaceImage
) {

	$imageToUse = $spaceImage;
}

if (
	$node->image_source === 'custom'
	&& !empty($node->custom_image)
) {

	$imageToUse = $node->custom_image;
}

if (
	$node->image_source === 'asset'
	&& $node->asset_id
) {

	$asset = $node->asset;

	if (
		$asset
		&& !empty($asset->filename)
	) {

		$imageToUse =
			Yii::getAlias('@web')
			. '/uploads/orgmap/assets/'
			. $asset->filename;
	}
}

/*
--------------------------------------------------
Organ-Farbe übernehmen
--------------------------------------------------
*/

if (
	empty($node->color)
	&& $node->organ
	&& !empty($node->organ->color)
) {

	$node->color =
		$node->organ->color;
}

/*
--------------------------------------------------
Nodegrössen
--------------------------------------------------
*/

$nodeWidth =
	$node->width
	?: ($node->radius * 2);

$nodeHeight =
	$node->height
	?: ($node->radius * 2);


/*
--------------------------------------------------
Shape-Helfer
--------------------------------------------------
*/

$isCircle =
	($node->shape ?: 'circle')
	=== 'circle';

$isRectangle =
	in_array(

		$node->shape,

		[
			'rectangle',
			'panel',
			'legend'
		]
	);


/*
--------------------------------------------------
Hintergrundstil
--------------------------------------------------
*/

$backgroundStyle =
	NodeStyleHelper::buildBackgroundStyle(
		$node,
		$imageToUse
	);

$labelStyle =
	NodeStyleHelper::buildLabelStyle($node);

$nodeClasses = [

	'org-node-html',

	'org-node-' . ($node->shape ?: 'circle'),
];

if ($node->is_background) {

	$nodeClasses[] =
		'org-node-is-background';
}

$tabIndex = '0';

if ($node->is_background) {

	$tabIndex = '-1';
}

if (!empty($node->content)) {

	$nodeClasses[] =
		'org-node-has-content';
}

if (!empty($imageToUse)) {

	$nodeClasses[] =
		'org-node-has-image';
}

$isBackground =
	(bool) $node->is_background;

?>

<?php

$left =
	(int) ($node->pos_x - ($nodeWidth / 2));

$top =
	(int) ($node->pos_y - ($nodeHeight / 2));


?>


<div class="org-node">

	<a
		class="<?= Html::encode(implode(' ', $nodeClasses)) ?>"

		data-id="<?= $node->id ?>"
		data-x="<?= (int) $node->pos_x ?>"
		data-y="<?= (int) $node->pos_y ?>"

		<?php if (!$editMode && $tabIndex !== '-1'): ?>

				href="<?= Html::encode($link) ?>"
		
		<?php endif; ?>

			<?= $node->open_in_new_tab ? 'target="_blank" rel="noopener noreferrer"' : '' ?>

		aria-label="<?= Html::encode($node->title) ?>"

		tabindex="<?= $tabIndex ?>"
		
		style="
			left:
			<?= $left ?>px;
		
			top:
			<?= $top ?>px;
		
			width:
			<?= (int) $nodeWidth ?>px;
		
			height:
			<?= (int) $nodeHeight ?>px;
		
			z-index:
			<?= $node->sort_order ?: 1 ?>;
		
			border-color:
				<?= Html::encode($node->border_color ?: ($node->color ?: '#2196f3')) ?>;
		
			border-width:
			<?= (int) ($node->border_width ?? 3) ?>px;
		"
	>
		
		<div
			class="org-node-background"
			style="<?= Html::encode($backgroundStyle) ?>"
		></div>

		<?php if ((int)$node->show_label === 1): ?>

			<div
				class="org-node-label <?= $node->label_background ? 'has-background' : '' ?>"
				style="<?= Html::encode($labelStyle) ?>"
			>

				<?php if (!empty($node->content)): ?>

					<div class="org-node-content">

						<strong>
							<?= htmlspecialchars($node->title) ?>
						</strong>

						<?= \yii\helpers\HtmlPurifier::process(
							$node->content,
							[
								'HTML.Allowed' =>
									'h1,h2,h3,h4,p,br,strong,em,ul,ol,li,span'
							]
						) ?>

					</div>

				<?php else: ?>

					<?= nl2br(
						htmlspecialchars($node->title)
					) ?>

				<?php endif; ?>

			</div>

		<?php endif; ?>

		<?php if ($editMode): ?>

			<div class="org-node-resize"></div>

		<?php endif; ?>

	</a>

</div>
