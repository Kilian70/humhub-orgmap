<?php
/** All P1/P2 checks plus metadata and real HumHub form/widget/asset checks. */
declare(strict_types=1);
ob_start();
require __DIR__ . '/P2IntegrationTest.php';
$previous = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
$start = $checks;
identity(1);
$app->request->headers->remove('X-Requested-With');
$app->response->statusCode = 200;
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/orgmap-test';
$app->setModule('web', ['class' => humhub\modules\web\Module::class, 'enableServiceWorker' => false]);
Yii::setAlias('@web', '');
Yii::setAlias('@orgmap', $moduleRoot);
Yii::setAlias('@vendor', $vendor);
Yii::setAlias('@npm', $vendor . '/npm-asset');
Yii::setAlias('@bower', $vendor . '/bower-asset');

use humhub\modules\orgmap\models\Organ;
use humhub\modules\orgmap\models\Connection;
use humhub\modules\orgmap\models\Asset;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\assets\OrgMapAsset;
use humhub\modules\orgmap\assets\OrgMapAdminAsset;

$left = node('P3 source'); $right = node('P3 target');
$records = [
    new Organ(['name' => 'Metadata organ']),
    new Connection(['from_node_id' => $left->id, 'to_node_id' => $right->id]),
    new Asset(['title' => 'Metadata asset', 'filename' => 'p3.png', 'type' => 'image']),
];
foreach ($records as $record) {
    $earliest = time();
    check($record instanceof humhub\components\ActiveRecord, 'Model lacks HumHub base');
    check($record->save(false), 'Metadata insertion failed');
    $record->refresh();
    check(is_int($record->created_at) && $record->created_at >= $earliest && $record->created_at <= time(), 'Integer creation timestamp not set');
    check((int) $record->created_by === 1, 'Current creator not recorded');
    $old = $record->attributes;
    check($record->save(false), 'No-op save failed'); $record->refresh();
    check($record->attributes === $old, 'No-op invented historical metadata');

    $fixture = ['created_at' => 1234567890, 'created_by' => 3];
    if ($record->hasAttribute('updated_at')) { $fixture += ['updated_at' => 1234567900, 'updated_by' => 3]; }
    $record->updateAttributes($fixture); $record->refresh();
    identity(2);
    $attribute = $record instanceof Organ ? 'name' : ($record instanceof Connection ? 'label' : 'title');
    $record->$attribute = 'Edited by viewer in isolated fixture';
    check($record->save(false), 'Historical update failed'); $record->refresh();
    check((int) $record->created_at === 1234567890 && (int) $record->created_by === 3, 'Historical creation changed');
    if ($record->hasAttribute('updated_at')) {
        check(is_int($record->updated_at) && $record->updated_at >= $earliest, 'Integer update timestamp not set');
        check((int) $record->updated_by === 2, 'Actual editor missing');
    }
    $record->updateAttributes(['created_at' => null, 'created_by' => null]);
    $record->$attribute = 'Unknown historical creator stays unknown';
    check($record->save(false), 'Unknown creator update failed'); $record->refresh();
    check($record->created_at === null && $record->created_by === null, 'Unknown historical creation was fabricated');
    if ($record->hasAttribute('updated_by')) {
        $app->user->switchIdentity(null);
        $record->$attribute = 'Automated isolated update';
        check($record->save(false), 'Automated update failed'); $record->refresh();
        check($record->updated_by === null, 'Automated update falsely attributed to previous editor');
    }
    identity(1);
    $record->$attribute = 'Vetoed change';
    $saved = $record->attributes;
    $record->on($record::EVENT_BEFORE_UPDATE, static function ($event): void { $event->isValid = false; });
    check($record->save(false) === false, 'Core beforeSave veto ignored');
    foreach (['created_at', 'created_by', 'updated_at', 'updated_by'] as $key) {
        if ($record->hasAttribute($key)) { check($record->$key === $saved[$key], 'Veto changed metadata in memory'); }
    }
    $record->off($record::EVENT_BEFORE_UPDATE);
}

$imported = new Organ(['name' => 'Explicit import', 'created_at' => 100, 'created_by' => 3, 'updated_at' => 200, 'updated_by' => 2]);
check($imported->save(false), 'Explicit imported record failed'); $imported->refresh();
check($imported->created_at === 100 && $imported->created_by === 3 && $imported->updated_at === 200 && $imported->updated_by === 2, 'Explicit imported metadata overwritten');
check($imported->getCreatedBy()->one()->id == 3 && $imported->getUpdatedBy()->one()->id == 2, 'Core author relations unavailable');
$importedAttributes = $imported->attributes;
identity(2);
check($imported->save(false), 'Imported no-op failed'); $imported->refresh();
check($imported->attributes === $importedAttributes, 'No-op replaced historical import timestamp');
identity(1);
$left->connectionIds = [$right->id];
$copy = unserialize(serialize($left));
check($copy->attributes === $left->attributes && $copy->getOldAttributes() === $left->getOldAttributes(), 'Core serialization changed node attributes');
check($copy->connectionIds === $left->connectionIds, 'Core serialization lost connection selection');
check(!$left->hasAttribute('created_at'), 'Node audit columns introduced without migration');
$extraRule = new Node(['title' => 'Extended rules']);
$extraRule->on(humhub\components\ActiveRecord::EVENT_APPEND_RULES, static function ($event): void { $event->result = [[['title'], 'string', 'max' => 3]]; });
check(!$extraRule->validate(['title']), 'HumHub append-rules event unavailable');

// Real renderer / publisher, writing only into the isolated file root.
$app->assetManager->basePath = $testRoot . '/assets'; $app->assetManager->baseUrl = '/assets';
$view = $app->view;
$mapAsset = new OrgMapAsset(); $adminAsset = new OrgMapAdminAsset();
foreach ([$mapAsset, $adminAsset] as $bundle) {
    check($bundle instanceof humhub\components\assets\AssetBundle, 'Asset lacks HumHub base');
    check($bundle->jsOptions['position'] === yii\web\View::POS_END && empty($bundle->jsOptions['defer']) && empty($bundle->jsOptions['async']), 'Asset loading order changed');
    check($bundle->depends === [humhub\assets\AppAsset::class], 'Asset dependency changed');
    $bundle->publish($app->assetManager);
    foreach ($bundle->js as $file) {
        $path = $bundle->basePath . '/' . $file;
        $exists = method_exists($app->assetManager, 'fileExists') ? $app->assetManager->fileExists($path) : is_file($path);
        check($exists, 'Published JavaScript missing: ' . $file);
    }
}
check($mapAsset->js === (new OrgMapAsset())->js && array_search('js/persistence.js', $mapAsset->js, true) < array_search('js/dragging.js', $mapAsset->js, true), 'Persistence script order changed');
check($adminAsset->js === [], 'Administration unexpectedly loads custom scripts');

$app->controller = new humhub\modules\orgmap\controllers\AdminController('admin', $module);
$app->request->enableCsrfValidation = true;
ob_start();
$view->beginPage();
ob_start();
$form = humhub\modules\orgmap\components\ActiveForm::begin(['id' => 'orgmap-p3-form', 'action' => '/synthetic-save']);
echo $form->field($left, 'title')->textInput();
echo $form->field($left, 'type')->dropDownList(Node::getShapeOptions());
echo $form->field($left, 'visible')->checkbox();
humhub\modules\orgmap\components\ActiveForm::end();
$html = ob_get_clean();
check($form instanceof humhub\widgets\form\ActiveForm && $form->enableClientValidation === true, 'Form convention changed validation');
check(str_contains($html, 'name="Node[title]"') && str_contains($html, 'id="node-title"'), 'Form name/id changed');
check(str_contains($html, 'name="Node[visible]"') && str_contains($html, 'type="checkbox"'), 'Boolean form input lost');
check(!str_contains($html, 'data-ui-select2'), 'Native dropdown unexpectedly initialized Select2');
check(str_contains($html, 'name="_csrf"'), 'Form CSRF field missing');
check(str_contains(implode('', $view->js[yii\web\View::POS_READY] ?? []), 'yiiActiveForm'), 'Client validation initialization lost');
$eventSeen = false;
yii\base\Event::on(humhub\modules\orgmap\widgets\IconPickerWidget::class, humhub\components\Widget::EVENT_CREATE, static function () use (&$eventSeen): void { $eventSeen = true; });
$widget = humhub\modules\orgmap\widgets\IconPickerWidget::widget(['model' => $left]);
check($eventSeen && str_contains($widget, 'name="Node[icon_class]"'), 'Core widget hook or icon input lost');
yii\base\Event::off(humhub\modules\orgmap\widgets\IconPickerWidget::class, humhub\components\Widget::EVENT_CREATE);
check(humhub\modules\orgmap\widgets\IconPickerWidget::widget(['render' => false]) === '', 'Core widget suppression unsupported');
$view->endPage(); ob_end_clean();

echo json_encode(['core' => $app->version, 'php' => PHP_VERSION, 'database' => $name, 'p1_checks' => $previous['p1_checks'], 'p2_checks' => $previous['p2_checks'], 'p3_checks' => $checks - $start, 'checks' => $checks, 'result' => 'PASS'], JSON_PRETTY_PRINT) . PHP_EOL;
