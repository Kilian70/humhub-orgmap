<?php

namespace humhub\modules\orgmap\controllers;

use Yii;
use yii\web\UploadedFile;
use humhub\components\Controller;
use humhub\modules\orgmap\models\Asset;
use humhub\modules\orgmap\services\AssetStorage;
use humhub\modules\orgmap\permissions\ManageOrgMap;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

class AssetController extends Controller
{
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => ['create' => ['GET', 'POST'], 'delete' => ['POST']],
			],
		]);
	}

	public function beforeAction($action)
	{
		if (!Yii::$app->user->can(ManageOrgMap::class)) {
			throw new ForbiddenHttpException();
		}

		return parent::beforeAction($action);
	}

	public function actionIndex()
	{

		$assets = Asset::find()
			->with('nodes')
            ->where(['deleted_at' => null])
			->orderBy([
				'id' => SORT_DESC
			])
			->all();

        $assets = array_values(array_filter($assets, static fn(Asset $asset) => $asset->canView()));

		return $this->render('index', [

			'assets' => $assets,
		]);
	}

	public function actionCreate()
	{
	
		$model = new Asset();
	
		if (
			$model->load(
				Yii::$app->request->post()
			)
		) {
		
			$model->imageFile =
				UploadedFile::getInstance(
					$model,
					'imageFile'
				);
		
			if (!$model->imageFile || !$model->validate()) {
				return $this->render('create', ['model' => $model]);
			}
		
            $model->filename = bin2hex(random_bytes(16)) . '.' . strtolower($model->imageFile->extension);
            $content = file_get_contents($model->imageFile->tempName);
            if ($content === false) {
                $model->addError('imageFile', Yii::t('OrgmapModule.base', 'Datei konnte nicht gespeichert werden.'));
            } else {
                // The record is the ACL/lifecycle owner, so publish its File reference last.
                try {
                    if (!$model->save(false)) {
                        throw new \RuntimeException('OrgMap asset could not be saved.');
                    }
                    AssetStorage::saveUpload($model, $content);
                    return $this->redirect(['/orgmap/asset/index']);
                } catch (\Throwable $exception) {
                    Yii::error($exception, __METHOD__);
                    if (!$model->isNewRecord) {
                        $model->updateAttributes(['deleted_at' => time()]);
                    }
                    $model->addError('imageFile', Yii::t('OrgmapModule.base', 'Datei konnte nicht gespeichert werden.'));
                }
            }

		}
	
		return $this->render('create', [
	
			'model' => $model,
		]);
	}
	
	public function actionDelete($id)
	{
	
		$model = Asset::findOne($id);
	
		if (!$model) {
	
			throw new \yii\web\NotFoundHttpException();
		}

        if (!$model->canView()) {
            throw new ForbiddenHttpException();
        }
		
				$usedByNodes =
			\humhub\modules\orgmap\models\Node::find()
				->where([
					'asset_id' => $model->id
				])
				->count();
		
		if ($usedByNodes > 0) {
		
			Yii::$app->session->setFlash(
		
				'error',
		
				Yii::t(
					'OrgmapModule.base',
					'Asset wird noch verwendet und kann nicht gelöscht werden.'
				)
			);
		
			return $this->redirect([
				'/orgmap/asset/index'
			]);
		}
	
        // Remove from the active library without losing a recoverable record or original.
        if ($model->updateAttributes(['deleted_at' => time()]) === false) {
            throw new \RuntimeException('Asset could not be archived.');
        }
        return $this->redirect(['/orgmap/asset/index']);
    }
}
