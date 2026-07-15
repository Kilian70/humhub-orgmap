<?php

namespace humhub\modules\orgmap\controllers;

use Yii;
use yii\web\UploadedFile;
use humhub\components\Controller;
use humhub\modules\orgmap\models\Asset;
use humhub\modules\orgmap\helpers\BackgroundImageHelper;
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
			->orderBy([
				'id' => SORT_DESC
			])
			->all();

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
		
				$filename =
					uniqid()
					. '.'
					. $model->imageFile->extension;
		
			$model->filename =
				$filename;
			
			$uploadPath =
				Yii::getAlias(
					'@webroot/uploads/orgmap/assets/'
				);
			
			if (!is_dir($uploadPath)) {
			
				$created = mkdir(
					$uploadPath,
					0775,
					true
				);
				if (!$created && !is_dir($uploadPath)) {
					$model->addError('imageFile', Yii::t('OrgmapModule.base', 'Upload-Verzeichnis konnte nicht erstellt werden.'));
					return $this->render('create', ['model' => $model]);
				}
			}
			
				$filePath = $uploadPath . $filename;
				if (!$model->imageFile->saveAs($filePath)) {
					$model->addError('imageFile', Yii::t('OrgmapModule.base', 'Datei konnte nicht gespeichert werden.'));
					return $this->render('create', ['model' => $model]);
				}
			
			if ($model->type === 'background') {

				BackgroundImageHelper::optimize(
						$filePath
				);
			}
	
			if ($model->save(false)) {
	
				return $this->redirect([
					'/orgmap/asset/index'
				]);
			}

			if (is_file($filePath) && !unlink($filePath)) {
				Yii::warning('Could not remove orphaned OrgMap asset: ' . $filePath, __METHOD__);
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
	
		$filename = $model->filename;
		if ($model->delete() === false) {
			throw new \RuntimeException('Asset could not be deleted.');
		}

		if ($filename) {
	
			$uploadPath = Yii::getAlias(
						'@webroot/uploads/orgmap/assets/'
					);
			$filePath = $uploadPath . basename($filename);
			$realUploadPath = realpath($uploadPath);
			$realFilePath = realpath($filePath);
		
				if ($realUploadPath && $realFilePath && str_starts_with($realFilePath, $realUploadPath . DIRECTORY_SEPARATOR)) {
		
					if (!unlink($realFilePath)) {
						Yii::warning('Could not remove OrgMap asset file: ' . $realFilePath, __METHOD__);
						Yii::$app->session->setFlash('warning', Yii::t('OrgmapModule.base', 'Der Datenbankeintrag wurde gelöscht, die Datei konnte jedoch nicht entfernt werden.'));
					}
			}
		}
	
		return $this->redirect([
			'/orgmap/asset/index'
		]);
	}
	
}
