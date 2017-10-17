<?php

namespace common\models;

use Yii;

class Employee extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'employee'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'empCode',
            'createTime',
        	'createBy',
        	'status',
        	'nameTh',
        	'sernameTh',
        	'nameEn',
        	'sernamemEn',
        	'sex',
        	'email',
        	'positionId',
        	'positionName',
        	'divCode',
        	'divName',
        	'depCode',
        	'depName',
        	'sectionName',
        	'companyCode',
        	'companyName',
        	'officePhone',
        	'birthday',
        	'beginDate',
        	'resignDate',
        	'lastUpdateTime',
        	'lastUpdateBy'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['empCode',
            'createTime',
        	'createBy',
        	'status',
        	'nameTh',
        	'sernameTh',
        	'nameEn',
        	'sernamemEn',
        	'sex',
        	'email',
        	'positionId',
        	'positionName',
        	'divCode',
        	'divName',
        	'depCode',
        	'depName',
        	'sectionName',
        	'companyCode',
        	'companyName',
        	'officePhone',
        	'birthday',
        	'beginDate',
        	'resignDate',
        	'lastUpdateTime',
        	'lastUpdateBy'], 'safe']
        ];
    }

}