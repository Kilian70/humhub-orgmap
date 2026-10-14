<?php
/** All prior checks, plus Space reference repair with both table naming strategies. */
declare(strict_types=1);
ob_start();
require __DIR__ . '/P3IntegrationTest.php';
$previous = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
$start = $checks;
require_once $moduleRoot . '/migrations/m261013_010000_preserve_space_references.php';
$scopeController = new class('map', $app->getModule('orgmap')) extends humhub\modules\orgmap\controllers\MapController {
    public function render($view, $params = []) { return $params; }
};

foreach (['plain', 'prefixed', 'legacy-prefix'] as $strategy) {
    dropModuleTables();
    $db->tablePrefix = $strategy === 'prefixed' ? 'hh_' : '';
    schemaUpgrade();
    if ($strategy === 'legacy-prefix') { $db->tablePrefix = 'hh_'; }
    $table = humhub\modules\orgmap\models\Node::tableName();
    check($table === ($strategy === 'prefixed' ? 'hh_orgmap_node' : 'orgmap_node'), 'Reference repair selected the wrong table');
    $schema = $db->schema->getTableSchema($table, true);
    check(!array_filter($schema->foreignKeys, static fn($key) => isset($key['space_id'])), 'Fresh schema still loses Space provenance');
    identity(1);
    $asset = new humhub\modules\orgmap\models\Asset(['title' => 'Retained original', 'filename' => 'retained.png', 'type' => 'image']);
    check($asset->save(false), 'Asset fixture failed');
    humhub\modules\orgmap\services\AssetStorage::saveUpload($asset, $png);
    $privateNode = node('Private provenance', ['space_id' => 3, 'asset_id' => $asset->id, 'image_source' => 'asset', 'show_in_tree' => 1, 'label_x' => -50, 'label_y' => 83]);
    check($privateNode->refresh(), 'Persisted baseline could not be loaded');
    $attributes = $privateNode->attributes;
    $file = humhub\modules\orgmap\services\AssetStorage::file($asset, false);
    foreach (['SET NULL', 'CASCADE', 'RESTRICT'] as $action) {
        $repair = new humhub\modules\orgmap\services\SpaceReferenceUpgrade(['db' => $db]);
        ob_start();
        try {
            $repair->addForeignKey('fixture-space-key', $table, 'space_id', 'space', 'id', $action, 'CASCADE');
            $beforeKeys = $db->schema->getTableSchema($table, true)->foreignKeys;
            $oldHistory = $db->createCommand('SELECT * FROM migration ORDER BY version')->queryAll();
            (new m261013_010000_preserve_space_references(['db' => $db]))->up();
            $repair->apply();
        } finally { ob_end_clean(); }
        $afterKeys = $db->schema->getTableSchema($table, true)->foreignKeys;
        unset($beforeKeys['fixture-space-key']);
        check($afterKeys === $beforeKeys, 'Reference repair changed an unrelated foreign key');
        check($db->createCommand('SELECT * FROM migration ORDER BY version')->queryAll() === $oldHistory, 'Repair rewrote migration history');
        check($privateNode->refresh() && $privateNode->attributes === $attributes, 'Repair modified node data');
        $indexes = $db->createCommand('SHOW INDEX FROM ' . $db->quoteTableName($table))->queryAll();
        check((bool) array_filter($indexes, static fn($index) => $index['Column_name'] === 'space_id' && (int) $index['Seq_in_index'] === 1), 'Repair lost the Space index');
        identity(2);
        check(!$file->canView(Yii::$app->user->identity), 'Private original was readable before Space deletion');
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->delete('space', ['id' => 3])->execute();
            check($privateNode->refresh() && $privateNode->attributes === $attributes, 'Space deletion lost provenance or node values');
            check(!humhub\modules\orgmap\helpers\AccessHelper::nodeQuery()->andWhere(['id' => $privateNode->id])->exists(), 'Deleted Space node became publicly selectable');
            check(!$asset->canView() && !$file->canView(Yii::$app->user->identity), 'Deleted Space original became readable');
            $map = $scopeController->actionIndex(); $tree = $scopeController->actionTree();
            check(!in_array($privateNode->id, array_map(static fn($node) => $node->id, $map['nodes'])), 'Map exposed an orphaned Space node');
            check(!in_array($privateNode->id, array_map(static fn($node) => $node->id, $tree['treeNodes'])), 'Tree exposed an orphaned Space node');
            check(humhub\modules\orgmap\services\AssetStorage::read($file) === $png, 'Space deletion changed original bytes');
        } finally { $transaction->rollBack(); }
    }
}
$db->tablePrefix = '';
echo json_encode(['core' => $app->version, 'php' => PHP_VERSION, 'previous_checks' => $previous['checks'], 'space_reference_checks' => $checks - $start, 'checks' => $checks, 'result' => 'PASS'], JSON_PRETTY_PRINT) . PHP_EOL;
