<?php

namespace orgmap;

use humhub\modules\orgmap\models\Node;
use tests\codeception\_support\HumHubDbTestCase;

abstract class OrgMapTestCase extends HumHubDbTestCase
{
    protected function createNode(string $title, array $attributes = []): Node
    {
        $node = new Node(array_merge([
            'title' => $title,
            'type' => 'organ',
            'pos_x' => 100,
            'pos_y' => 100,
            'radius' => 80,
            'color' => '#3399cc',
            'visible' => 1,
            'sort_order' => 0,
            'display_mode' => 'color',
            'image_source' => 'none',
            'shape' => 'circle',
            'width' => 160,
            'height' => 160,
            'border_color' => '#226688',
            'border_width' => 2,
            'opacity' => 100,
            'color_opacity' => 100,
            'image_opacity' => 100,
            'font_size' => 16,
        ], $attributes));

        $this->assertTrue(
            $node->save(),
            'Node could not be saved: ' . json_encode($node->getErrors(), JSON_UNESCAPED_UNICODE)
        );
        $this->assertTrue($node->refresh());

        return $node;
    }
}
