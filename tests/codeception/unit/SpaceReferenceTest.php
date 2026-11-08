<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\helpers\AccessHelper;
use humhub\modules\orgmap\models\Asset;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\services\AssetStorage;
use humhub\modules\orgmap\services\SpaceReferenceUpgrade;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;
use orgmap\OrgMapTestCase;
use Yii;

class SpaceReferenceTest extends OrgMapTestCase
{
    /** @dataProvider oldConstraintProvider */
    public function testCoreSpaceDeletionPreservesPrivateNodesAndFiles(?string $oldAction): void
    {
        $db = Yii::$app->db;
        $database = (string) $db->createCommand('SELECT DATABASE()')->queryScalar();
        if ($database !== 'humhub_test') {
            $explicitDatabase = getenv('ORGMAP_ISOLATED_CODECEPTION_DB')
                ?: getenv('HUMHUB_TEST_DB_NAME');
            $this->assertMatchesRegularExpression(
                '/^(orgmap_test_codeception_(118|119)_[a-f0-9]{8}|humhub_auto_(118|119))$/',
                $database
            );
            $this->assertSame($explicitDatabase, $database);
        }
        $identity = Yii::$app->user->identity;
        try {
            Yii::$app->user->setIdentity(User::findOne(1));
            $manager = new PermissionManager();
            $editors = new Group(['name' => 'Isolated OrgMap editor', 'is_admin_group' => 0]);
            $this->assertTrue($editors->save());
            $db->createCommand()->insert('group_user', ['group_id' => $editors->id, 'user_id' => 1])->execute();
            $manager->setGroupState($editors->id, new ManageOrgMap(), \humhub\libs\BasePermission::STATE_ALLOW);
            $manager->setGroupState($editors->id, new ViewOrgMap(), \humhub\libs\BasePermission::STATE_ALLOW);
            $this->assertTrue(AccessHelper::canManage());
            $space = new Space(['name' => 'Isolated OrgMap privacy', 'visibility' => Space::VISIBILITY_NONE, 'created_by' => 1]);
            $this->assertTrue($space->save(), json_encode($space->errors));
            $spaceId = (int) $space->id;
            $asset = new Asset(['title' => 'Retained private original', 'filename' => 'space-reference.png', 'type' => 'image']);
            $this->assertTrue($asset->save(false)); // Fixture bytes are supplied directly below.
            $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
            AssetStorage::saveUpload($asset, $bytes);
            $node = $this->createNode('Retained private node', ['space_id' => $spaceId, 'asset_id' => $asset->id, 'image_source' => 'asset']);
            $before = $node->attributes;
            $file = AssetStorage::file($asset, false);
            $reader = User::findOne(2);
            $groups = $db->createCommand('SELECT group_id FROM group_user WHERE user_id = :id', [':id' => $reader->id])->queryColumn();
            $this->assertNotEmpty($groups);
            foreach ($groups as $group) {
                $manager->setGroupState($group, new ViewOrgMap(), \humhub\libs\BasePermission::STATE_ALLOW);
                $manager->setGroupState($group, new ManageOrgMap(), \humhub\libs\BasePermission::STATE_DENY);
            }
            $this->assertTrue(AccessHelper::canView($reader));
            $this->assertFalse(AccessHelper::canManage($reader));
            $this->assertFalse(AccessHelper::nodeQuery($reader)->andWhere(['id' => $node->id])->exists());
            $this->assertFalse($file->canView($reader));
            $upgrade = new SpaceReferenceUpgrade(['db' => $db]);
            ob_start();
            try {
                $upgrade->apply();
                if ($oldAction !== null) {
                    $upgrade->addForeignKey('fixture-old-space-reference', Node::tableName(), 'space_id', Space::tableName(), 'id', $oldAction, 'CASCADE');
                    require_once dirname(__DIR__, 3) . '/migrations/m261013_010000_preserve_space_references.php';
                    $this->assertNotFalse((new \m261013_010000_preserve_space_references(['db' => $db]))->up());
                }
                $upgrade->apply(); // A retry must not alter data or other constraints.
            } finally { ob_end_clean(); }
            $this->assertTrue($node->refresh());
            $this->assertSame($before, $node->attributes);
            $indexes = $db->createCommand('SHOW INDEX FROM ' . $db->quoteTableName(Node::tableName()))->queryAll();
            $this->assertNotEmpty(array_filter($indexes, static fn($index) => $index['Column_name'] === 'space_id' && (int) $index['Seq_in_index'] === 1));
            $this->assertSame(1, $space->delete(), 'The actual Core Space deletion must remain usable.');
            $this->assertNull(Space::findOne($spaceId));
            $this->assertTrue($node->refresh());
            $this->assertSame($before, $node->attributes);
            $this->assertSame($spaceId, (int) $node->space_id);
            $this->assertNotNull(Asset::findOne($asset->id));
            $this->assertSame($bytes, AssetStorage::read($file));
            $this->assertFalse(AccessHelper::nodeQuery($reader)->andWhere(['id' => $node->id])->exists());
            $this->assertFalse($asset->canView($reader));
            $this->assertFalse($file->canView($reader));
        } finally { Yii::$app->user->setIdentity($identity); }
    }

    public static function oldConstraintProvider(): array
    {
        return [['SET NULL'], ['CASCADE'], ['RESTRICT'], [null]];
    }
}
