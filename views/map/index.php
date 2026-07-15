	<?php
	
		use yii\helpers\Url;
		use yii\helpers\Html;
	use humhub\modules\orgmap\assets\OrgMapAsset;
	use humhub\modules\orgmap\helpers\ConnectionSvgHelper;
	use humhub\modules\orgmap\helpers\ConnectionRenderHelper;
	
	OrgMapAsset::register($this);
	
	$this->title = Yii::$app
	->getModule('orgmap')
	->settings
	->get(
		'moduleTitle',
		'ORG.'
	);
	
	/*
	Bearbeitungsmodus nur für Admin
	*/
	
	$editMode =
		Yii::$app->user->isAdmin()
		&& Yii::$app->request->get('edit') == 1;
	
	/*
	Linien anzeigen
	*/
	
	$showLines = Yii::$app
		->getModule('orgmap')
		->settings
		->get('showLines', true);
	
	/*
	View Mode
	*/
	
	$viewMode = 'split';
	
	?>

	<?php
	
	/*
	--------------------------------------------------
	Toolbar
	--------------------------------------------------
	*/
	?>

	<div class="container">
	
			<h1><?= Html::encode($this->title) ?></h1>
	
			<p class="orgmap-toolbar">

        <?php if (Yii::$app->user->isAdmin()): ?>

            <a
                href="<?= Url::to(['/orgmap/admin/index']) ?>"
                class="btn btn-secondary btn-sm"
                title="<?= Yii::t('OrgmapModule.base', 'Verwaltung') ?>"
            >
                <i class="fa fa-cog"></i>
            </a>

<a
	href="<?= Url::to(['/orgmap/map/index', 'edit' => 1]) ?>"
	class="btn btn-secondary btn-sm"
	title="<?= Yii::t('OrgmapModule.base', 'Bearbeitungsmodus') ?>"
>
	<i class="fa fa-pencil"></i>
</a>

        <?php endif; ?>

        
<button
	type="button"
	class="btn btn-secondary btn-sm orgmap-view-btn"
	data-view="tree"
>
	<?= Yii::t(
		'OrgmapModule.base',
		'Navigation'
	) ?>
</button>

<button
	type="button"
	class="btn btn-secondary btn-sm orgmap-view-btn"
	data-view="map"
>
	<?= Yii::t(
		'OrgmapModule.base',
		'Karte'
	) ?>
</button>

<button
	type="button"
	class="btn btn-secondary btn-sm orgmap-view-btn"
	data-view="split"
>
	<?= Yii::t(
		'OrgmapModule.base',
		'Split'
	) ?>
</button>

<button
    type="button"
    id="zoom-out"
    class="btn btn-secondary btn-sm"
    title="<?= Yii::t(
        'OrgmapModule.base',
        'Verkleinern'
    ) ?>"
    aria-label="<?= Yii::t(
        'OrgmapModule.base',
        'Verkleinern'
    ) ?>"
    data-bs-toggle="tooltip"
>
    −
</button>

<button
	type="button"
	id="zoom-in"
    class="btn btn-secondary btn-sm"
    title="<?= Yii::t(
        'OrgmapModule.base',
        'Vergrössern'
    ) ?>"
    aria-label="<?= Yii::t(
        'OrgmapModule.base',
        'Vergrössern'
    ) ?>"
    data-bs-toggle="tooltip"
>
    +
</button>

<button
	type="button"
	id="orgmap-fullscreen"
	class="btn btn-secondary btn-sm"
	title="<?= Yii::t('OrgmapModule.base', 'Vollbild') ?>"
	aria-label="<?= Yii::t('OrgmapModule.base', 'Vollbild') ?>"
>
	<i class="fa fa-expand"></i>
</button>

<button
	type="button"
	id="orgmap-print"
	class="btn btn-secondary btn-sm"
	title="<?= Yii::t('OrgmapModule.base', 'Drucken') ?>"
	aria-label="<?= Yii::t('OrgmapModule.base', 'Drucken') ?>"
>
	<i class="fa fa-print"></i>
</button>
    

<?php

/*
--------------------------------------------------
Workspace
--------------------------------------------------
*/

$workspaceSize = Yii::$app
	->getModule('orgmap')
	->settings
	->get('workspaceSize', 'medium');

$workspaceWidth = 2400;
$workspaceHeight = 1350;

if ($workspaceSize === 'small') {

	$workspaceWidth = 1600;
	$workspaceHeight = 900;
}

if ($workspaceSize === 'large') {

	$workspaceWidth = 3200;
	$workspaceHeight = 1800;
}

if ($workspaceSize === 'custom') {

	$workspaceWidth = Yii::$app
		->getModule('orgmap')
		->settings
		->get(
			'workspaceWidth',
			2400
		);

	$workspaceHeight = Yii::$app
		->getModule('orgmap')
		->settings
		->get(
			'workspaceHeight',
			1350
		);
}

/*
foreach ($nodes as $workspaceNode) {

    if (
        $workspaceNode->is_background
        && $workspaceNode->width
        && $workspaceNode->height
    ) {

        $workspaceWidth = max(
            $workspaceWidth,
            $workspaceNode->pos_x
                + ($workspaceNode->width / 2)
        );

        $workspaceHeight = max(
            $workspaceHeight,
            $workspaceNode->pos_y
                + ($workspaceNode->height / 2)
        );
    }
}
*/
	
	?>
	
<div
	class="orgmap-layout"
	data-view-mode="<?= $viewMode ?>"
>

	<div class="orgmap-sidebar">

			<?= $this->render('tree', [
				'organs' => $organs,
				'treeNodes' => $treeNodes,
			]) ?>

	</div>

	<div class="orgmap-main">
	
	<div class="orgmap-scroll">
	
<div
    class="orgmap-wrapper"

	data-workspace-size="<?= Html::encode($workspaceSize) ?>"

	data-edit-mode="<?= $editMode ? 1 : 0 ?>"
	data-save-position-url="<?= Html::encode(Url::to(['/orgmap/map/save-position'])) ?>"
	data-save-label-url="<?= Html::encode(Url::to(['/orgmap/admin/save-connection-label'])) ?>"
	data-edit-connection-url="<?= Html::encode(Url::to(['/orgmap/admin/edit-connection'])) ?>"

    style="
        width: <?= $workspaceWidth ?>px;
        height: <?= $workspaceHeight ?>px;
    "
>

	
			<svg
				id="orgmap-svg"
				width="<?= $workspaceWidth ?>"
				height="<?= $workspaceHeight ?>"
				viewBox="0 0 <?= $workspaceWidth ?> <?= $workspaceHeight ?>"
			>
	
		<?php
		
		/*
		--------------------------------------------------
		SVG Definitionen
		--------------------------------------------------
		*/
		?>
		
		<defs>

			<marker
				id="arrow-end"
				markerWidth="10"
				markerHeight="10"
					markerUnits="strokeWidth"
				refX="8"
				refY="3"
				orient="auto"
			>

				<path
					d="M0,0 L0,6 L9,3 z"
					fill="context-stroke"
				/>

			</marker>

			<marker
				id="arrow-start"
				markerWidth="10"
				markerHeight="10"
					markerUnits="strokeWidth"
				refX="1"
				refY="3"
				orient="auto"
			>

				<path
					d="M9,0 L9,6 L0,3 z"
					fill="context-stroke"
				/>

			</marker>

		</defs>

		<?php
		/*
		--------------------------------------------------
		Linien Rendering
		--------------------------------------------------
		*/
		?>
		
		<?php if ($showLines): ?>
		
		<?php

		$useConnectionSystem = Yii::$app
			->getModule('orgmap')
			->settings
			->get('useConnectionSystem', false);

		$nodesById = [];
		foreach ($nodes as $indexedNode) {
			$nodesById[$indexedNode->id] = $indexedNode;
		}
		
		?>
		
				<?php foreach ($connections as $connection): ?>

		<?php
	
		$data =
			ConnectionSvgHelper::buildConnectionData(
				$connection,
				$nodesById
			);
		
		if (!$data) {
			continue;
		}
		
		$from = $data['from'];
		
		$to = $data['to'];
		
		$x1 = $data['x1'];
		
		$y1 = $data['y1'];
		
		$x2 = $data['x2'];
		
		$y2 = $data['y2'];
		
		$dx = $data['dx'];
		
		$dy = $data['dy'];
		
		$distance = $data['distance'];
		
		$angle = $data['angle'];
	
		if ($distance == 0) {
			continue;
		}

		$curve = $connection->curve ?: 0;
			
		$geometry =
			ConnectionSvgHelper::buildConnectionGeometry(
				$x1,
				$y1,
				$x2,
				$y2,
				$curve
			);

		$midpoint =
			ConnectionSvgHelper::buildMidpoint(
				$x1,
				$y1,
				$x2,
				$y2
			);
			
		$midX = $geometry['midX'];

		$midY = $geometry['midY'];
	
		?>
	

		<?php
		
		$d = ConnectionSvgHelper::buildPath(
			$geometry,
			$x1,
			$y1,
			$x2,
			$y2
		);
				
		?>
		
		<?php
			
		$dasharray =
			ConnectionSvgHelper::buildStrokeDasharray(
				$connection->style
			);

		$pathAttributes =
			ConnectionRenderHelper::renderPathAttributes(
				$connection,
				$dasharray
			);

		$arrowAttributes =
			ConnectionRenderHelper::renderArrowAttributes(
				$connection
			);
		
		?>

		<path
		
			d="<?= $d ?>"
		
			<?= $pathAttributes ?>
			
			<?= $arrowAttributes ?>
		/>
	
			<?php
			
			$labelPosition =
				ConnectionSvgHelper::buildLabelPosition(
					$geometry,
					$connection->label_offset_x ?: 0,
					$connection->label_offset_y ?: 0
				);
			
			$labelX = $labelPosition['x'];
			
			$labelY = $labelPosition['y'];	
			
			?>
			
			<?php

			$transform =
				ConnectionSvgHelper::buildLabelTransform(
					$connection->label_rotation,
					$angle,
					$labelX,
					$labelY
				);
			
			?>
			
			<?php

			$textAttributes =
				ConnectionRenderHelper::renderTextAttributes(
					$connection
				);
			
			?>
			
			<?php if (!empty($connection->label)): ?>
			
			<?= ConnectionRenderHelper::renderLabel(
				$connection,
				$labelX,
				$labelY,
				$transform,
				$textAttributes
			) ?>
			
			<?php endif; ?>
	
	<?php endforeach; ?>

<?php endif; ?>

</svg>


<?php
/*
--------------------------------------------------
Node Rendering
--------------------------------------------------
*/
?>

<div class="orgmap-background-layer">

	<?php foreach ($nodes as $node): ?>

		<?php if ($node->is_background): ?>

			<?= $this->render('_node', [

				'node' => $node,

				'editMode' => $editMode

			]) ?>

		<?php endif; ?>

	<?php endforeach; ?>

</div>

<div class="orgmap-layer">

	<?php foreach ($nodes as $node): ?>

		<?php if (!$node->is_background): ?>

			<?= $this->render('_node', [

				'node' => $node,

				'editMode' => $editMode

			]) ?>

		<?php endif; ?>

	<?php endforeach; ?>

</div>

</div>

</div>

</div>

</div>
