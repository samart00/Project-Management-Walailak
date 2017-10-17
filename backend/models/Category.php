<?php

namespace backend\models;

use Yii;
use common\libs\Permission;

/**
 * This is the model class for collection "category".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $categoryName
 * @property mixed $description
 * @property mixed $status
 * @property mixed $activeFlag
 */
class Category extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'category'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'categoryName',
            'description',
        	'activeFlag',
        	'createDate',
        	'createBy'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categoryName', 'description', 'activeFlag', 'createDate', 'createBy'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'categoryName' => 'CategoryName',
            'description' => 'Description',
        	'activeFlag' => 'ActiveFlag'
        ];
    }
    
 	public function findAllCategoryByActiveFlag($activeFlag){
    	$conditions = [];
    	$query = Category::find();
    	
    	if(!empty($activeFlag)){
    		$conditions['activeFlag'] = $activeFlag;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$listCategory = $query->all();
    	return $listCategory;
    }
   
	public function findCategoryById($id){
		$categoryData = Category::findOne($id);
		return $categoryData;
	}
	
	public function findCategoryByNameAndActiveFlag($categoryName, $activeFlag){
		$conditions = [];
		$query = Category::find();
		
		if(!empty($activeFlag)){
			$conditions['activeFlag'] = (int)$activeFlag;
		}
		
		if(!empty($conditions)){
			$query->where($conditions);
		}
		
		if(!empty($categoryName)){
			$query->andWhere(['like', "categoryName", $categoryName]);
		}
		
		$listCategory = $query->all();
		return $listCategory;
	}
	
	public function getCategoryName($categoryId){
		$model = Category::findOne($categoryId);
		return $model->categoryName;
	}
}
