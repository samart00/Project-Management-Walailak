<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for collection "policy".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 */
class Policy extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'policy'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
        	'policyName',
        	'defaultPolicy',
        	'description',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

}
