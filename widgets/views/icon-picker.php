<?php

use yii\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\orgmap\helpers\IconPickerHelper;

/** @var $model */
/** @var $attribute */

?>

<div class="orgmap-icon-picker">

	<label class="control-label">
		<?= Yii::t(
			'OrgmapModule.base',
			'Icon'
		) ?>
	</label>

	<div class="orgmap-selected-icon">

		<?php if (!empty($model->$attribute)): ?>
	
			<i class="fa <?= Html::encode(
				$model->$attribute
			) ?>"></i>
	
		<?php endif; ?>
	
	</div>

	<p class="orgmap-icon-actions">

		<button
			type="button"
			class="btn btn-secondary orgmap-icon-open">

			<?= Icon::get('pencil') ?>

			<?= Yii::t(
				'OrgmapModule.base',
				'Icon auswählen'
			) ?>

		</button>

	</p>

	<div
		class="orgmap-icon-panel"
		hidden>
	
		<button
			type="button"
			class="orgmap-icon-option"
			data-icon=""
			title="<?= Yii::t(
				'OrgmapModule.base',
				'Kein Icon'
			) ?>">
	
		</button>
	
		<?php foreach (
			IconPickerHelper::getAllIcons()
			as $icon
		): ?>

			<button
				type="button"
				class="orgmap-icon-option"
				data-icon="<?= Html::encode($icon) ?>"
				title="<?= Html::encode($icon) ?>">

				<i class="fa <?= Html::encode($icon) ?>"></i>

			</button>

		<?php endforeach; ?>

	</div>

	<?= Html::activeHiddenInput(
		$model,
		$attribute
	) ?>

</div>