<?php

use yii\db\Migration;

class m260816_090000_split_node_opacity extends Migration
{
	public function safeUp()
	{
		$this->addColumn(
			'orgmap_node',
			'color_opacity',
			$this->integer()->null()->after('opacity')
		);

		$this->addColumn(
			'orgmap_node',
			'image_opacity',
			$this->integer()->null()->after('color_opacity')
		);

		$this->update(
			'orgmap_node',
			[
				'color_opacity' => new \yii\db\Expression('COALESCE([[opacity]], 100)'),
				// Im bisherigen Mischmodus beeinflusste opacity nur die Farbe;
				// das Bild wurde immer vollständig sichtbar dargestellt.
				'image_opacity' => new \yii\db\Expression(
					"CASE WHEN [[display_mode]] = 'mixed' THEN 100 ELSE COALESCE([[opacity]], 100) END"
				),
			]
		);

		$this->alterColumn(
			'orgmap_node',
			'color_opacity',
			$this->integer()->notNull()->defaultValue(100)
		);

		$this->alterColumn(
			'orgmap_node',
			'image_opacity',
			$this->integer()->notNull()->defaultValue(100)
		);
	}

	public function safeDown()
	{
		$this->dropColumn('orgmap_node', 'image_opacity');
		$this->dropColumn('orgmap_node', 'color_opacity');
	}
}
