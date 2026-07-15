<?php

namespace humhub\modules\orgmap\assets;

use yii\web\AssetBundle;

class OrgMapAsset extends AssetBundle
{

    public $sourcePath = '@orgmap/resources';

	public $css = [
		'css/orgmap.css',
		'css/tree.css',
		'css/nodes.css',
	];

    public $js = [

        // Core
        'js/core.js',

		// Main
		'js/icon-picker.js',
		'js/orgmap.js',
		'js/tree.js',

        // Camera
        'js/zoom.js',
        'js/panning.js',

        // Interaction
        'js/dragging.js',
        'js/resize.js',
        'js/interaction.js',

        // Rendering
        'js/svg.js',
    ];

    public $depends = [
        'humhub\assets\AppAsset',
    ];
}