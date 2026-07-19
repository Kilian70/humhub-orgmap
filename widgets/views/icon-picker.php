<?php

use yii\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\orgmap\helpers\IconPickerHelper;

/** @var $model */
/** @var $attribute */

?>

<div
	class="orgmap-icon-picker"
	role="group"
	aria-labelledby="orgmap-icon-picker-label"
>

		<span class="control-label" id="orgmap-icon-picker-label">
		<?= Yii::t(
			'OrgmapModule.base',
			'Icon'
		) ?>
		</span>

		<div class="orgmap-selected-icon" role="status" aria-live="polite" aria-atomic="true">

		<?php if (!empty($model->$attribute)): ?>
	
			<i class="fa <?= Html::encode(
				$model->$attribute
			) ?>"></i>
	
		<?php endif; ?>
	
	</div>

	<p class="orgmap-icon-actions">

			<button
				type="button"
				class="btn btn-secondary orgmap-icon-open"
				aria-expanded="false"
				aria-controls="orgmap-icon-panel">

			<?= Icon::get('pencil') ?>

			<?= Yii::t(
				'OrgmapModule.base',
				'Icon auswählen'
			) ?>

		</button>

	</p>

		<div
			id="orgmap-icon-panel"
			class="orgmap-icon-panel"
		hidden>
	
		<button
			type="button"
			class="orgmap-icon-option"
			data-icon=""
				title="<?= Yii::t(
				'OrgmapModule.base',
				'Kein Icon'
				) ?>"
				aria-label="<?= Yii::t('OrgmapModule.base', 'Kein Icon') ?>"
				aria-pressed="false">
	
		</button>
	
		<?php foreach (
			IconPickerHelper::getAllIcons()
			as $icon
		): ?>

			<button
				type="button"
				class="orgmap-icon-option"
				data-icon="<?= Html::encode($icon) ?>"
					title="<?= Html::encode($icon) ?>"
					aria-label="<?= Html::encode($icon) ?>"
					aria-pressed="false">

					<i class="fa <?= Html::encode($icon) ?>" aria-hidden="true"></i>

			</button>

		<?php endforeach; ?>

	</div>

	<?= Html::activeHiddenInput(
		$model,
		$attribute
	) ?>

</div>
