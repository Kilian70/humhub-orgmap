<?php

use humhub\components\Migration;
use humhub\modules\orgmap\services\SpaceReferenceUpgrade;

/** Also repairs installations that already applied the secure-storage migration. */
class m261013_010000_preserve_space_references extends Migration
{
    public function up()
    {
        (new SpaceReferenceUpgrade(['db' => $this->db]))->apply();
    }

    public function down()
    {
        echo "OrgMap Space provenance must not be removed by restoring a SET NULL or CASCADE constraint.\n";
        return false;
    }
}
