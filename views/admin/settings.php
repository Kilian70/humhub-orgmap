<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

/*
----------------------------------------------------
Titel
----------------------------------------------------
*/

$this->title =
	$model->moduleTitle
	. ' '
	. Yii::t(
		'OrgmapModule.base',
		'Einstellungen'
	);

?>

<div class="container">

	    <h1><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->errorSummary($model, [
		'class' => 'alert alert-danger',
		'role' => 'alert',
	]) ?>

<div class="form-group">

	<?= Html::label(
		Yii::t(
			'OrgmapModule.base',
			'Grösse der Arbeitsfläche'
		),
		'workspace_size'
	) ?>

	<?= Html::dropDownList(

		'workspace_size',

		$workspaceSize,

		[
			'small' => Yii::t(
				'OrgmapModule.base',
				'Klein (1600 × 900)'
			),

			'medium' => Yii::t(
				'OrgmapModule.base',
				'Mittel (2400 × 1350)'
			),

			'large' => Yii::t(
				'OrgmapModule.base',
				'Gross (3200 × 1800)'
			),

			'custom' => Yii::t(
				'OrgmapModule.base',
				'Eigene Grösse'
			),
		],

		[
			'class' => 'form-control',
			'id' => 'workspace-size'
		]
	) ?>

</div>

	<div id="custom-workspace-fields">

	<?= $form->field($model, 'workspaceWidth')
		->textInput([
			'type' => 'number',
			'min' => 500
		]) ?>

	<?= $form->field($model, 'workspaceHeight')
		->textInput([
			'type' => 'number',
			'min' => 500
		]) ?>

	</div>

	<?= $form->field($model, 'showLines')->checkbox() ?>
	    
		<?= $form->field($model, 'allowGuestAccess')
		->checkbox() ?>
	
	<?= $form->field($model, 'topMenuSortOrder')
		->textInput([
			'type' => 'number'
		]) ?>
	
	<?= $form->field($model, 'moduleTitle')
		->textInput() ?>
		
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

    <?php ActiveForm::end(); ?>

<?php

$this->registerJs(<<<JS

function toggleWorkspaceFields() {

	const select =
		document.getElementById('workspace-size');

	const fields =
		document.getElementById('custom-workspace-fields');

	if (!select || !fields) {
		return;
	}

	fields.style.display =
		select.value === 'custom'
		? 'block'
		: 'none';
}

toggleWorkspaceFields();

const select =
	document.getElementById('workspace-size');

if (select) {

	select.addEventListener(
		'change',
		toggleWorkspaceFields
	);
}

JS);

?>

</div>
