<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\controllers\AdminController;
use humhub\modules\orgmap\controllers\AssetController;
use humhub\modules\orgmap\controllers\MapController;
use humhub\modules\orgmap\controllers\OrganController;
use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use orgmap\OrgMapTestCase;
use Yii;
use yii\base\Action;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\User;

class ControllerPermissionsTest extends OrgMapTestCase
{
	private $originalUser;
	private $originalController;

	protected function setUp(): void
	{
		parent::setUp();
		$this->originalUser = Yii::$app->get('user');
		$this->originalController = Yii::$app->controller;
	}

	protected function tearDown(): void
	{
		Yii::$app->set('user', $this->originalUser);
		Yii::$app->controller = $this->originalController;
		parent::tearDown();
	}

	/**
	 * @dataProvider managementActionProvider
	 */
	public function testManagementActionsRejectUsersWithoutPermission(string $controllerClass, string $actionId): void
	{
		$this->setUser(false, false, ManageOrgMap::class);
		$controller = new $controllerClass($this->controllerId($controllerClass), Yii::$app->getModule('orgmap'));

		$this->expectException(ForbiddenHttpException::class);
		$controller->beforeAction(new Action($actionId, $controller));
	}

	/**
	 * @dataProvider managementActionProvider
	 */
	public function testManagementActionsAcceptUsersWithPermission(string $controllerClass, string $actionId): void
	{
		$this->setUser(false, true, ManageOrgMap::class);
		$controller = new $controllerClass($this->controllerId($controllerClass), Yii::$app->getModule('orgmap'));

		$this->assertControllerDoesNotReject($controller, $actionId);
	}

	/**
	 * @dataProvider viewActionProvider
	 */
	public function testMapViewActionsRejectUsersWithoutPermission(string $actionId): void
	{
		$this->setUser(false, false, ViewOrgMap::class);
		$controller = new MapController('map', Yii::$app->getModule('orgmap'));

		$this->expectException(ForbiddenHttpException::class);
		$controller->beforeAction(new Action($actionId, $controller));
	}

	/**
	 * @dataProvider viewActionProvider
	 */
	public function testMapViewActionsAcceptUsersWithPermission(string $actionId): void
	{
		$this->setUser(false, true, ViewOrgMap::class);
		$controller = new MapController('map', Yii::$app->getModule('orgmap'));

		$this->assertControllerDoesNotReject($controller, $actionId);
	}

	/**
	 * @dataProvider viewActionProvider
	 */
	public function testMapViewActionsRespectGuestSetting(string $actionId): void
	{
		$settings = Yii::$app->getModule('orgmap')->settings;
		$originalValue = $settings->get('allowGuestAccess', false);
		$this->setUser(true, false, ViewOrgMap::class);
		$controller = new MapController('map', Yii::$app->getModule('orgmap'));

		try {
			$settings->set('allowGuestAccess', true);
			$this->assertControllerDoesNotReject($controller, $actionId);

			$settings->set('allowGuestAccess', false);
			$this->expectException(ForbiddenHttpException::class);
			$controller->beforeAction(new Action($actionId, $controller));
		} finally {
			$settings->set('allowGuestAccess', $originalValue);
		}
	}

	public function testStateChangingActionsRequirePost(): void
	{
		$expected = [
			AdminController::class => [
				'delete', 'toggle-visible', 'toggle-lines', 'reset-label',
				'save-connection-label', 'fit-workspace-to-background',
			],
			AssetController::class => ['delete'],
			OrganController::class => ['delete'],
			MapController::class => ['save-position'],
		];

		foreach ($expected as $controllerClass => $actionIds) {
			$controller = new $controllerClass($this->controllerId($controllerClass), Yii::$app->getModule('orgmap'));
			$behaviors = $controller->behaviors();
			$this->assertSame(VerbFilter::class, $behaviors['verbs']['class']);

			foreach ($actionIds as $actionId) {
				$this->assertSame(['POST'], $behaviors['verbs']['actions'][$actionId], $controllerClass . ':' . $actionId);
			}
		}
	}

	public function managementActionProvider(): array
	{
		return [
			['controllerClass' => AdminController::class, 'actionId' => 'index'],
			['controllerClass' => AdminController::class, 'actionId' => 'create'],
			['controllerClass' => AdminController::class, 'actionId' => 'update'],
			['controllerClass' => AdminController::class, 'actionId' => 'toggle-lines'],
			['controllerClass' => AdminController::class, 'actionId' => 'reset-label'],
			['controllerClass' => AdminController::class, 'actionId' => 'toggle-visible'],
			['controllerClass' => AdminController::class, 'actionId' => 'delete'],
			['controllerClass' => AdminController::class, 'actionId' => 'edit-connection'],
			['controllerClass' => AdminController::class, 'actionId' => 'settings'],
			['controllerClass' => AdminController::class, 'actionId' => 'fit-workspace-to-background'],
			['controllerClass' => AdminController::class, 'actionId' => 'save-connection-label'],
			['controllerClass' => AssetController::class, 'actionId' => 'index'],
			['controllerClass' => AssetController::class, 'actionId' => 'create'],
			['controllerClass' => AssetController::class, 'actionId' => 'delete'],
			['controllerClass' => OrganController::class, 'actionId' => 'index'],
			['controllerClass' => OrganController::class, 'actionId' => 'create'],
			['controllerClass' => OrganController::class, 'actionId' => 'update'],
			['controllerClass' => OrganController::class, 'actionId' => 'delete'],
			['controllerClass' => MapController::class, 'actionId' => 'save-position'],
		];
	}

	public function viewActionProvider(): array
	{
		return [
			['actionId' => 'index'],
			['actionId' => 'tree'],
		];
	}

	private function setUser(bool $isGuest, bool $allowed, string $expectedPermission): void
	{
		$user = new PermissionTestUser([
			'isTestGuest' => $isGuest,
			'allowed' => $allowed,
			'expectedPermission' => $expectedPermission,
			'identityClass' => \humhub\modules\user\models\User::class,
			'enableSession' => false,
		]);
		Yii::$app->set('user', $user);
	}

	private function assertControllerDoesNotReject($controller, string $actionId): void
	{
		$action = new Action($actionId, $controller);
		$controller->action = $action;
		Yii::$app->controller = $controller;

		$this->assertIsBool($controller->beforeAction($action));
	}

	private function controllerId(string $controllerClass): string
	{
		return match ($controllerClass) {
			AdminController::class => 'admin',
			AssetController::class => 'asset',
			OrganController::class => 'organ',
			MapController::class => 'map',
		};
	}
}

class PermissionTestUser extends User
{
	public bool $isTestGuest = false;
	public bool $allowed = false;
	public string $expectedPermission = '';

	public function getIsGuest()
	{
		return $this->isTestGuest;
	}

	public function can($permissionName, $params = [], $allowCaching = true)
	{
		return $permissionName === $this->expectedPermission && $this->allowed;
	}
}
