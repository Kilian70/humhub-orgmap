<?php

namespace humhub\modules\orgmap\helpers;

use humhub\modules\space\models\Space;

final class SpacePresentationHelper
{
    public static function imageUrl(Space $space): string
    {
        // AssetImage (including its initialization events) on 1.19; legacy API on 1.18.
        $image = method_exists($space, 'getImage') ? $space->getImage() : $space->getProfileImage();
        return $image->getUrl();
    }

    public static function aboutUrl(Space $space): string
    {
        return $space->createUrl('/space/space/about');
    }
}
