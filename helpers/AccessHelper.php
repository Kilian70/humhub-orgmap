<?php

namespace humhub\modules\orgmap\helpers;

use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;

final class AccessHelper
{
    public static function user($user = null): ?User
    {
        if ($user instanceof User) {
            return $user;
        }
        if ($user === null) {
            return Yii::$app->user->getIdentity();
        }
        return is_scalar($user) && ctype_digit((string) $user) ? User::findOne((int) $user) : null;
    }

    public static function canView($user = null): bool
    {
        if (!Yii::$app->hasModule('orgmap')) {
            return false;
        }
        $identity = self::user($user);
        if ($identity === null) {
            return $user === null && AuthHelper::isGuestAccessEnabled()
                && (bool) Yii::$app->getModule('orgmap')->settings->get('allowGuestAccess', false);
        }
        return (int) $identity->status === User::STATUS_ENABLED
            && (new PermissionManager(['subject' => $identity]))->can(ViewOrgMap::class);
    }

    public static function canManage($user = null): bool
    {
        if (!Yii::$app->hasModule('orgmap')) {
            return false;
        }
        $identity = self::user($user);
        return $identity !== null && (int) $identity->status === User::STATUS_ENABLED
            && (new PermissionManager(['subject' => $identity]))->can(ManageOrgMap::class);
    }

    public static function visibleSpaces($user = null)
    {
        $identity = self::user($user);
        $query = Space::find()->visible($identity);
        // Core visible() allows registered-only spaces for guests; their route does not.
        if ($identity === null) {
            $query->andWhere([Space::tableName() . '.visibility' => Space::VISIBILITY_ALL]);
        }
        return $query;
    }

    public static function filterNodes(array $nodes, $user = null): array
    {
        $ids = array_unique(array_filter(array_map(static fn(Node $node) => $node->space_id, $nodes)));
        $visibleIds = $ids ? self::visibleSpaces($user)->andWhere([Space::tableName() . '.id' => $ids])->select(Space::tableName() . '.id')->column() : [];
        $allowed = array_fill_keys($visibleIds, true);
        return array_values(array_filter($nodes, static fn(Node $node) => !$node->space_id || isset($allowed[$node->space_id])));
    }

    public static function nodeQuery($user = null)
    {
        $spaces = self::visibleSpaces($user)->select(Space::tableName() . '.id');
        return Node::find()->andWhere(['or', ['space_id' => null], ['space_id' => 0], ['in', 'space_id', $spaces]]);
    }

    public static function connectionQuery()
    {
        $ids = self::nodeQuery()->select('id');
        return \humhub\modules\orgmap\models\Connection::find()
            ->andWhere(['in', 'from_node_id', $ids])->andWhere(['in', 'to_node_id', clone $ids]);
    }

    public static function assertNode(Node $node): void
    {
        if (!self::filterNodes([$node])) {
            throw new \yii\web\ForbiddenHttpException();
        }
    }

    public static function assertConnection(\humhub\modules\orgmap\models\Connection $connection): void
    {
        if ($connection->fromNode === null || $connection->toNode === null) {
            throw new \yii\web\ForbiddenHttpException();
        }
        self::assertNode($connection->fromNode);
        self::assertNode($connection->toNode);
    }
}
