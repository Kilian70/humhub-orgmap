<?php

namespace humhub\modules\orgmap\commands;

use humhub\modules\orgmap\services\LegacyMigrationArchive;

/** File-only preflight for global CLI migration commands that skip module bootstrap. */
class MigrationController extends \yii\console\Controller
{
    public function actionPrepare(): int
    {
        LegacyMigrationArchive::prepare(dirname(__DIR__));
        $this->stdout("OrgMap historical migration files verified and archived. No database migrations executed.\n");
        return 0;
    }
}
