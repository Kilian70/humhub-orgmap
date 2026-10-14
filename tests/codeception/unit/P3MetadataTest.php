<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\models\Organ;
use humhub\modules\orgmap\assets\OrgMapAsset;
use humhub\modules\orgmap\components\ActiveForm;
use orgmap\OrgMapTestCase;
use Yii;

class P3MetadataTest extends OrgMapTestCase
{
    public function testExistingCreationMetadataAndUnknownAuthorsArePreserved(): void
    {
        $originalIdentity = Yii::$app->user->identity;
        try {
            Yii::$app->user->setIdentity(\humhub\modules\user\models\User::findOne(1));
            $record = new Organ(['name' => 'Metadata', 'created_at' => 100, 'created_by' => 1, 'updated_at' => 200, 'updated_by' => 1]);
            $this->assertTrue($record->save(false)); $record->refresh();
            $this->assertSame(100, $record->created_at);
            $this->assertSame(200, $record->updated_at);
            $record->updateAttributes(['created_at' => null, 'created_by' => null]);
            $record->name = 'Actual new edit';
            $this->assertTrue($record->save(false)); $record->refresh();
            $this->assertNull($record->created_at);
            $this->assertNull($record->created_by);
            $this->assertIsInt($record->updated_at);
            $this->assertSame(1, $record->updated_by);
            $before = $record->attributes;
            $this->assertTrue($record->save(false)); $record->refresh();
            $this->assertSame($before, $record->attributes);
        } finally { Yii::$app->user->setIdentity($originalIdentity); }
    }

    public function testAssetsAndFormsKeepTheirExistingBehavior(): void
    {
        $asset = new OrgMapAsset();
        $this->assertInstanceOf(\humhub\components\assets\AssetBundle::class, $asset);
        $this->assertSame(\yii\web\View::POS_END, $asset->jsOptions['position']);
        $this->assertFalse($asset->defer);
        $this->assertSame([\humhub\assets\AppAsset::class], $asset->depends);
        ob_start();
        try {
            $form = ActiveForm::begin(['id' => 'p3-convention-test', 'action' => '/isolated-test-save']);
            ActiveForm::end();
        } finally { ob_end_clean(); }
        $this->assertInstanceOf(\humhub\widgets\form\ActiveForm::class, $form);
        $this->assertTrue($form->enableClientValidation);
    }
}
