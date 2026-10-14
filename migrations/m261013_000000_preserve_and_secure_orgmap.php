<?php

use humhub\components\Migration;
use humhub\modules\orgmap\services\LegacyAssetImport;
use humhub\modules\orgmap\services\SchemaUpgrade;

/** Replaces execution of archived legacy migrations, retaining history and all rows. */
class m261013_000000_preserve_and_secure_orgmap extends Migration
{
    public function up()
    {
        \humhub\modules\orgmap\services\PreservingMigrationService::assertLayout(dirname(__DIR__));
        // MySQL DDL cannot be rolled back reliably. Every schema step is additive/idempotent.
        (new SchemaUpgrade(['db' => $this->db]))->apply();
        LegacyAssetImport::run();
    }

    public function down()
    {
        echo "OrgMap secure storage must not be downgraded by deleting data. Restore a verified backup instead.\n";
        return false;
    }
}
