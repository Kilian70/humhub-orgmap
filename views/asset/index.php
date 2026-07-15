<?php

use yii\helpers\Html;
use humhub\modules\orgmap\models\Asset;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

/** @var $assets */

$this->title = Yii::t(
	'OrgmapModule.base',
	'ORG Assets'
);

?>

<div class="container">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>

	<?= Html::a(
	
		Yii::t(
			'OrgmapModule.base',
			'Neues Asset'
		),
	
		['/orgmap/asset/create'],
	
		[
			'class' => 'btn btn-primary btn-sm'
		]
	) ?>

	<?= ActionButtonHelper::back(
		['/orgmap/admin/index']
	) ?>

	</p>

	<table class="table table-bordered">

		<tr>

			<th>ID</th>

			<th>
				<?= Yii::t(
					'OrgmapModule.base',
					'Vorschau'
				) ?>
			</th>

			<th>
				<?= Yii::t(
					'OrgmapModule.base',
					'Titel'
				) ?>
			</th>

			<th>
				<?= Yii::t(
					'OrgmapModule.base',
					'Datei'
				) ?>
			</th>

			<th>
				<?= Yii::t(
					'OrgmapModule.base',
					'Typ'
				) ?>
			</th>
			
			<th>
				<?= Yii::t(
					'OrgmapModule.base',
					'Verwendet von'
				) ?>
			</th>
			
			<th>
				<?= Yii::t(
					'OrgmapModule.base',
					'Aktionen'
				) ?>
			</th>
		</tr>

		<?php foreach ($assets as $asset): ?>
		<?php

		$usedByNodes =
			\humhub\modules\orgmap\models\Node::find()
				->where([
					'asset_id' => $asset->id
				])
				->orderBy([
					'title' => SORT_ASC
				])
				->all();
		
		?>

			<tr>

				<td>
					<?= $asset->id ?>
				</td>

				<td>

					<?php if ($asset->filename): ?>

						<img
							src="<?= Yii::getAlias('@web') ?>/uploads/orgmap/assets/<?= Html::encode($asset->filename) ?>"
							style="
								width: 120px;
								height: auto;
								border-radius: 6px;
							"
						>

					<?php endif; ?>

				</td>

				<td>
					<?= Html::encode($asset->title) ?>
				</td>

				<td>
				
					<div style="overflow-wrap: anywhere;">

						<strong>
							<?= Html::encode($asset->filename) ?>
						</strong>
					
					</div>
				
					<div class="text-muted">
				
						<?= $asset->getImageDimensions() ?>
				
					</div>
				
					<div class="text-muted">
				
						<?= $asset->getFileSize() ?>
				
					</div>
				
				</td>
				
				<td>
					<?= Html::encode(

						Asset::getTypeOptions()[
							$asset->type
						] ?? $asset->type
					
					) ?>
				</td>
				
				<td>
				
			<?php if ($usedByNodes): ?>
			
				<?php foreach ($usedByNodes as $node): ?>
			
					<div>
			
						<?= Html::encode(
							$node->title
						) ?>
			
					</div>
			
				<?php endforeach; ?>
			
			<?php else: ?>
			
				<span class="text-muted">
			
					<?= Yii::t(
						'OrgmapModule.base',
						'Nicht verwendet'
					) ?>
			
				</span>
			
			<?php endif; ?>
				
				</td>
				
				<td class="orgmap-action-buttons">

				
					<?= ActionButtonHelper::delete(
					
						[
							'/orgmap/asset/delete',
							'id' => $asset->id
						],
					
						Yii::t(
							'OrgmapModule.base',
							'Asset wirklich löschen?'
						)
					
					) ?>
				
				</td>

			</tr>

		<?php endforeach; ?>

	</table>

</div>
