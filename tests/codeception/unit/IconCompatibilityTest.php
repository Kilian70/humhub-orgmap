<?php

declare(strict_types=1);

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\widgets\IconPickerWidget;
use humhub\modules\ui\icon\widgets\Icon;
use orgmap\OrgMapTestCase;

final class IconCompatibilityTest extends OrgMapTestCase
{
    public function testUsesHumHubIconAbstractions(): void
    {
        $moduleRoot = dirname(__DIR__, 3);
        $files = [
            'Events.php',
            'helpers/ActionButtonHelper.php',
            'resources/js/orgmap.js',
            'views/map/index.php',
            'views/map/tree.php',
            'widgets/views/icon-picker.php',
        ];

        foreach ($files as $file) {
            $source = file_get_contents($moduleRoot . '/' . $file);
            $this->assertIsString($source, $file);
            $this->assertDoesNotMatchRegularExpression('/class=["\'][^"\']*\bfa\b/', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/\bfa-(?:expand|compress)\b/', $source, $file);
        }

        $this->assertFileDoesNotExist($moduleRoot . '/helpers/IconPickerHelper.php');
        $this->assertFileDoesNotExist($moduleRoot . '/resources/js/icon-picker.js');
    }

    public function testCorePickerAndLegacyStoredIconRender(): void
    {
        $model = new Node(['title' => 'Icon', 'icon_class' => 'fa-home']);
        $picker = IconPickerWidget::widget(['model' => $model]);

        $this->assertStringContainsString('name="Node[icon_class]"', $picker);
        $this->assertNotEmpty(Icon::getNames());
        $this->assertNotSame('', trim((string) Icon::get($model->icon_class)));
    }
}
