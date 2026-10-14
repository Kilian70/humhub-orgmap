<?php

namespace humhub\modules\orgmap\components;

class ActiveForm extends \humhub\widgets\form\ActiveForm
{
    // Preserve the previous Yii form's validation and native dropdown behavior.
    public $enableClientValidation = true;
    public $fieldClass = ActiveField::class;
}
