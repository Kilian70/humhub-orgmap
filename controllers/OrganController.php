<?php

namespace humhub\modules\orgmap\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\orgmap\models\Organ;
use humhub\modules\orgmap\models\Node;
use yii\filters\VerbFilter;

class OrganController extends Controller
{
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST']]],
		]);
	}

    public function actionIndex()
    {

        $organs = Organ::find()
            ->orderBy([
                'sort_order' => SORT_ASC,
                'name' => SORT_ASC,
            ])
            ->all();

        return $this->render('index', [
            'organs' => $organs,
        ]);
    }


    public function actionCreate()
    {

        $model = new Organ();

        if (
            $model->load(Yii::$app->request->post())
            && $model->save()
        ) {

            return $this->redirect(['index']);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }


    public function actionUpdate($id)
    {

        $model = Organ::findOne($id);

        if (!$model) {
            throw new \yii\web\NotFoundHttpException();
        }

        if (
            $model->load(Yii::$app->request->post())
            && $model->save()
        ) {

            return $this->redirect(['index']);
        }

        return $this->render('form', [
            'model' => $model,
        ]);
    }


    public function actionDelete($id)
    {

        $model = Organ::findOne($id);

		if ($model) {
			$transaction = Yii::$app->db->beginTransaction();
			try {
				Node::updateAll(['organ_id' => null], ['organ_id' => $model->id]);
				if ($model->delete() === false) {
					throw new \RuntimeException('Organ could not be deleted.');
				}
				$transaction->commit();
			} catch (\Throwable $e) {
				$transaction->rollBack();
				throw $e;
			}
		}

        return $this->redirect(['index']);
    }
}
