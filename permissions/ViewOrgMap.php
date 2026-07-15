<?php

namespace humhub\modules\orgmap\permissions;

use Yii;
use humhub\libs\BasePermission;

	class ViewOrgMap extends BasePermission
	{
	
		protected $id = 'viewOrgMap';
	
		protected $moduleId = 'orgmap';
	
		protected $title;
	
		protected $description;
    

    public function __construct()
    {
        $this->title = Yii::t(
            'OrgmapModule.base',
            'ORG anzeigen'
        );

        $this->description = Yii::t(
            'OrgmapModule.base',
            'Erlaubt das Anzeigen des Organigramms.'
        );

        parent::__construct();
    }
}