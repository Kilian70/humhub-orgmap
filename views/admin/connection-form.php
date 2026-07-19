<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

/** @var $model */

$this->title = Yii::t(
	'OrgmapModule.base',
	'Verbindung bearbeiten'
);

?>

<div class="container">

	<h1>
		<?= Html::encode($this->title) ?>
	</h1>
	
	<?php

	$fromNode =
		\humhub\modules\orgmap\models\Node::findOne(
			$model->from_node_id
		);
	
	$toNode =
		\humhub\modules\orgmap\models\Node::findOne(
			$model->to_node_id
		);
	
	?>
	
	<div class="alert alert-info">

		<strong>
			<?= Yii::t(
				'OrgmapModule.base',
				'Von'
			) ?>:
		</strong>
	
		<?= Html::encode(
			$fromNode->title ?? '-'
		) ?>
	
		&nbsp;&rarr;&nbsp;
	
		<strong>
			<?= Yii::t(
				'OrgmapModule.base',
				'Nach'
			) ?>:
		</strong>
	
		<?= Html::encode(
			$toNode->title ?? '-'
		) ?>
	
	</div>
	
	<p>
	
		<?= ActionButtonHelper::back(
			[
				'/orgmap/admin/update',
				'id' => $model->from_node_id
			]
		) ?>
	
	</p>

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->errorSummary($model, [
		'class' => 'alert alert-danger',
		'role' => 'alert',
	]) ?>

	<?php
	/*
	--------------------------------------------------
	Verbindung
	--------------------------------------------------
	*/
	?>
	
	<hr>

	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Verbindung'
		) ?>
	</h2>

	<?= $form->field($model, 'type')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Verbindungstyp'
			)
		)
		->dropDownList(
	
			$model::getTypeOptions()

	) ?>

	<?= $form->field($model, 'color')->input(
		'color',
		[
			'class' => 'form-control'
		]
	) ?>

	<?= $form->field($model, 'width')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Linienstärke'
			)
		)
		->textInput() ?>

	<?= $form->field($model, 'style')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Linienstil'
			)
		)
		->dropDownList([

			'solid' => Yii::t(
				'OrgmapModule.base',
				'Normal'
			),

			'dashed' => Yii::t(
				'OrgmapModule.base',
				'Gestrichelt'
			),

			'dotted' => Yii::t(
				'OrgmapModule.base',
				'Gepunktet'
			),

		]) ?>

	<?= $form->field($model, 'curve')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Bogen'
			)
		)
		->textInput([
			'type' => 'number'
		]) ?>
	
	<div class="help-block">
	
		<?= Yii::t(
			'OrgmapModule.base',
			'0 = Gerade, + = nach unten, - = nach oben'
		) ?>
	
	</div>
	
	<?= $form->field($model, 'arrow')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Pfeile'
			)
		)
		->dropDownList(

			$model::getArrowOptions()

		) ?>

	<?php
	/*
	--------------------------------------------------
	Beschriftung
	--------------------------------------------------
	*/
	?>
	
	<hr>

	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Beschriftung'
		) ?>
	</h2>

	<?= $form->field($model, 'label')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Beschriftung'
			)
		)
		->textInput() ?>

	<?= $form->field($model, 'font_size')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Schriftgrösse'
			)
		)
		->textInput([
			'type' => 'number'
		]) ?>
		
	<?= $form->field($model, 'font_weight')
	->label(
		Yii::t(
			'OrgmapModule.base',
			'Schriftstärke'
		)
	)
	->dropDownList(

		$model::getFontWeightOptions()

	) ?>

	<?= $form->field($model, 'label_rotation')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Textrotation'
			)
		)
		->dropDownList(

			$model::getLabelRotationOptions()

		) ?>

	<hr>
	
	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Position'
		) ?>
	</h2>

	<?= $form->field($model, 'label_offset_x')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Label horizontal'
			)
		)
		->textInput([
			'type' => 'number'
		]) ?>

	<?= $form->field($model, 'label_offset_y')
		->label(
			Yii::t(
				'OrgmapModule.base',
				'Label vertikal'
			)
		)
		->textInput([
			'type' => 'number'
		]) ?>

	<hr>

	<h2 class="h3">
		<?= Yii::t(
			'OrgmapModule.base',
			'Vorschau'
		) ?>
	</h2>
	
	<?php
	
	$leftArrow = '';
	
	$rightArrow = '';
	
	switch ($model->arrow) {
	
		case 'start':
	
			$leftArrow = '←';
	
			break;
	
		case 'end':
	
			$rightArrow = '→';
	
			break;
	
		case 'both':
	
			$leftArrow = '←';
	
			$rightArrow = '→';
	
			break;
	}
	
	$line = str_repeat('─', 12);
	
	if ($model->style === 'dashed') {
	
		$line = '- - - - - - -';
	
	} elseif ($model->style === 'dotted') {
	
		$line = '· · · · · · ·';
	}
	
	?>
	
		<div
			aria-hidden="true"
			style="
			padding:20px;
			text-align:center;
			border:1px solid #ddd;
			border-radius:8px;
			background:#fafafa;
			font-size:
				<?= (int)$model->font_size ?>px;
			font-weight:
				<?= Html::encode(
					$model->font_weight
				) ?>;
			color:
				<?= Html::encode(
					$model->color
				) ?>;
		"
	>
	
		<?= $leftArrow ?>
		<?= $line ?>
	
		<?= Html::encode(
			$model->label ?: '-'
		) ?>
	
		<?= $line ?>
		<?= $rightArrow ?>
	
	</div>
	
	<br><br>
	
	<?= Html::submitButton(

		Yii::t(
			'OrgmapModule.base',
			'Speichern'
		),

		[
			'class' => 'btn btn-success'
		]

	) ?>

	<?php ActiveForm::end(); ?>

</div>
