<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\models\Organ;
use humhub\modules\orgmap\models\Connection;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\orgmap\assets\OrgMapAdminAsset;
use humhub\modules\orgmap\helpers\IconPickerHelper;
use humhub\modules\orgmap\widgets\IconPickerWidget;
use humhub\modules\orgmap\helpers\ActionButtonHelper;


/** @var $model */

OrgMapAdminAsset::register($this);

$this->title = Yii::t('OrgmapModule.base', 'ORG. Kreis');
?>

<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

<p>

	<?= ActionButtonHelper::back(
		['/orgmap/admin/index']
	) ?>

</p>

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->errorSummary($model, [
		'class' => 'alert alert-danger',
		'role' => 'alert',
	]) ?>

    <?= $form->field($model, 'space_id')
        ->hiddenInput()
        ->label(false) ?>

	<?= $form->field($model, 'title')->textInput() ?>

	<?= $form->field($model, 'subtitle')->textInput() ?>
	
	<?= IconPickerWidget::widget([
		'model' => $model,
		'attribute' => 'icon_class',
	]) ?>

		<label class="control-label" for="node-content">
		Inhalt
	</label>
	
	<div class="orgmap-content-tools">
	
<button
	type="button"
	class="btn btn-light btn-sm orgmap-insert-tag"
	data-tag="<h3></h3>"
>
	H3
</button>

<button
	type="button"
	class="btn btn-light btn-sm orgmap-insert-tag"
	data-tag="<p></p>"
>
	Text
</button>

<button
	type="button"
	class="btn btn-light btn-sm orgmap-insert-tag"
	data-tag="<strong></strong>"
>
	Fett
</button>

<button
	type="button"
	class="btn btn-light btn-sm orgmap-insert-tag"
	data-tag="<ul>\n<li></li>\n</ul>"
>
	Liste
</button>
	
	</div>
	
	<?= Html::activeTextarea(
		$model,
		'content',
		[
			'class' => 'form-control',
			'rows' => 6,
			'id' => 'node-content'
		]
	) ?>
	
	<?= $form->field($model, 'organ_id')->dropDownList(
	
		ArrayHelper::map(
			Organ::find()->all(),
			'id',
			'name'
		),
	
		[
			'prompt' => Yii::t(
				'OrgmapModule.base',
				'- Kein Organ -'
			)
		]
	
	) ?>
	
	<hr>
	
	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Darstellung'
		) ?>
	</h2>
	
		<?= $form->field($model, 'display_mode')->dropDownList([
		
			'color' => Yii::t(
				'OrgmapModule.base',
				'Nur Farbe'
			),
		
			'image' => Yii::t(
				'OrgmapModule.base',
				'Nur Bild'
			),
		
			'mixed' => Yii::t(
				'OrgmapModule.base',
				'Bild + Farbe'
			),
		
			'none' => Yii::t(
				'OrgmapModule.base',
				'Kein Hintergrund'
			),
		
		]) ?>
		
		<?= $form->field($model, 'background_size')
			->label(
				Yii::t(
					'OrgmapModule.base',
					'Bildanpassung'
				)
			)
			->dropDownList(
				$model::getBackgroundSizeOptions()
			) ?>
		
		<?= $form->field($model, 'shape')->dropDownList(
		Node::getShapeOptions()
	) ?>
	
	<?= $form->field($model, 'color')->input(
		'color',
		[
			'class' => 'form-control'
		]
	) ?>
	
	<?= $form->field($model, 'border_color')->input(
		'color',
		[
			'class' => 'form-control'
		]
	) ?>
	
	<?= $form->field($model, 'border_width')->dropDownList([
		0 => '0 px',
		1 => '1 px',
		2 => '2 px',
		3 => '3 px',
		4 => '4 px',
		5 => '5 px',
	]) ?>
	
	<?= $form->field($model, 'image_source')->dropDownList([

		'none' => Yii::t(
			'OrgmapModule.base',
			'Keine'
		),
	
		'space' => Yii::t(
			'OrgmapModule.base',
			'Spacebild'
		),
	
		'custom' => Yii::t(
			'OrgmapModule.base',
			'Bild URL'
		),
	
		'asset' => Yii::t(
			'OrgmapModule.base',
			'ORG Asset'
		),
	
	]) ?>
	
	<?= $form->field($model, 'link_type')->dropDownList([
	
		'' => Yii::t(
			'OrgmapModule.base',
			'Kein Link'
		),
	
		'space_home' => Yii::t(
			'OrgmapModule.base',
			'Space-Startseite'
		),
	
		'space_about' => Yii::t(
			'OrgmapModule.base',
			'Space-About-Seite'
		),
	
		'external' => Yii::t(
			'OrgmapModule.base',
			'Externer Link'
		),
	
	]) ?>
	
	<?= $form->field($model, 'url')->textInput() ?>


	<div class="orgmap-asset-field">

		<?= $form->field($model, 'asset_id')
			->dropDownList(
	
				ArrayHelper::map(
	
					\humhub\modules\orgmap\models\Asset::find()
						->orderBy([
							'title' => SORT_ASC
						])
						->all(),
	
					'id',
	
					'title'
				),
	
				[
					'prompt' => Yii::t(
						'OrgmapModule.base',
						'Kein Asset'
					),
				
					'options' => array_reduce(
				
						\humhub\modules\orgmap\models\Asset::find()->all(),
				
						function ($carry, $asset) {
				
							$carry[$asset->id] = [
				
								'data-image' =>
									Yii::getAlias('@web')
									. '/uploads/orgmap/assets/'
									. $asset->filename
							];
				
							return $carry;
						},
				
						[]
					),
				]
			) ?>
			
		<div class="orgmap-asset-preview">
	
		</div>
	
	</div>	
	
	<div class="orgmap-custom-image-field">
	
		<?= $form->field($model, 'custom_image')
			->textInput() ?>
	
	</div>	

	<?= $form->field($model, 'opacity')->textInput() ?>	
		
	
	<hr>
	
	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Verbindungen'
		) ?>
	</h2>
		
		
	<?= $form->field($model, 'connectionIds')
		->label(false)
		->widget(
	
			Select2::class,
	
		[
			'data' => ArrayHelper::map(
	
				Node::find()
					->where(['!=', 'id', $model->id])
					->orderBy('title')
					->all(),
	
				'id',
				'title'
			),
	
			'options' => [
	
				'multiple' => true,
	
				'placeholder' => Yii::t(
					'OrgmapModule.base',
					'Verbindungen auswählen'
				),
			],
	
			'pluginOptions' => [
	
				'allowClear' => true,
			],
		]
	
	) ?>
	
	<?php

	$allConnections = Connection::find()
		->where([
			'or',
			['from_node_id' => $model->id],
			['to_node_id' => $model->id],
		])
		->all();
	
	?>
	
	<?php if (!$model->isNewRecord): ?>

	<div class="table-responsive" tabindex="0" role="region" aria-label="<?= Yii::t('OrgmapModule.base', 'Verbindungen') ?>">
	<table class="table table-bordered">
		<caption class="sr-only"><?= Yii::t('OrgmapModule.base', 'Verbindungen') ?></caption>

		<thead>

			<tr>

				<th scope="col">
					<?= Yii::t(
						'OrgmapModule.base',
						'Ziel'
					) ?>
				</th>
				
				<th scope="col">
					<?= Yii::t(
						'OrgmapModule.base',
						'Typ'
					) ?>
				</th>

				<th scope="col">
					<?= Yii::t(
						'OrgmapModule.base',
						'Farbe'
					) ?>
				</th>

				<th scope="col">
					<?= Yii::t(
						'OrgmapModule.base',
						'Dicke'
					) ?>
				</th>

				<th scope="col">
					<?= Yii::t(
						'OrgmapModule.base',
						'Stil'
					) ?>
				</th>
				
				<th scope="col">
					<?= Yii::t(
						'OrgmapModule.base',
						'Aktionen'
					) ?>
				</th>

			</tr>

		</thead>

		<tbody>

	<?php foreach ($allConnections as $connection): ?>
	
		<tr>
	
			<td>
			<?php
			
			$targetNode = null;
			
			if ($connection->from_node_id == $model->id) {
			
				$targetNode = $connection->toNode;
			}
			
			if ($connection->to_node_id == $model->id) {
			
				$targetNode = $connection->fromNode;
			}
			
			?>
			
			<?= Html::encode(
				$targetNode->title ?? '-'
			) ?>
			</td>	
			<td>

				<?= Html::encode(
			
					Connection::getTypeOptions()[
						$connection->type
					] ?? Yii::t(
						'OrgmapModule.base',
						'Benutzerdefiniert'
					)
			
				) ?>
			
			</td>
				
			<td>
			
				<div
					style="
						display:flex;
						align-items:center;
						gap:10px;
					"
				>
			
					<div
						style="
							width:20px;
							height:20px;
							border-radius:50%;
							background:
								<?= Html::encode(
									$connection->color
								) ?>;
							border:1px solid #999;
						"
					></div>
			
					<span>
						<?= Html::encode(
							$connection->color
						) ?>
					</span>
			
				</div>
			
			</td>
	
			<td>
				<?= Html::encode(
					$connection->width
				) ?>
			</td>
	
			<td>
				<?= Html::encode(
					$connection->style
				) ?>
			</td>
	
			<td class="orgmap-action-buttons">

				<?= ActionButtonHelper::edit(
					[
						'/orgmap/admin/edit-connection',
						'id' => $connection->id
					]
				) ?>
			
			</td>
			
		</tr>
	
	<?php endforeach; ?>

		</tbody>

	</table>
	</div>

<?php endif; ?>

	
	<hr>

	<h2 class="h3">
	<?= Yii::t(
		'OrgmapModule.base',
		'Position und Grösse'
	) ?>
</h2>

<div class="form-group">

	<button
		type="button"
		id="center-node"
		class="btn btn-default btn-sm"
	>
		<?= Yii::t(
			'OrgmapModule.base',
			'Zentrieren'
		) ?>
	</button>

	<button
		type="button"
		id="fill-workspace"
		class="btn btn-default btn-sm"
	>
		<?= Yii::t(
			'OrgmapModule.base',
			'Arbeitsfläche ausfüllen'
		) ?>
	</button>

</div>

<?= $form->field($model, 'pos_x')->textInput() ?>

<?= $form->field($model, 'pos_y')->textInput() ?>

<?= $form->field($model, 'radius')->textInput() ?>

<?= $form->field($model, 'width')->textInput() ?>

<?= $form->field($model, 'height')->textInput() ?>

<?= $form->field($model, 'sort_order')->textInput() ?>

<?= $form->field($model, 'font_size') ?>    
    <hr>

	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Verhalten'
		) ?>
	</h2>
    
    <?= $form->field($model, 'label_background')->checkbox() ?>
    
	<?= $form->field($model, 'show_label')->checkbox() ?>
	
	<?= $form->field($model, 'show_in_tree')->checkbox() ?>
    
	<?= $form->field($model, 'open_in_new_tab')->checkbox() ?>
	
	<?= $form->field($model, 'is_background')->checkbox() ?>
	
	<?= $form->field($model, 'visible')->checkbox() ?>

    <br>

	<p>

		<?= ActionButtonHelper::back(
			['/orgmap/admin/index'],
			[
				'style' => 'margin-right:10px;'
			]
		) ?>
	
		<?= Html::submitButton(
	
			Yii::t(
				'OrgmapModule.base',
				'Speichern'
			),
	
			[
				'class' => 'btn btn-success btn-sm'
			]
	
		) ?>
	
	</p>
	
    <?php ActiveForm::end(); ?>


<?php

$workspaceSize = Yii::$app
	->getModule('orgmap')
	->settings
	->get(
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

		$workspaceWidth = Yii::$app
			->getModule('orgmap')
			->settings
			->get('workspaceWidth', 2400);

		$workspaceHeight = Yii::$app
			->getModule('orgmap')
			->settings
			->get('workspaceHeight', 1350);
		break;

	default:

		$workspaceWidth = 2400;
		$workspaceHeight = 1350;
}

$centerX = round($workspaceWidth / 2);
$centerY = round($workspaceHeight / 2);

$this->registerJs(<<<JS

document
	.querySelectorAll('.orgmap-insert-tag')
	.forEach(button => {

		button.addEventListener(
			'click',
			function ()
			{

				insertContentTag(
					this.dataset.tag
				);

			}
		);

	});

function insertContentTag(tag)
{
	const textarea =
		document.getElementById(
			'node-content'
		);

	if (!textarea) {
		return;
	}

	const start =
		textarea.selectionStart;

	const end =
		textarea.selectionEnd;

	const text =
		textarea.value;

	textarea.value =
		text.substring(0, start)
		+ tag
		+ text.substring(end);

	textarea.focus();

	textarea.selectionStart =
	textarea.selectionEnd =
		start + tag.length;
}

function updateImageSourceFields()
{
	const imageSource =
		document.getElementById(
			'node-image_source'
		);

	if (!imageSource) {
		return;
	}

	const assetField =
		document.querySelector(
			'.orgmap-asset-field'
		);

	const customField =
		document.querySelector(
			'.orgmap-custom-image-field'
		);

	if (assetField) {

		assetField.style.display =
			imageSource.value === 'asset'
				? 'block'
				: 'none';
	}

	if (customField) {

		customField.style.display =
			imageSource.value === 'custom'
				? 'block'
				: 'none';
	}
}

var imageSourceSelect =
	document.getElementById(
		'node-image_source'
	);

if (imageSourceSelect) {

	imageSourceSelect.addEventListener(
		'change',
		updateImageSourceFields
	);

	updateImageSourceFields();
}

function updateAssetPreview()
{
	const assetSelect =
		document.getElementById(
			'node-asset_id'
		);

	const preview =
		document.querySelector(
			'.orgmap-asset-preview'
		);

	if (!assetSelect || !preview) {
		return;
	}

	const selectedOption =
		assetSelect.options[
			assetSelect.selectedIndex
		];

	const imageUrl =
		selectedOption.dataset.image;

	if (!imageUrl) {

		preview.innerHTML = '';

		return;
	}

	preview.replaceChildren();
	const image = document.createElement('img');
	image.src = imageUrl;
	image.alt = '';
	image.style.maxWidth = '250px';
	image.style.borderRadius = '8px';
	image.style.marginTop = '10px';
	preview.appendChild(image);
}

var assetSelect =
	document.getElementById(
		'node-asset_id'
	);

if (assetSelect) {

	assetSelect.addEventListener(
		'change',
		updateAssetPreview
	);

	updateAssetPreview();
}

var centerNodeButton =
	document.getElementById(
		'center-node'
	);

if (centerNodeButton) {

centerNodeButton.addEventListener(
	'click',
	function () {

		document.getElementById(
			'node-pos_x'
		).value = $centerX;

		document.getElementById(
			'node-pos_y'
		).value = $centerY;
	}
);
}

var fillWorkspaceButton =
	document.getElementById(
		'fill-workspace'
	);

if (fillWorkspaceButton) {

	fillWorkspaceButton.addEventListener(
		'click',
		function () {

			document.getElementById(
				'node-pos_x'
			).value =
				$centerX;

			document.getElementById(
				'node-pos_y'
			).value =
				$centerY;

			document.getElementById(
				'node-width'
			).value =
				$workspaceWidth;

			document.getElementById(
				'node-height'
			).value =
				$workspaceHeight;
		}
	);
}

function updateWorkspaceButtons()
{
	const background =
		document.getElementById(
			'node-is_background'
		);

	const button =
		document.getElementById(
			'fill-workspace'
		);

	if (!background || !button) {
		return;
	}

	button.disabled =
		!background.checked;
}

const backgroundCheckbox =
	document.getElementById(
		'node-is_background'
	);

if (backgroundCheckbox) {

	backgroundCheckbox.addEventListener(
		'change',
		updateWorkspaceButtons
	);

	updateWorkspaceButtons();
}

JS);
?>

</div>
