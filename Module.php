<?php

namespace humhub\modules\orgmap;

use humhub\modules\orgmap\permissions\ManageOrgMap;
use humhub\modules\orgmap\permissions\ViewOrgMap;

class Module extends \humhub\components\Module
{

    public $controllerNamespace = 'humhub\modules\orgmap\controllers';

    public function getName()
    {
        return 'OrgMap';
    }

	public function getDescription()
	{
		return 'Visual organization map.';
	}
	
	/*
	--------------------------------------------------
	Permissions
	--------------------------------------------------
	*/
	
	public function getPermissions(
		$contentContainer = null
	): array
	{
			return [
			
				new ViewOrgMap(),
			
				new ManageOrgMap(),
			
			];
	}
	}