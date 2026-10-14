<?php

namespace humhub\modules\orgmap\assets;

use humhub\components\assets\AssetBundle;

/** Loads the standalone tree without initializing map controls on a missing map. */
class OrgMapTreeAsset extends AssetBundle
{
    public $defer = false;
    public $jsPosition = \yii\web\View::POS_END;
    public $sourcePath = '@orgmap/resources';
    public $css = ['css/tree.css'];
    public $js = ['js/tree.js'];
    public $depends = ['humhub\assets\AppAsset'];
}
