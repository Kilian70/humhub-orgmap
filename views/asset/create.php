<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

/** @var $model */

$this->title = Yii::t(
	'OrgmapModule.base',
	'Asset erstellen'
);

?>

<div class="container">

	<h1><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin([

		'options' => [
			'enctype' => 'multipart/form-data'
		]

		]); ?>

		<?= $form->errorSummary($model, [
			'class' => 'alert alert-danger',
			'role' => 'alert',
		]) ?>

	<?= $form->field($model, 'title')
		->textInput() ?>

	<?= $form->field($model, 'imageFile')
		->fileInput() ?>

	<?= $form->field($model, 'type')->dropDownList($model::getTypeOptions()) ?>

	<p>

		<?= ActionButtonHelper::back(
			['/orgmap/asset/index'],
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
				'class' => 'btn btn-primary btn-sm'
			]
		) ?>
	
	</p>

	<?php ActiveForm::end(); ?>

</div>
