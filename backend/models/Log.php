<?php

namespace backend\models;

use Yii;

class Log extends \yii\mongodb\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function collectionName()
	{
		return ['wu-dev', 'log'];
	}

	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		return [
				'_id',
				'oldData',
				'newData',
				'userId', 
				'refId',
				'editDate',
				'action',
				'memberId',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['oldData', 'newData', 'userId','refId','editDate','action','memberId'], 'safe']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
				'_id' => 'ID',
				'oldData' => 'oldData',
				'newData' => 'newData',
				'userId' => 'userId',
				'refId' => 'refId',
				'editDate' => 'editDate',
				'action' => 'action',
				'memberId' => 'memberId',
		];
	}
}
