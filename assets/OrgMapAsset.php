<?php

namespace humhub\modules\orgmap\assets;

use humhub\components\assets\AssetBundle;

class OrgMapAsset extends AssetBundle
{

    // Keep existing synchronous POS_END loading after AppAsset and page markup.
    public $defer = false;
    public $jsPosition = \yii\web\View::POS_END;

    public $sourcePath = '@orgmap/resources';

	public $css = [
		'css/orgmap.css',
		'css/tree.css',
		'css/nodes.css',
	];

    public $js = [

        // Core
        'js/core.js',
        'js/persistence.js',

		// SVG-Geometrie muss vor Kamera und Interaktionen verfügbar sein
		'js/svg.js',

		// Main
		'js/orgmap.js',
		'js/tree.js',

        // Camera
        'js/zoom.js',
        'js/panning.js',

        // Interaction
        'js/dragging.js',
        'js/resize.js',
        'js/interaction.js',
    ];

    public $depends = [
        'humhub\assets\AppAsset',
    ];
}
