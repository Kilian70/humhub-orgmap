<?php
/**
 * CLI regression checks using a real HumHub core and an isolated MySQL schema.
 * php tests/P1IntegrationTest.php CORE VENDOR DB_CONFIG TEST_ROOT
 * DB_CONFIG is an external PHP file returning Yii connection settings; never commit credentials.
 */
declare(strict_types=1);

if ($argc !== 5) { throw new RuntimeException('Expected CORE VENDOR DB_CONFIG TEST_ROOT'); }
[$script, $core, $vendor, $dbConfig, $testRoot] = $argv;
$moduleRoot = dirname(__DIR__);
$testRoot = rtrim($testRoot, '/') . '/run-' . bin2hex(random_bytes(3));
define('YII_DEBUG', false); define('YII_ENV', 'test'); define('YII_ENABLE_ERROR_HANDLER', false);
set_exception_handler(static function (Throwable $error): void { fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . ' at ' . $error->getFile() . ':' . $error->getLine() . PHP_EOL); exit(1); });
require $vendor . '/autoload.php'; require $vendor . '/yiisoft/yii2/Yii.php';
$_ENV['HUMHUB_ALIASES__HUMHUB'] = $core . '/protected/humhub';
$config = yii\helpers\ArrayHelper::merge(require $core . '/protected/humhub/config/common.php', require $core . '/protected/humhub/config/web.php');
foreach (['protected', 'protected/runtime', 'assets', 'uploads/file'] as $dir) {
    yii\helpers\FileHelper::createDirectory($testRoot . '/' . $dir, 0700);
}
$_SERVER['SCRIPT_FILENAME'] = $testRoot . '/index.php'; $_SERVER['SCRIPT_NAME'] = '/index.php'; $_SERVER['SERVER_NAME'] = 'localhost';
$config['id'] = 'orgmap-regression'; $config['basePath'] = $testRoot . '/protected'; $config['runtimePath'] = $testRoot . '/protected/runtime';
$config['bootstrap'] = []; $config['aliases']['@root'] = $testRoot; $config['aliases']['@webroot'] = $testRoot;
$config['aliases']['@filestore'] = $testRoot . '/uploads/file';
$config['aliases']['@humhub/modules/orgmap'] = $moduleRoot; $config['aliases']['@orgmap'] = $moduleRoot;
$config['components']['db'] = ['class' => yii\db\Connection::class] + (require $dbConfig);
$config['components']['i18n']['translations']['OrgmapModule.*'] = ['class' => yii\i18n\PhpMessageSource::class, 'basePath' => $moduleRoot . '/messages', 'fileMap' => ['OrgmapModule.base' => 'base.php']];
foreach (['user', 'file', 'space', 'content', 'admin'] as $id) { $category = ucfirst($id) . 'Module'; $config['components']['i18n']['translations'][$category . '.*'] = ['class' => yii\i18n\PhpMessageSource::class, 'basePath' => $core . '/protected/humhub/modules/' . $id . '/messages', 'fileMap' => [$category . '.base' => 'base.php']]; }
$config['components']['cache'] = ['class' => yii\caching\ArrayCache::class];
$config['components']['runtimeCache'] = ['class' => yii\caching\ArrayCache::class];
$config['components']['log'] = ['targets' => []];
$config['components']['request'] = ['class' => humhub\components\Request::class, 'cookieValidationKey' => 'isolated-tests', 'scriptFile' => $testRoot . '/index.php', 'scriptUrl' => '/index.php', 'baseUrl' => ''];
$config['components']['urlManager'] = ['class' => humhub\components\UrlManager::class, 'enablePrettyUrl' => false, 'showScriptName' => true, 'baseUrl' => '', 'hostInfo' => 'http://localhost', 'scriptUrl' => '/index.php'];
$config['components']['session'] = ['class' => yii\web\CacheSession::class, 'cache' => 'cache'];
$config['modules'] = [];
foreach (['user', 'file', 'space', 'content', 'admin'] as $id) { $config['modules'][$id] = ['class' => 'humhub\\modules\\' . $id . '\\Module']; }
$config['modules']['orgmap'] = ['class' => humhub\modules\orgmap\Module::class];
$config['params']['moduleAutoloadPaths'] = [$moduleRoot, $core . '/protected/humhub/modules'];
$app = new humhub\components\Application($config);
class FailingOrgMapTestStore extends humhub\modules\file\components\StorageManager
{
    public function setContent($content, $variant = null): void { throw new RuntimeException('Simulated isolated backend failure'); }
}
$db = $app->db;
$name = (string) $db->createCommand('SELECT DATABASE()')->queryScalar();
if (!preg_match('/^orgmap_test_p1_(118|119)_[a-f0-9]{8}$/', $name)) { throw new RuntimeException('Refusing to run against a non-isolated database.'); }
$checks = 0;
function check(bool $condition, string $message): void { global $checks; if (!$condition) { throw new RuntimeException($message); } $checks++; }
function rawInsert(string $table, array $values): void {
    global $db;
    foreach ($db->schema->getTableSchema($table)->columns as $name => $column) {
        if (!array_key_exists($name, $values) && !$column->allowNull && $column->defaultValue === null && !$column->autoIncrement) {
            $values[$name] = in_array($column->phpType, ['integer', 'double'], true) ? 0 : '';
        }
    }
    $db->createCommand()->insert($table, $values)->execute();
}
function identity(int $id): humhub\modules\user\models\User {
    global $app;
    $component = new humhub\modules\user\components\User(['identityClass' => humhub\modules\user\models\User::class, 'enableSession' => false]);
    $app->set('user', $component);
    $user = humhub\modules\user\models\User::findOne($id);
    $component->switchIdentity($user);
    return $user;
}
function node(string $title, array $values = []): humhub\modules\orgmap\models\Node {
    $node = new humhub\modules\orgmap\models\Node(array_merge(['title' => $title, 'visible' => 1, 'shape' => 'circle', 'image_source' => 'none', 'display_mode' => 'color', 'width' => 160, 'height' => 160, 'radius' => 80, 'pos_x' => 100, 'pos_y' => 100, 'color' => '#336699', 'font_size' => 16], $values));
    check($node->save(), 'Could not save test node: ' . json_encode($node->getErrors()));
    return $node;
}
function schemaUpgrade(): void { global $db; ob_start(); try { (new humhub\modules\orgmap\services\SchemaUpgrade(['db' => $db]))->apply(); } finally { ob_end_clean(); } }
function dropModuleTables(): void {
    global $db;
    check(str_starts_with((string) $db->createCommand('SELECT DATABASE()')->queryScalar(), 'orgmap_test_p1_'), 'Isolation lost');
    $db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
    foreach (['orgmap_connection', 'orgmap_node', 'orgmap_organ', 'orgmap_asset'] as $name) {
        foreach (array_unique([$name, 'hh_' . $name]) as $table) {
            if ($db->schema->getTableSchema($table, true)) { $db->createCommand()->dropTable($table)->execute(); }
        }
    }
    $db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute(); $db->schema->refresh();
}
dropModuleTables();
$db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
foreach ($db->schema->getTableNames() as $table) { $db->createCommand()->truncateTable($table)->execute(); }
$db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
$app->installationState->setInstalled();
foreach ([1, 2, 3] as $id) {
    rawInsert('group', ['id' => $id, 'name' => 'OrgMap Test ' . $id, 'is_admin_group' => 0]);
    rawInsert('user', ['id' => $id, 'username' => 'orgmap_test_' . $id, 'status' => 1, 'guid' => sprintf('00000000-0000-4000-8000-%012d', $id), 'email' => 'orgmap' . $id . '@example.invalid']);
    rawInsert('group_user', ['group_id' => $id, 'user_id' => $id]);
}
rawInsert('group', ['id' => 4, 'name' => 'Administrators', 'is_admin_group' => 1]);
$manager = new humhub\modules\user\components\PermissionManager();
foreach ([1, 2] as $group) { $manager->setGroupState($group, new humhub\modules\orgmap\permissions\ViewOrgMap(), humhub\libs\BasePermission::STATE_ALLOW); }
$manager->setGroupState(1, new humhub\modules\orgmap\permissions\ManageOrgMap(), humhub\libs\BasePermission::STATE_ALLOW);
$admin = identity(1); $viewer = humhub\modules\user\models\User::findOne(2); $denied = humhub\modules\user\models\User::findOne(3);
foreach ([1 => humhub\modules\space\models\Space::VISIBILITY_ALL, 2 => humhub\modules\space\models\Space::VISIBILITY_REGISTERED_ONLY, 3 => humhub\modules\space\models\Space::VISIBILITY_NONE] as $id => $visibility) {
    rawInsert('space', ['id' => $id, 'guid' => sprintf('00000000-0000-4000-9000-%012d', $id), 'name' => 'Test Space ' . $id, 'visibility' => $visibility]);
}
rawInsert('space_membership', ['space_id' => 3, 'user_id' => 1, 'group_id' => 'member', 'status' => 1]);
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

// Fresh install, both prefix strategies, then collision: no schema mutation on ambiguity.
schemaUpgrade(); check($db->schema->getTableSchema('orgmap_node', true) !== null, 'Fresh schema missing');
$first = node('Fresh retained'); $first->refresh(); $attrs = $first->attributes; schemaUpgrade(); check($first->refresh() && $first->attributes === $attrs, 'Idempotent upgrade changed a node');
// Earlier releases had a single opacity column. Preserve it and backfill new fields only.
$db->createCommand()->dropColumn('orgmap_node', 'color_opacity')->execute();
$db->createCommand()->dropColumn('orgmap_node', 'image_opacity')->execute();
$db->createCommand()->update('orgmap_node', ['opacity' => 45, 'display_mode' => 'mixed'], ['id' => $first->id])->execute();
$db->schema->refresh(); schemaUpgrade(); $first->refresh();
check((int) $first->opacity === 45 && (int) $first->color_opacity === 45 && (int) $first->image_opacity === 100, 'Early opacity upgrade changed appearance or original value');
check(count(glob($moduleRoot . '/migrations/m*.php')) === 2 && count(glob($moduleRoot . '/migrations/legacy/*.php')) === 35, 'Legacy migrations remain executable or archive is incomplete');
$badLayout = $testRoot . '/bad-layout'; yii\helpers\FileHelper::createDirectory($badLayout . '/migrations/legacy');
file_put_contents($badLayout . '/migrations/legacy/m260711_150000_harden_orgmap_relations.php', '<?php');
file_put_contents($badLayout . '/migrations/m260711_150000_harden_orgmap_relations.php', '<?php');
try { humhub\modules\orgmap\services\PreservingMigrationService::assertLayout($badLayout); check(false, 'Unsafe overlay layout accepted'); } catch (LogicException $expected) { check(true, 'Unsafe overlay layout refused'); }
dropModuleTables(); $db->tablePrefix = 'hh_'; schemaUpgrade();
check(humhub\modules\orgmap\models\Node::tableName() === 'hh_orgmap_node', 'Fresh prefixed table not selected');
$prefixed = node('Prefixed retained'); check($prefixed->refresh(), 'Prefixed node unavailable');
$db->createCommand()->createTable('orgmap_node', ['id' => 'pk', 'marker' => 'text'])->execute();
try { schemaUpgrade(); check(false, 'Ambiguous tables were accepted'); } catch (yii\base\InvalidConfigException $expected) { check(true, 'Ambiguity refused'); }
check($db->schema->getTableSchema('orgmap_node', true)->getColumn('marker') !== null, 'Collision mutated legacy table');
dropModuleTables(); $db->tablePrefix = '';

// Published legacy DDL builds an old schema in this isolated DB; the destructive cleanup is excluded.
foreach (glob($moduleRoot . '/migrations/legacy/m*.php') as $path) {
    if (str_contains($path, 'harden_orgmap_relations')) { continue; }
    require_once $path; $class = basename($path, '.php');
    ob_start(); try { check((new $class(['db' => $db]))->up() !== false, 'Legacy test schema could not be prepared');
        rawInsert('migration', ['version' => $class, 'apply_time' => 123456]); } finally { ob_end_clean(); }
}
rawInsert('orgmap_node', ['id' => 500, 'title' => 'Legacy kept', 'opacity' => 37, 'color_opacity' => 37, 'image_opacity' => 100, 'parent_id' => 9999, 'space_id' => 9999]);
rawInsert('orgmap_connection', ['id' => 600, 'from_node_id' => 500, 'to_node_id' => 9999, 'label' => 'Orphan preserved']);
rawInsert('orgmap_connection', ['id' => 601, 'from_node_id' => 500, 'to_node_id' => 500, 'label' => 'Self preserved']);
rawInsert('orgmap_asset', ['id' => 700, 'title' => 'Legacy original', 'filename' => 'original.png', 'type' => 'image', 'created_at' => 123456]);
$beforeNode = $db->createCommand('SELECT * FROM orgmap_node WHERE id=500')->queryOne();
$beforeConnections = $db->createCommand('SELECT * FROM orgmap_connection ORDER BY id')->queryAll();
$beforeAsset = $db->createCommand('SELECT * FROM orgmap_asset WHERE id=700')->queryOne();
$public = $testRoot . '/uploads/orgmap/assets'; yii\helpers\FileHelper::createDirectory($public);
file_put_contents($public . '/original.png', $png); file_put_contents($public . '/unreferenced.bin', 'keep unreferenced bytes');
// Prefix config on an old unprefixed installation must keep its original tables and IDs.
$db->tablePrefix = 'hh_'; schemaUpgrade();
check(humhub\modules\orgmap\models\Node::tableName() === 'orgmap_node', 'Legacy table moved or ignored');
check($db->createCommand('SELECT * FROM orgmap_node WHERE id=500')->queryOne() === $beforeNode, 'Legacy node was changed');
check($db->createCommand('SELECT * FROM orgmap_connection ORDER BY id')->queryAll() === $beforeConnections, 'Invalid/self connections were deleted or changed');
humhub\modules\orgmap\services\LegacyAssetImport::run();
$asset = humhub\modules\orgmap\models\Asset::findOne(700); $file = humhub\modules\orgmap\services\AssetStorage::file($asset, false);
check($file instanceof humhub\modules\file\models\File, 'Legacy File reference missing');
check(humhub\modules\orgmap\services\AssetStorage::read($file) === $png, 'Legacy original bytes changed');
foreach ($beforeAsset as $key => $value) { check((string) $asset->$key === (string) $value, 'Legacy asset metadata changed: ' . $key); }
$archive = humhub\modules\orgmap\services\LegacyAssetImport::archivePath();
check(!is_dir($public), 'Public legacy image path still exists');
check(file_get_contents($archive . '/original.png') === $png && file_get_contents($archive . '/unreferenced.bin') === 'keep unreferenced bytes', 'Archive lost files');
humhub\modules\orgmap\services\LegacyAssetImport::run(); check(humhub\modules\orgmap\services\AssetStorage::file($asset, false)->guid === $file->guid, 'Retry replaced an original');

// Real File polymorphic ACL, not just a module-controller permission check.
$publicNode = node('Public', ['space_id' => 1, 'asset_id' => 700]);
$registered = node('Registered', ['space_id' => 2]); $private = node('Private', ['space_id' => 3]);
check($file->canView($viewer), 'Viewer denied public image'); check(!$file->canView($denied), 'Denied user can download image');
$asset->updateAttributes(['deleted_at' => time()]); check(!$file->canView($viewer), 'Archived asset image remains viewable'); $asset->updateAttributes(['deleted_at' => null]);
$viewer = identity(2); $visibleIds = humhub\modules\orgmap\helpers\AccessHelper::nodeQuery()->select('id')->column();
check(in_array($publicNode->id, $visibleIds) && in_array($registered->id, $visibleIds) && !in_array($private->id, $visibleIds), 'Space visibility leaked private node');
$controller = new class('map', $app->getModule('orgmap')) extends humhub\modules\orgmap\controllers\MapController {
    public function render($view, $params = []) { return $params; }
};
$map = $controller->actionIndex(); $tree = $controller->actionTree();
check(!in_array($private->id, array_map(static fn($node) => $node->id, $map['nodes'])), 'Map controller leaked private node');
check(!in_array($private->id, array_map(static fn($node) => $node->id, $tree['treeNodes'])), 'Tree controller leaked private node');
$bad = new humhub\modules\orgmap\models\Node(['title' => 'Forbidden', 'space_id' => 3]); check(!$bad->validate(), 'Private space ID accepted');
try { humhub\modules\orgmap\helpers\AccessHelper::assertNode($private); check(false, 'Private node mutation allowed'); } catch (yii\web\ForbiddenHttpException $expected) { check(true, 'Private node denied'); }
$privateAsset = new humhub\modules\orgmap\models\Asset(['title' => 'Private image', 'filename' => 'private.png', 'type' => 'image']); check($privateAsset->save(false), 'Private asset could not be saved');
humhub\modules\orgmap\services\AssetStorage::saveUpload($privateAsset, $png);
identity(1); $private->asset_id = $privateAsset->id; check($private->save(), 'Private asset association failed');
$privateFile = humhub\modules\orgmap\services\AssetStorage::file($privateAsset);
check(!$privateFile->canView($viewer) && $privateFile->canView($admin), 'Private asset ACL incorrect');

// Guests require BOTH settings and may only see public spaces/nodes and associated files.
$app->set('user', new humhub\modules\user\components\User(['identityClass' => humhub\modules\user\models\User::class, 'enableSession' => false]));
$app->getModule('user')->settings->set('auth.allowGuestAccess', 1); $module = $app->getModule('orgmap'); $module->settings->set('allowGuestAccess', 1);
check($file->canView(), 'Guest denied public asset despite allowed settings');
$guestIds = humhub\modules\orgmap\helpers\AccessHelper::nodeQuery()->select('id')->column();
check(in_array($publicNode->id, $guestIds) && !in_array($registered->id, $guestIds) && !in_array($private->id, $guestIds), 'Guest space visibility incorrect');
$module->settings->set('allowGuestAccess', 0); check(!$file->canView(), 'Guest setting did not revoke image access');

// New originals use the core store; metadata keeps the published integer timestamp format.
identity(1); $newAsset = new humhub\modules\orgmap\models\Asset(['title' => 'New original', 'filename' => 'new.png', 'type' => 'image']);
check($newAsset->save(false), 'New asset could not be saved'); humhub\modules\orgmap\services\AssetStorage::saveUpload($newAsset, $png);
check(is_numeric($newAsset->created_at), 'Legacy timestamp format changed'); check(!is_dir($public), 'Upload recreated public storage');
check(humhub\modules\orgmap\services\AssetStorage::read(humhub\modules\orgmap\services\AssetStorage::file($newAsset)) === $png, 'New upload lost bytes');
check(str_contains($newAsset->getImageUrl(), 'file%2Ffile%2Fdownload'), 'Image URL bypasses core download');

// Restore the original core history-table configuration after module prefix scenarios.
$db->tablePrefix = '';
// Optimization creates a separate preview while the original bytes remain unchanged.
$module = $app->getModule('orgmap'); $module->settings->set('workspaceSize', 'small');
$image = imagecreatetruecolor(2000, 1000); ob_start(); imagepng($image); $largePng = ob_get_clean(); imagedestroy($image);
$background = new humhub\modules\orgmap\models\Asset(['title' => 'Background', 'filename' => 'background.png', 'type' => 'background']);
check($background->save(false), 'Background record missing'); humhub\modules\orgmap\services\AssetStorage::saveUpload($background, $largePng);
check(humhub\modules\orgmap\services\AssetStorage::read(humhub\modules\orgmap\services\AssetStorage::file($background, false)) === $largePng, 'Optimizer overwrote original');
check($background->preview_file_guid !== null && $background->preview_file_guid !== $background->file_guid, 'Preview was not separate');
check(getimagesizefromstring(humhub\modules\orgmap\services\AssetStorage::read(humhub\modules\orgmap\services\AssetStorage::file($background)))[0] === 1600, 'Background optimization lost its function');

// A simulated backend failure must keep legacy bytes and metadata; a retry succeeds.
$broken = new humhub\modules\orgmap\models\Asset(['title' => 'Retry original', 'filename' => 'retry.png', 'type' => 'image']);
check($broken->save(false), 'Retry asset missing');
yii\helpers\FileHelper::createDirectory($public); file_put_contents($public . '/retry.png', $png);
$oldStorageClass = $app->getModule('file')->storageManagerClass;
$app->getModule('file')->storageManagerClass = FailingOrgMapTestStore::class;
$module->legacyAssetArchivePath = '@app/orgmap-failure-archive';
try { humhub\modules\orgmap\services\LegacyAssetImport::run(); check(false, 'Backend error ignored'); } catch (RuntimeException $expected) { check(true, 'Backend error propagated'); }
check(file_get_contents($public . '/retry.png') === $png, 'Backend error destroyed original');
check(humhub\modules\orgmap\models\Asset::findOne($broken->id)->file_guid === null, 'Failed backend published a reference');
$app->getModule('file')->storageManagerClass = $oldStorageClass;
humhub\modules\orgmap\services\LegacyAssetImport::run(); $broken->refresh();
check(humhub\modules\orgmap\services\AssetStorage::read(humhub\modules\orgmap\services\AssetStorage::file($broken, false)) === $png, 'Retry did not recover original');
check(file_get_contents(humhub\modules\orgmap\services\LegacyAssetImport::archivePath() . '/retry.png') === $png, 'Retry archive missing');
$module->legacyAssetArchivePath = '@app/orgmap-legacy-assets';

// Permission revocation and deactivated identities also deny direct File access.
$manager->setGroupState(2, new humhub\modules\orgmap\permissions\ViewOrgMap(), humhub\libs\BasePermission::STATE_DENY);
check(!$file->canView($viewer), 'Permission revocation left direct image readable');
$manager->setGroupState(2, new humhub\modules\orgmap\permissions\ViewOrgMap(), humhub\libs\BasePermission::STATE_ALLOW);
$db->createCommand()->update('user', ['status' => 0], ['id' => 2])->execute();
check(!$file->canView(humhub\modules\user\models\User::findOne(2)), 'Deactivated user could view image');
$db->createCommand()->update('user', ['status' => 1], ['id' => 2])->execute();

// Disable via real version-specific core lifecycle, retaining settings, files and migration history.
$module->settings->set('moduleTitle', 'Retained title');
rawInsert('module_enabled', ['module_id' => 'orgmap']);
$beforeData = $db->createCommand('SELECT * FROM orgmap_node ORDER BY id')->queryAll();
$history = $db->createCommand('SELECT * FROM migration')->queryAll();
check($module->disable() === true, 'Disable failed');
check($db->createCommand('SELECT * FROM orgmap_node ORDER BY id')->queryAll() === $beforeData, 'Disable changed nodes');
check($db->createCommand('SELECT * FROM migration')->queryAll() === $history, 'Disable deleted migration history');
check(!$file->canView($viewer), 'Disabled module still exposes images');
check(file_get_contents($archive . '/original.png') === $png, 'Disable deleted archived original');
$app->setModule('orgmap', $module); check($module->settings->get('moduleTitle') === 'Retained title', 'Disable deleted settings');
try { $module->getMigrationService()->uninstall(); check(false, 'Destructive uninstall allowed'); } catch (LogicException $expected) { check(true, 'Uninstall guarded'); }
// Real migration entry point must scan only the new top-level migration.
ob_start(); try { check($module->getMigrationService()->migrateUp() === true, 'Core migration service failed'); } finally { ob_end_clean(); }
check($db->createCommand('SELECT * FROM orgmap_node ORDER BY id')->queryAll() === $beforeData, 'Migration service replayed destructive history');
check($db->createCommand('SELECT COUNT(*) FROM migration WHERE version="m261013_000000_preserve_and_secure_orgmap"')->queryScalar() == 1, 'New migration history missing');
check((new yii\db\Query())->from('migration')->where(['version' => array_column($history, 'version')])->orderBy('version')->all($db) === $history, 'Upgrade changed published migration history');
check($module->enable() !== false, 'Re-enable failed');
check($module->settings->get('moduleTitle') === 'Retained title', 'Re-enable lost settings');
check($db->createCommand('SELECT * FROM orgmap_node ORDER BY id')->queryAll() === $beforeData, 'Re-enable changed nodes');
check($file->canView($viewer), 'Re-enabled module did not restore authorized image access');
echo json_encode(['core' => $app->version, 'php' => PHP_VERSION, 'database' => $name, 'checks' => $checks, 'result' => 'PASS'], JSON_PRETTY_PRINT) . PHP_EOL;
