<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\models\Connection;
use humhub\modules\orgmap\models\Node;
use humhub\modules\orgmap\models\Organ;
use orgmap\OrgMapTestCase;

class NodeLifecycleTest extends OrgMapTestCase
{
    public function testOrganNodeAndConnectionLifecycle(): void
    {
        $organ = new Organ([
            'name' => 'Runtime test organ',
            'color' => '#8844aa',
            'sort_order' => 10,
        ]);
        $this->assertTrue(
            $organ->save(),
            'Organ could not be saved: ' . json_encode($organ->getErrors(), JSON_UNESCAPED_UNICODE)
        );

        $source = $this->createNode('Runtime source', [
            'organ_id' => $organ->id,
            'pos_x' => 120,
            'pos_y' => 180,
        ]);
        $target = $this->createNode('Runtime target', [
            'organ_id' => $organ->id,
            'pos_x' => 480,
            'pos_y' => 320,
        ]);

        $source->title = 'Runtime source updated';
        $source->pos_x = 240;
        $this->assertTrue(
            $source->save(),
            'Updated node could not be saved: ' . json_encode($source->getErrors(), JSON_UNESCAPED_UNICODE)
        );
        $this->assertSame('Runtime source updated', Node::findOne($source->id)->title);
        $this->assertSame(240, (int)Node::findOne($source->id)->pos_x);

        $connection = new Connection([
            'from_node_id' => $source->id,
            'to_node_id' => $target->id,
            'type' => 'reports_to',
            'color' => '#666666',
            'width' => 2,
            'style' => 'solid',
            'arrow' => 'end',
            'label' => 'reports to',
            'curve' => 0,
            'font_size' => 12,
            'font_weight' => 'normal',
            'label_rotation' => 'auto',
            'label_offset_x' => 0,
            'label_offset_y' => 0,
        ]);
        $this->assertTrue(
            $connection->save(),
            'Connection could not be saved: ' . json_encode($connection->getErrors(), JSON_UNESCAPED_UNICODE)
        );
        $connectionId = (int)$connection->id;

        $this->assertSame(1, $target->delete());
        $this->assertNull(Connection::findOne($connectionId), 'Connection was not removed with its target node.');

        $this->assertSame(1, $source->delete());
        $this->assertSame(1, $organ->delete());
    }
}
