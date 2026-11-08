<?php

use humhub\modules\ui\form\widgets\IconPicker;

/** @var $model */
/** @var $attribute */

?>

<div class="orgmap-icon-picker">
	<label class="control-label" for="node-icon_class">
		<?= Yii::t('OrgmapModule.base', 'Icon') ?>
	</label>

	<?= IconPicker::widget([
		'model' => $model,
		'attribute' => $attribute,
		'options' => [
			'id' => 'node-icon_class',
			'aria-label' => Yii::t('OrgmapModule.base', 'Icon'),
		],
	]) ?>
</div>
