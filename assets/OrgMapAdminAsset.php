<?php

namespace humhub\modules\orgmap\assets;

use humhub\components\assets\AssetBundle;

/**
 * Schlankes Asset-Paket für die OrgMap-Verwaltung.
 *
 * Die Verwaltungsseiten benötigen die Modulgestaltung und HumHubs Icon-Picker,
 * aber keine Karten-, Kamera- oder Interaktionsskripte. Würde dort das
 * vollständige Kartenpaket geladen, markiert HumHub es bei einem späteren
 * PJAX-Wechsel bereits als vorhanden, obwohl es ohne Karte initialisiert
 * wurde.
 */
class OrgMapAdminAsset extends AssetBundle
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

    public $js = [];

    public $depends = [
        'humhub\assets\AppAsset',
    ];
}
