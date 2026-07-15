<?php

namespace humhub\modules\orgmap\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\models\Connection;
use humhub\modules\orgmap\models\SettingsForm;
use humhub\modules\orgmap\models\Organ;
use humhub\modules\space\models\Space;
use yii\filters\VerbFilter;

class AdminController extends Controller
{
	public function behaviors()
	{
		return array_merge(parent::behaviors(), [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'delete' => ['POST'], 'toggle-visible' => ['POST'], 'toggle-lines' => ['POST'],
					'reset-label' => ['POST'], 'save-connection-label' => ['POST'],
				],
			],
		]);
	}

    public function actionIndex()
    {

        $model = new SettingsForm();


	/*
	----------------------------------------------------
	Speichern
	----------------------------------------------------
	*/
	
	if (Yii::$app->request->isPost) {
	
		$model->load(Yii::$app->request->post());
	
			if (!$model->save()) {
				return $this->render('index', ['model' => $model, 'spaces' => Space::find()->all(), 'nodes' => Node::find()->all(), 'organs' => Organ::find()->all()]);
			}
	
		Yii::$app->session->setFlash(
			'success',
			Yii::t(
				'OrgmapModule.base',
				'Gespeichert'
			)
		);
	
		return $this->refresh();
	}

	/*
	----------------------------------------------------
	Daten laden
	----------------------------------------------------
	*/
	
	/*
	----------------------------------------------------
	Spaces
	----------------------------------------------------
	*/
	
	$spaces = Space::find()
		->orderBy([
			'name' => SORT_ASC
		])
		->all();
	
	/*
	----------------------------------------------------
	Nodes
	----------------------------------------------------
	*/
	
	$nodes = Node::find()
		->orderBy([
			'sort_order' => SORT_ASC,
			'title' => SORT_ASC,
		])
		->all();


	/*
	----------------------------------------------------
	Hauptorgane
	----------------------------------------------------
	*/
	
	$groups = Organ::find()
		->orderBy([
			'sort_order' => SORT_ASC,
			'name' => SORT_ASC,
		])
		->all();
	
	/*
	----------------------------------------------------
	View
	----------------------------------------------------
	*/
	
	return $this->render('index', [
	
		'model' => $model,
	
		'spaces' => $spaces,
	
		'nodes' => $nodes,
	
		'organs' => $groups,
	]);
	
	}
	
	public function actionCreate($space_id = null)
	{
	
		$model = new Node();


	/*
	----------------------------------------------------
	Space automatisch übernehmen
	----------------------------------------------------
	*/
	
	if ($space_id) {
	
		$space = Space::findOne($space_id);
	
		if ($space) {
	
			$model->space_id = $space->id;
	
			$model->title = $space->name;
	
			$model->color = '#6ec6ff';
	
			$model->radius = 80;
	
			$model->visible = 1;
		}
	}
	
	if (
		$model->organ_id
		&& empty($model->color)
	) {
	
		$organ = Organ::findOne(
			$model->organ_id
		);
	
		if (
			$organ
			&& !empty($organ->color)
		) {
	
			$model->color =
				$organ->color;
		}
	}
	
	/*
	----------------------------------------------------
	Externe
	----------------------------------------------------
	*/
	
	if (!$space_id) {
	
		$model->is_external = 1;
	
		$model->visible = 1;
	
		$model->display_mode = 'color';
	
		$model->image_source = 'custom';
	
		$model->label_background = 1;
	
		$model->show_label = 1;
	
		$model->radius = 80;
	
		$model->sort_order = 9000;
	
		$model->color = '#6ec6ff';
	}
	
	/*
	----------------------------------------------------
	Speichern
	----------------------------------------------------
	*/
	
	if ($model->load(Yii::$app->request->post())) {
	
		if (
			empty($model->color)
			&& $model->organ_id
		) {
	
			$organ = Organ::findOne(
				$model->organ_id
			);
	
			if (
				$organ
				&& !empty($organ->color)
			) {
	
				$model->color =
					$organ->color;
			}
		}
	
		if ($model->save()) {
		
			Connection::deleteAll([
				'from_node_id' => $model->id
			]);
		
			if (!empty($model->connectionIds)) {
		
					foreach ($model->connectionIds as $targetId) {
						$targetId = (int) $targetId;
						if ($targetId === (int) $model->id || !Node::find()->where(['id' => $targetId])->exists()) {
							continue;
						}
		
					$connection = new Connection();

					$connection->color = '#666666';
					
					$connection->width = 2;
					
					$connection->style = 'solid';
					
					$connection->from_node_id =
						$model->id;
		
					$connection->to_node_id =
						$targetId;
		
						$connection->save();
				}
			}
		
			return $this->redirect(['index']);
		}
	}
	
	/*
	----------------------------------------------------
	Formular
	----------------------------------------------------
	*/
	
	return $this->render('form', [
		'model' => $model,
	]);
	
	}
	
	public function actionUpdate($id)
	{
	
			$model = Node::findOne($id);

			if (!$model) {
				throw new \yii\web\NotFoundHttpException();
			}
			
			$connections = Connection::find()
			->where([
				'or',
				['from_node_id' => $model->id],
				['to_node_id' => $model->id],
			])
			->all();
		
		$model->connectionIds = [];
		
		foreach ($connections as $connection) {
		
			if ($connection->from_node_id == $model->id) {
		
				$model->connectionIds[] =
					$connection->to_node_id;
			}
		
			if ($connection->to_node_id == $model->id) {
		
				$model->connectionIds[] =
					$connection->from_node_id;
			}
		}
	
	
		/*
		----------------------------------------------------
		Speichern
		----------------------------------------------------
		*/
	
		if (
			$model->load(Yii::$app->request->post())
			&& $model->save()
		) {
		

		$currentConnectionIds = [];
		
		foreach ($connections as $connection) {
		
			if ($connection->from_node_id == $model->id) {
		
				$currentConnectionIds[] =
					$connection->to_node_id;
			}
		
			if ($connection->to_node_id == $model->id) {
		
				$currentConnectionIds[] =
					$connection->from_node_id;
			}
		}
		
		if (!is_array($model->connectionIds)) {
		
			$model->connectionIds = [];
		}
		
		$connectionsToAdd = array_diff(
			$model->connectionIds,
			$currentConnectionIds
		);
		
		$connectionsToRemove = array_diff(
			$currentConnectionIds,
			$model->connectionIds
		);
		
		foreach ($connectionsToRemove as $targetId) {
		
			Connection::deleteAll([
				'or',
				[
					'from_node_id' => $model->id,
					'to_node_id' => $targetId,
				],
				[
					'from_node_id' => $targetId,
					'to_node_id' => $model->id,
				],
			]);
		}
		
			foreach ($connectionsToAdd as $targetId) {
				$targetId = (int) $targetId;
				if ($targetId === (int) $model->id || !Node::find()->where(['id' => $targetId])->exists()) {
					continue;
				}
		
			$connection = new Connection();
		
			$connection->from_node_id =
				$model->id;
		
			$connection->to_node_id =
				$targetId;
		
			$connection->color = '#666666';
		
			$connection->width = 2;
		
			$connection->style = 'solid';
		
				$connection->save();
		}

		
			return $this->redirect(['index']);
		}
			
		/*
		----------------------------------------------------
		Formular
		----------------------------------------------------
		*/
	
		return $this->render('form', [
			'model' => $model,
		]);
	}
	
	/*
	----------------------------------------------------
	Linien ein-/ausblenden
	----------------------------------------------------
	*/
	
	public function actionToggleLines()
	{
	
		$showLines =
			Yii::$app->request->post('showLines', 0) == 1;
	
		Yii::$app->getModule('orgmap')
			->settings
			->set('showLines', $showLines);
	
		return $this->redirect(['index']);
	}


	/*
	----------------------------------------------------
	Textposition zurücksetzen
	----------------------------------------------------
	*/
	
	public function actionResetLabel($id)
	{
	
		$model = Node::findOne($id);
	
		if (!$model) {
	
			throw new \yii\web\NotFoundHttpException();
		}
	
		$model->label_x = null;
		$model->label_y = null;
	
		$model->save(false);
	
		return $this->redirect(['index']);
	}
	
	/*
	----------------------------------------------------
	Sichtbarkeit umschalten
	----------------------------------------------------
	*/
	
	public function actionToggleVisible($id)
	{
	
		$model = Node::findOne($id);
	
		if (!$model) {
	
			throw new \yii\web\NotFoundHttpException();
		}
	
		$model->visible = !$model->visible;
	
		$model->save(false);
	
		return $this->redirect(['index']);
	}
	
	/*
	----------------------------------------------------
	Löschen
	----------------------------------------------------
	*/
	
		public function actionDelete($id)
		{
	
		$model = Node::findOne($id);
	
			if ($model) {
				$transaction = Yii::$app->db->beginTransaction();
				try {
					Connection::deleteAll([
						'or',
						['from_node_id' => $model->id],
						['to_node_id' => $model->id],
					]);

					if ($model->delete() === false) {
						throw new \RuntimeException('Node could not be deleted.');
					}
					$transaction->commit();
				} catch (\Throwable $e) {
					$transaction->rollBack();
					throw $e;
				}
			}
	
		return $this->redirect(['index']);
	}
	
	/*
	----------------------------------------------------
	Connection bearbeiten
	----------------------------------------------------
	*/	
	
	public function actionEditConnection($id)
	{
	
		$model = Connection::findOne($id);
	
		if (!$model) {
	
			throw new \yii\web\NotFoundHttpException();
		}
	
		if ($model->load(Yii::$app->request->post())) {

			$model->applyTypeDefaults();
		
			if ($model->save()) {
		
				return $this->redirect([
					'update',
					'id' => $model->from_node_id
				]);
			}
		}
	
		return $this->render('connection-form', [
	
			'model' => $model,
	
		]);
	}
	
	/*
	----------------------------------------------------
	Einstellungen
	----------------------------------------------------
	*/
	
public function actionSettings()
{

    $settings =
        Yii::$app->getModule('orgmap')->settings;
        $model = new SettingsForm();

    /*
    ----------------------------------------------------
    Speichern
    ----------------------------------------------------
    */

    if (Yii::$app->request->isPost) {

		$model->load(Yii::$app->request->post());
		$model->workspaceSize = Yii::$app->request->post('workspace_size');
		
		if (!$model->save()) {
			return $this->render('settings', ['model' => $model, 'workspaceSize' => $model->workspaceSize]);
		}
	

        Yii::$app->session->setFlash(
            'success',
            Yii::t(
                'OrgmapModule.base',
                'Gespeichert'
            )
        );

        return $this->refresh();
    }

    /*
    ----------------------------------------------------
    View
    ----------------------------------------------------
    */

	return $this->render('settings', [
	
		'model' => $model,
	
		'workspaceSize' => $settings->get(
			'workspaceSize',
			'medium'
		),
	]);
}

/*
----------------------------------------------------
Connection Label speichern
----------------------------------------------------
*/

public function actionSaveConnectionLabel()
{

	Yii::$app->response->format =
		\yii\web\Response::FORMAT_JSON;

	$data = json_decode(
		Yii::$app->request->rawBody,
		true
	);

	if (!is_array($data) || !isset($data['id'])) {
		throw new \yii\web\BadRequestHttpException();
	}

	$model = Connection::findOne(
		$data['id'] ?? null
	);

	if (!$model) {
		throw new \yii\web\NotFoundHttpException();
	}

	$model->label_offset_x =
		max(-10000, min(10000, (int) ($data['label_offset_x'] ?? 0)));

	$model->label_offset_y =
		max(-10000, min(10000, (int) ($data['label_offset_y'] ?? 0)));

	$saved = $model->save(false);

	return [
		'success' => $saved
	];
}
	
}
