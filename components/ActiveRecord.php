<?php

namespace humhub\modules\orgmap\components;

use Yii;

/** HumHub facilities with metadata adapted to the published OrgMap integer schema. */
abstract class ActiveRecord extends \humhub\components\ActiveRecord
{
    private const METADATA = ['created_at', 'created_by', 'updated_at', 'updated_by'];

    public function beforeSave($insert)
    {
        $attributes = array_values(array_filter(self::METADATA, fn($name) => $this->hasAttribute($name)));
        $original = $this->getAttributes($attributes);
        if (!parent::beforeSave($insert)) {
            $this->setAttributes($original, false);
            return false;
        }

        $changed = array_diff_key($this->getDirtyAttributes(), array_flip(self::METADATA));
        if (!$insert && !$changed) {
            foreach ($attributes as $name) { $this->$name = $this->getOldAttribute($name); }
            return true;
        }
        foreach ($attributes as $name) {
            if (!$insert && in_array($name, ['created_at', 'created_by'], true)) {
                // Never attribute an old record's unknown creation to its current editor.
                $this->$name = $this->getOldAttribute($name);
            } elseif ($insert && $original[$name] !== null) {
                // Explicit metadata on imported/new records is not a backfill target.
                $this->$name = $original[$name];
            } elseif (in_array($name, ['created_at', 'updated_at'], true)) {
                $this->$name = static::getTableSchema()->columns[$name]->phpType === 'integer'
                    ? time() : date('Y-m-d H:i:s');
            } elseif ($name === 'updated_by') {
                // A background update must not retain the previous editor as its author.
                $this->$name = Yii::$app->has('user') ? Yii::$app->user->id : null;
            }
        }
        return true;
    }
}
