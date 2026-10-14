<?php
/** Runs all P1 checks first, then P2 against the SAME guarded isolated database. */
declare(strict_types=1);

ob_start();
require __DIR__ . '/P1IntegrationTest.php';
$p1 = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
$start = $checks;
identity(1);
Yii::setAlias('@themes', $core . '/themes'); // Read core theme assets; publish only to isolated webroot.

use humhub\modules\orgmap\helpers\BackgroundImageHelper;
use humhub\modules\orgmap\helpers\SpacePresentationHelper;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\controllers\MapController;

$space = humhub\modules\space\models\Space::findOne(1);
foreach ([false, true] as $pretty) {
    $app->urlManager->enablePrettyUrl = $pretty;
    $url = SpacePresentationHelper::aboutUrl($space);
    check($url === $space->createUrl('/space/space/about'), 'Space about route mismatch');
    if (!$pretty) {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        check(($query['r'] ?? '') === 'space/space/about', 'Non-pretty about route invalid');
        check(($query['cguid'] ?? '') === $space->guid, 'Non-pretty container GUID missing');
    } else {
        check(str_contains($url, 'space/space/about'), 'Pretty about route missing');
        check(str_contains($url, $space->guid), 'Pretty container GUID missing');
    }
}
$app->urlManager->enablePrettyUrl = false;
$expectedImage = method_exists($space, 'getImage') ? $space->getImage()->getUrl() : $space->getProfileImage()->getUrl();
check(SpacePresentationHelper::imageUrl($space) === $expectedImage, 'Version-specific Space image API mismatch');
if (method_exists($space, 'getImage')) {
    $eventSpace = humhub\modules\space\models\Space::findOne(1);
    $imageEvent = false;
    $eventSpace->on($eventSpace::EVENT_INIT_PROFILE_IMAGE, static function () use (&$imageEvent): void { $imageEvent = true; });
    SpacePresentationHelper::imageUrl($eventSpace);
    check($imageEvent, 'New image initialization event was bypassed');
}

foreach ([20, 5000, 5001, 10000] as $value) {
    $candidate = new Node(['title' => 'Boundary', 'width' => $value, 'height' => $value]);
    check($candidate->validate(['width', 'height']), 'Valid workspace dimension rejected: ' . $value);
}
foreach ([19, 10001, 'invalid', 20.5] as $value) {
    $candidate = new Node(['title' => 'Boundary', 'width' => $value]);
    check(!$candidate->validate(['width']), 'Invalid dimension accepted');
}
foreach ([20, 5000] as $value) {
    $candidate = new Node(['title' => 'Radius', 'radius' => $value]);
    check($candidate->validate(['radius']), 'Valid radius rejected');
}
$candidate = new Node(['title' => 'Radius', 'radius' => 5001]);
check(!$candidate->validate(['radius']), 'Oversized radius accepted');
$candidate = new Node(['title' => 'Icon', 'icon_class' => str_repeat('x', 100)]);
check($candidate->validate(['icon_class']), 'Schema-length icon rejected');
$candidate->icon_class .= 'x';
check(!$candidate->validate(['icon_class']), 'Icon can still overflow existing column');

$backgroundNode = node('Large preserved background', ['width' => 10000, 'height' => 8000, 'radius' => 80, 'is_background' => 1]);
check($backgroundNode->validate(['width', 'height']), 'Background cannot fill workspace');
check($backgroundNode->save(false), 'Background fixture failed');
$app->request->enableCsrfValidation = false; // Isolated direct controller harness, not module configuration.
$controller = new MapController('map', $module);
function saveP2Position(MapController $controller, array $payload): array {
    global $app;
    $app->request->setRawBody(json_encode($payload));
    $app->response->statusCode = 200;
    return $controller->actionSavePosition();
}
$result = saveP2Position($controller, ['id' => $backgroundNode->id, 'x' => 700, 'width' => 10000, 'height' => 8000]);
check($result['success'] === true, 'Large background position save failed');
$backgroundNode->refresh();
check((int) $backgroundNode->width === 10000 && (int) $backgroundNode->height === 8000, 'Position endpoint shrank background');
$before = $backgroundNode->attributes;
$result = saveP2Position($controller, ['id' => $backgroundNode->id, 'x' => 800, 'width' => 10001]);
check($result['success'] === false && $app->response->statusCode === 422, 'Invalid size not rejected explicitly');
$backgroundNode->refresh();
check($backgroundNode->attributes === $before, 'Invalid size partially changed existing row');
$backgroundNode->updateAttributes(['width' => 12000]);
check($backgroundNode->validate(['width']), 'Historical dimension cannot remain unchanged');
$result = saveP2Position($controller, ['id' => $backgroundNode->id, 'x' => 900, 'width' => 12000]);
check($result['success'] === true, 'Unchanged legacy size blocked position update');
$backgroundNode->refresh();
check((int) $backgroundNode->width === 12000, 'Legacy dimension was silently clamped');

check(!BackgroundImageHelper::fitsMemoryBudget(10000, 4000, 1600, 640, 128 * 1048576, 16 * 1048576), 'Large decode fits implausibly small memory budget');
check(BackgroundImageHelper::fitsMemoryBudget(2000, 1000, 1600, 800, 256 * 1048576, 16 * 1048576), 'Normal preview unnecessarily rejected');
check(BackgroundImageHelper::fitsMemoryBudget(10000, 4000, 1600, 640, -1, 0), 'Unlimited memory rejected');
$lowMemoryAsset = new humhub\modules\orgmap\models\Asset(['title' => 'No preview budget', 'filename' => 'budget.png', 'type' => 'background']);
check($lowMemoryAsset->save(false), 'Low memory asset fixture failed');
$originalLimit = ini_get('memory_limit');
try {
    ini_set('memory_limit', (string) (memory_get_usage(true) + 8 * 1048576));
    humhub\modules\orgmap\services\AssetStorage::saveUpload($lowMemoryAsset, $largePng);
    check($lowMemoryAsset->preview_file_guid === null, 'Optimizer decoded despite inadequate memory');
    check(humhub\modules\orgmap\services\AssetStorage::read(humhub\modules\orgmap\services\AssetStorage::file($lowMemoryAsset, false)) === $largePng, 'Missing preview lost original');
} finally { ini_set('memory_limit', $originalLimit); }

// Real beforeAction results: allowed means true, never merely "is bool".
$_SERVER['REQUEST_METHOD'] = 'GET';
$app->response->statusCode = 200;
foreach (['index', 'tree'] as $id) {
    $action = new yii\base\Action($id, $controller);
    $controller->action = $action; $app->controller = $controller;
    check($controller->beforeAction($action) === true, 'Authorized route did not pass core filters');
}
$_SERVER['REQUEST_METHOD'] = 'POST';
foreach ([
    'admin' => ['index', 'create', 'update', 'toggle-lines', 'reset-label', 'toggle-visible', 'delete', 'edit-connection', 'settings', 'fit-workspace-to-background', 'save-connection-label'],
    'asset' => ['index', 'create', 'delete'],
    'organ' => ['index', 'create', 'update', 'delete'],
    'map' => ['save-position'],
] as $controllerId => $actionIds) {
    $class = 'humhub\\modules\\orgmap\\controllers\\' . ucfirst($controllerId) . 'Controller';
    foreach ($actionIds as $id) {
        $allowed = new $class($controllerId, $module);
        $action = new yii\base\Action($id, $allowed); $allowed->action = $action; $app->controller = $allowed;
        check($allowed->beforeAction($action) === true, 'Authorized management action blocked: ' . $controllerId . '/' . $id);
    }
}

if (class_exists(humhub\components\gates\UserGate::class)) {
    $gate = new class extends humhub\components\gates\UserGate {
        public function getId(): string { return 'orgmap-isolated-test'; }
        public function getSortOrder(): int { return 50; }
        public function isOpen(): bool { return true; }
        public function isCacheable(): bool { return false; }
        public function getRoute(): array { return ['/user/auth/login']; }
    };
    $app->user->enableSession = true;
    $app->request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $app->gateManager->register($gate);
    foreach (['index', 'tree', 'save-position'] as $id) {
        $_SERVER['REQUEST_METHOD'] = $id === 'save-position' ? 'POST' : 'GET';
        $blocked = new MapController('map', $module);
        $action = new yii\base\Action($id, $blocked); $blocked->action = $action; $app->controller = $blocked;
        check($blocked->beforeAction($action) === false, 'Open core gate did not intercept ' . $id);
        check($app->response->statusCode === 401, 'Gate status mismatch');
        check($app->response->headers->get('X-Redirect') !== null, 'Gate redirect header missing');
    }
    $app->gateManager->deregister($gate->getId());
    $app->user->enableSession = false;
}

echo json_encode(['core' => $app->version, 'php' => PHP_VERSION, 'database' => $name, 'p1_checks' => $p1['checks'], 'p2_checks' => $checks - $start, 'checks' => $checks, 'result' => 'PASS'], JSON_PRETTY_PRINT) . PHP_EOL;
