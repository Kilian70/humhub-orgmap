<?php

use yii\helpers\Html;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\models\Organ;
use humhub\modules\orgmap\assets\OrgMapAdminAsset;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

OrgMapAdminAsset::register($this);

/** @var $spaces */
/** @var $nodes */
/** @var $organs */

$this->title = Yii::t('OrgmapModule.base', 'ORG. Verwaltung');


/*
------------------------------------------------------------
Linien anzeigen
------------------------------------------------------------
*/

$showLines = Yii::$app
    ->getModule('orgmap')
    ->settings
    ->get('showLines', true);


/*
------------------------------------------------------------
Nodes nach Space ID
------------------------------------------------------------
*/

$nodesBySpace = [];

foreach ($nodes as $node) {

    $nodesBySpace[$node->space_id] = $node;
}


/*
------------------------------------------------------------
Spaces nach Organ gruppieren
------------------------------------------------------------
*/

$groupedSpaces = [];

foreach ($spaces as $space) {

    $node = $nodesBySpace[$space->id] ?? null;

    $organId = $node->organ_id ?? 0;

    $groupedSpaces[$organId][] = $space;
}


/*
------------------------------------------------------------
Organe sortieren
------------------------------------------------------------
*/

$organSort = [];

foreach ($organs as $organ) {

    $organSort[$organ->id] = $organ->sort_order;
}

$organSort[0] = 9999;


uksort($groupedSpaces, function ($a, $b) use ($organSort) {

    $orderA = $organSort[$a] ?? 9999;
    $orderB = $organSort[$b] ?? 9999;

    return $orderA <=> $orderB;
});


/*
------------------------------------------------------------
Organ Namen
------------------------------------------------------------
*/

$organNames = [];

foreach ($organs as $organ) {

    $organNames[$organ->id] = $organ->name;
}

$organNames[0] = Yii::t(
    'OrgmapModule.base',
    '– kein Organ –'
);

?>

<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

<p>

	<?= Html::a(

		Yii::t(
			'OrgmapModule.base',
			'Organe verwalten'
		),

		['/orgmap/organ/index'],

		[
			'class' => 'btn btn-primary btn-sm'
		]

	) ?>

	<?= Html::a(

		Yii::t(
			'OrgmapModule.base',
			'Externen Eintrag erstellen'
		),

		['/orgmap/admin/create'],

		[
			'class' => 'btn btn-success btn-sm'
		]

	) ?>

	<?= ActionButtonHelper::assets(
		['/orgmap/asset/index']
	) ?>

	<?= ActionButtonHelper::settings(
		['/orgmap/admin/settings']
	) ?>

</p>

<?= Html::beginForm(['toggle-lines'], 'post') ?>

<div class="checkbox">

    <label>

        <?= Html::checkbox(
            'showLines',
            $showLines,
            ['value' => 1]
        ) ?>

        <?= Yii::t(
            'OrgmapModule.base',
            'Linien anzeigen'
        ) ?>

    </label>

</div>

<?= Html::submitButton(

    Yii::t(
        'OrgmapModule.base',
        'Speichern'
    ),

    [
        'class' => 'btn btn-secondary btn-sm'
    ]
) ?>

<?= Html::endForm() ?>


	<div class="table-responsive" tabindex="0" role="region" aria-label="<?= Html::encode($this->title) ?>">
	<table class="table table-hover table-striped">
		<caption class="sr-only"><?= Html::encode($this->title) ?></caption>

        <thead>

            <tr>

				<th scope="col" width="30%">

                    <?= Yii::t('OrgmapModule.base', 'Name') ?>

                </th>

				<th scope="col" width="25%">

                    <?= Yii::t('OrgmapModule.base', 'Bereich') ?>

                </th>

				<th scope="col" width="15%">

                    <?= Yii::t('OrgmapModule.base', 'Elementtyp') ?>

                </th>

				<th scope="col" width="10%">

                    <?= Yii::t('OrgmapModule.base', 'Extern') ?>

                </th>
                
				<th scope="col" width="10%">

					<?= Yii::t('OrgmapModule.base', 'Sichtbar') ?>
				
				</th>

				<th scope="col" width="20%">

                    <?= Yii::t('OrgmapModule.base', 'Aktionen') ?>

                </th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($groupedSpaces as $organId => $spaceList): ?>

                <tr class="table-secondary">

                    <td colspan="6">

                        <strong>

                            <?= Html::encode(
                                $organNames[$organId]
                                ?? 'Unbekannt'
                            ) ?>

                        </strong>

                    </td>

                </tr>

                <?php foreach ($spaceList as $space): ?>

                    <?php

                    $node = $nodesBySpace[$space->id] ?? null;

                    ?>

                    <tr>

						<td>
						
							<?php if ($node && $node->color): ?>
						
								<span
									style="
										display:inline-block;
										width:12px;
										height:12px;
										border-radius:50%;
										background: <?= Html::encode($node->color) ?>;
										margin-right:6px;
									"
								></span>
						
							<?php endif; ?>
						
							<?= Html::encode($space->name) ?>
						
						</td>

                        <td>

                            <?= Html::encode(
                                $organNames[$organId]
                                ?? '-'
                            ) ?>

                        </td>

						<td>

						<?php
						
						$elementType = 'Element';
						
						if ($node) {
						
							if ($node->is_background) {
						
								$elementType = 'Hintergrund';
						
							} elseif ($node->shape === 'legend') {
						
								$elementType = 'Legende';
						
							} elseif ($node->shape === 'panel') {
						
								$elementType = 'Panel';
						
							} elseif ($node->link_type === 'external') {
						
								$elementType = 'Externer Link';
						
							} elseif ($node->space_id) {
						
								$elementType = 'Space';
							}
						}
						
						echo Html::encode($elementType);
						
						?>
						
						</td>	
											
                        <td>

                            <?= $node && $node->is_external
                                ? Yii::t('OrgmapModule.base', 'JA')
                                : '-' ?>

                        </td>
                        
						<td>
						
							<?php if ($node): ?>
						
								<?= ActionButtonHelper::visibility(

									[
										'toggle-visible',
										'id' => $node->id
									],
								
									$node->visible
								
								) ?>
						
							<?php else: ?>
						
								-
						
							<?php endif; ?>
						
						</td>

                        <td class="orgmap-action-buttons">

							<?php if ($node): ?>
						
								<?= ActionButtonHelper::edit(
									[
										'update',
										'id' => $node->id
									]
								) ?>
						
								<?= ActionButtonHelper::center(
									[
										'reset-label',
										'id' => $node->id
									]
								) ?>
						
								<?= ActionButtonHelper::delete(
									[
										'delete',
										'id' => $node->id
									],
									Yii::t(
										'OrgmapModule.base',
										'Wirklich entfernen?'
									)
								) ?>
						
							<?php else: ?>
						
								<?= Html::a(
						
									Yii::t(
										'OrgmapModule.base',
										'Zu ORG. hinzufügen'
									),
						
									[
										'create',
										'space_id' => $space->id
									],
						
									[
										'class' => 'btn btn-success btn-sm'
									]
								) ?>
						
							<?php endif; ?>
						
						</td>
											</tr>

                <?php endforeach; ?>

            <?php endforeach; ?>

       <?php

$externalNodes = Node::find()
    ->where(['is_external' => 1])
    ->andWhere(['space_id' => null])
    ->all();

?>

<?php if (!empty($externalNodes)): ?>

    <tr class="table-secondary">

        <td colspan="6">

            <strong>

                <?= Yii::t(
                    'OrgmapModule.base',
                    'Externe Einträge'
                ) ?>

            </strong>

        </td>

    </tr>

    <?php foreach ($externalNodes as $node): ?>

        <tr>

            <td>
                <?= Html::encode($node->title) ?>
            </td>

            <td>
                <?= Html::encode(
                    $organNames[$node->organ_id] ?? '-'
                ) ?>
            </td>
            
		 <td>
		
		<?php
		
		$elementType = 'Element';
		
		if ($node) {
		
			if ($node->is_background) {
		
				$elementType = 'Hintergrund';
		
			} elseif ($node->shape === 'legend') {
		
				$elementType = 'Legende';
		
			} elseif ($node->shape === 'panel') {
		
				$elementType = 'Panel';
		
			} elseif ($node->link_type === 'external') {
		
				$elementType = 'Externer Link';
		
			} elseif ($node->space_id) {
		
				$elementType = 'Space';
			}
		}
		
		echo Html::encode($elementType);
		
		?>
		
		</td>            
            <td>

			<?= Yii::t(
				'OrgmapModule.base',
				'JA'
			) ?>
		
		</td>
		
		<td>
		
			<?= ActionButtonHelper::visibility(
		
				[
					'toggle-visible',
					'id' => $node->id
				],
		
				$node->visible
		
			) ?>
		
		</td>

           <td class="orgmap-action-buttons">

				<?= ActionButtonHelper::edit(
			
					[
						'update',
						'id' => $node->id
					]
			
				) ?>
			
				<?= ActionButtonHelper::delete(
			
					[
						'delete',
						'id' => $node->id
					],
			
					Yii::t(
						'OrgmapModule.base',
						'Wirklich entfernen?'
					)
			
				) ?>
			
			</td>
        </tr>

    <?php endforeach; ?>

<?php endif; ?>
       
       
       </tbody>

	</table>
	</div>

</div>
