<?php

namespace humhub\modules\orgmap\permissions;

use Yii;
use humhub\libs\BasePermission;

class ManageOrgMap extends BasePermission
{

    protected $id = 'manageOrgMap';

    protected $moduleId = 'orgmap';

    protected $title;

    protected $description;

    public function __construct()
    {
        $this->title = Yii::t(
            'OrgmapModule.base',
            'ORG verwalten'
        );

        $this->description = Yii::t(
            'OrgmapModule.base',
            'Erlaubt das Bearbeiten des Organigramms.'
        );

        parent::__construct();
    }
}