<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\helpers\BackgroundImageHelper;
use humhub\modules\orgmap\helpers\SpacePresentationHelper;
use humhub\modules\orgmap\models\Node;
use humhub\modules\space\models\Space;
use orgmap\OrgMapTestCase;
use Yii;

class P2CompatibilityTest extends OrgMapTestCase
{
    public function testDimensionsAndIconsMatchStorageLimits(): void
    {
        foreach ([20, 5001, 10000] as $size) {
            $node = new Node(['title' => 'Boundary', 'width' => $size, 'height' => $size]);
            $this->assertTrue($node->validate(['width', 'height']));
        }
        $node = new Node(['title' => 'Invalid', 'width' => 10001, 'icon_class' => str_repeat('x', 101)]);
        $this->assertFalse($node->validate(['width', 'icon_class']));
        $this->assertTrue($node->hasErrors('width'));
        $this->assertTrue($node->hasErrors('icon_class'));
        $stored = $this->createNode('Historical dimension');
        $stored->updateAttributes(['width' => 12000]);
        $this->assertTrue($stored->validate(['width']));
        $stored->title = 'Still editable';
        $this->assertTrue($stored->save());
        $this->assertSame(12000, (int) Node::findOne($stored->id)->width);
    }

    public function testAboutLinksAndImagesUseCoreApis(): void
    {
        $space = Space::find()->one();
        $this->assertNotNull($space);
        $original = Yii::$app->urlManager->enablePrettyUrl;
        try {
            foreach ([false, true] as $pretty) {
                Yii::$app->urlManager->enablePrettyUrl = $pretty;
                $url = SpacePresentationHelper::aboutUrl($space);
                $this->assertSame($space->createUrl('/space/space/about'), $url);
                if (!$pretty) {
                    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                    $this->assertSame('space/space/about', $query['r']);
                    $this->assertSame($space->guid, $query['cguid']);
                }
            }
            $image = method_exists($space, 'getImage') ? $space->getImage() : $space->getProfileImage();
            $this->assertSame($image->getUrl(), SpacePresentationHelper::imageUrl($space));
        } finally { Yii::$app->urlManager->enablePrettyUrl = $original; }
    }

    public function testOptionalPreviewHonorsMemoryBudget(): void
    {
        $this->assertFalse(BackgroundImageHelper::fitsMemoryBudget(10000, 4000, 1600, 640, 128 * 1048576, 16 * 1048576));
        $this->assertTrue(BackgroundImageHelper::fitsMemoryBudget(2000, 1000, 1600, 800, 256 * 1048576, 16 * 1048576));
    }
}
