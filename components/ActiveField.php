<?php

namespace humhub\modules\orgmap\components;

/** Preserve native change events used by the existing OrgMap form scripts. */
class ActiveField extends \humhub\widgets\form\ActiveField
{
    public function dropDownList($items, $options = []): self
    {
        return parent::dropDownList($items, array_merge(['data-ui-select2' => null], $options));
    }
}
