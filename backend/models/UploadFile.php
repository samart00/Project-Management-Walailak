<?php
namespace backend\models;

use yii\mongodb\file\ActiveRecord;

/**
 * Class Asset
 * @package common\models
 * @property string $_id MongoId
 * @property array $filename
 * @property string $uploadDate
 * @property string $length
 * @property string $chunkSize
 * @property string $md5
 * @property array $file
 * @property string $newFileContent
 * Must be application/pdf, image/png, image/gif etc...
 * @property string $contentType
 * @property string $description
 */

class UploadFile extends \yii\mongodb\ActiveRecord
{
	/**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'file'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
    	return[
    			[['description', 'contentType'], 'required'],
    	];
    }
    
    public function attributes()
    {
    	return 
    	['_id','filename','file','contentType', 'description','contentType'];
    			
    }
}
?>