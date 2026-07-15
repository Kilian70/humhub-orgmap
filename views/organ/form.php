<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\orgmap\models\Organ;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

/** @var $model */

$this->title = Yii::t('OrgmapModule.base', 'Organ');

?>

<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'parent_id')->dropDownList(

        Organ::find()
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column(),

        [
            'prompt' => '-'
        ]
    ) ?>

    <?= $form->field($model, 'sort_order') ?>
    
    <?= $form->field($model, 'color')->input(
		'color',
		[
			'class' => 'form-control'
		]
) ?>

<p>

	<?= ActionButtonHelper::back(
		['/orgmap/organ/index'],
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

</div>
