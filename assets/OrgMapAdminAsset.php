<?php

namespace humhub\modules\orgmap\assets;

use yii\web\AssetBundle;

/**
 * Schlankes Asset-Paket für die OrgMap-Verwaltung.
 *
 * Die Verwaltungsseiten benötigen die Modulgestaltung und den Icon-Picker,
 * aber keine Karten-, Kamera- oder Interaktionsskripte. Würde dort das
 * vollständige Kartenpaket geladen, markiert HumHub es bei einem späteren
 * PJAX-Wechsel bereits als vorhanden, obwohl es ohne Karte initialisiert
 * wurde.
 */
class OrgMapAdminAsset extends AssetBundle
{
    public $sourcePath = '@orgmap/resources';

    public $css = [
        'css/orgmap.css',
        'css/tree.css',
        'css/nodes.css',
    ];

    public $js = [
        'js/icon-picker.js',
    ];

    public $depends = [
        'humhub\assets\AppAsset',
    ];
}
