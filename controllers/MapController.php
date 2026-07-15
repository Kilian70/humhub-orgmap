<?php

namespace humhub\modules\orgmap\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use yii\web\ForbiddenHttpException;
use humhub\modules\orgmap\models\Connection;
use humhub\modules\orgmap\models\Organ;
use yii\filters\VerbFilter;


class MapController extends Controller
{
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'verbs' => ['class' => VerbFilter::class, 'actions' => ['save-position' => ['POST']]],
		]);
	}

	private function ensureViewPermission(): void
	{
		if (Yii::$app->user->isGuest) {
			if (!Yii::$app->getModule('orgmap')->settings->get('allowGuestAccess', false)) {
				throw new ForbiddenHttpException();
			}
		} elseif (!Yii::$app->user->can(ViewOrgMap::class)) {
			throw new ForbiddenHttpException();
		}
	}

	public function actionIndex()
	{
	
	if (
		Yii::$app->user->isGuest
	) {
	
		$allowGuestAccess =
			Yii::$app
				->getModule('orgmap')
				->settings
				->get(
					'allowGuestAccess',
					false
				);
	
	if (!$allowGuestAccess) {
	
		return $this->redirect(
			Yii::$app->user->loginUrl
		);
	}
	
	} elseif (
		!Yii::$app->user->can(ViewOrgMap::class)
	) {
	
		throw new ForbiddenHttpException();
	}
	
		$nodes = Node::find()
			->with(['organ', 'space', 'asset'])
			->where(['visible' => 1])
		
			->orderBy([
				'sort_order' => SORT_ASC,
				'id' => SORT_ASC,
			])
		
			->all();
		
		$organCounters = [];
		
		foreach ($nodes as $node) {
		
			if (
				$node->organ
				&& !$node->sort_order
			) {
			
				$organId =
					$node->organ->id;
			
				if (!isset($organCounters[$organId])) {
			
					$organCounters[$organId] = 1;
				}
			
				$node->sort_order =
					$node->organ->sort_order
					+ $organCounters[$organId];
			
				$organCounters[$organId]++;
			}
		}
	
		$connections = Connection::find()->all();
		$organs = Organ::find()->orderBy(['sort_order' => SORT_ASC])->all();
		$treeNodes = array_values(array_filter($nodes, static function (Node $node) {
			return (bool) $node->show_in_tree;
		}));
		
		return $this->render('index', [
		
			'nodes' => $nodes,
		
			'connections' => $connections,
			'organs' => $organs,
			'treeNodes' => $treeNodes,
		]);
	}
    
	public function actionSavePosition()
	{
	
		if (!Yii::$app->user->can(ManageOrgMap::class)) {
	
			throw new ForbiddenHttpException();
		}
	
		Yii::$app->response->format =
				\yii\web\Response::FORMAT_JSON;
	
			$data = json_decode(
			Yii::$app->request->rawBody,
			true
		);
	
			if (!is_array($data) || !isset($data['id'])) {
				throw new \yii\web\BadRequestHttpException();
			}

			$model = Node::findOne(
				(int) $data['id']
		);
	
		if (!$model) {
			throw new \yii\web\NotFoundHttpException();
		}
	
		if (isset($data['x'])) {

				$model->pos_x = max(-10000, min(20000, (int) $data['x']));
		}
		
		if (isset($data['y'])) {
		
				$model->pos_y = max(-10000, min(20000, (int) $data['y']));
		}
		
		if (isset($data['radius'])) {

				$model->radius = max(20, min(5000, (int) $data['radius']));
		}
		
		if (isset($data['width'])) {

				$model->width = max(20, min(5000, (int) $data['width']));
		}
		
		if (isset($data['height'])) {
		
				$model->height = max(20, min(5000, (int) $data['height']));
		}
		
		if (isset($data['label_x'])) {
		
				$model->label_x = max(-10000, min(10000, (int) $data['label_x']));
		}
		
		if (isset($data['label_y'])) {
		
				$model->label_y = max(-10000, min(10000, (int) $data['label_y']));
		}
			
		$saved = $model->save(false);
	
		return [
			'success' => $saved,
			'x' => $model->pos_x,
			'y' => $model->pos_y
		];
	}
	
	public function actionTree()
		{
			$this->ensureViewPermission();
			return $this->render('tree', [
				'organs' => Organ::find()->orderBy(['sort_order' => SORT_ASC])->all(),
				'treeNodes' => Node::find()
					->with(['organ', 'space'])
					->where(['visible' => 1, 'show_in_tree' => 1])
					->orderBy(['sort_order' => SORT_ASC])
					->all(),
			]);
		}
}
