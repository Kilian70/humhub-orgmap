<?php

use yii\helpers\Html;
use humhub\modules\orgmap\helpers\ActionButtonHelper;

/** @var $organs */

$this->title = Yii::t('OrgmapModule.base', 'Organe');

?>

<div class="card">

    <div class="card-header">

        <strong>
            <?= Html::encode($this->title) ?>
        </strong>

    </div>

    <div class="card-body">
	    
	   <p>

			<?= ActionButtonHelper::back(
				['/orgmap/admin/index']
			) ?>
		
		</p>

        <p>

            <?= Html::a(

                Yii::t(
                    'OrgmapModule.base',
                    'Neues Organ'
                ),

                ['create'],

                [
                    'class' => 'btn btn-success btn-sm'
                ]
            ) ?>

        </p>

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>
                        <?= Yii::t('OrgmapModule.base', 'Name') ?>
                    </th>

                    <th>
                        <?= Yii::t('OrgmapModule.base', 'Parent') ?>
                    </th>

                    <th>
                        <?= Yii::t('OrgmapModule.base', 'Sort') ?>
                    </th>

                    <th>
                        <?= Yii::t('OrgmapModule.base', 'Aktionen') ?>
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($organs as $organ): ?>

                    <tr>

                        <td>
                            <?= $organ->id ?>
                        </td>

                        <td>

                            <?php if ($organ->parent_id): ?>
                                —
                            <?php endif; ?>

                            <?php if ($organ->color): ?>

							<span
								style="
									display:inline-block;
									width:12px;
									height:12px;
									border-radius:50%;
									background: <?= Html::encode($organ->color) ?>;
									margin-right:6px;
								"
							></span>
						
						<?php endif; ?>
						
						<?= Html::encode($organ->name) ?>

                        </td>

                        <td>

                            <?= $organ->parent_id
                                ? $organ->parent->name
                                : '-' ?>

                        </td>

                        <td>

                            <?= $organ->sort_order ?>

                        </td>

                        <td class="orgmap-action-buttons">

							<?= ActionButtonHelper::edit(
								[
									'update',
									'id' => $organ->id
								]
							) ?>
						
							<?= ActionButtonHelper::delete(
								[
									'delete',
									'id' => $organ->id
								],
								Yii::t(
									'OrgmapModule.base',
									'Wirklich löschen?'
								)
							) ?>
						
						</td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>
