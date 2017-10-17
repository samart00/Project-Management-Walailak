<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;
use yii\web\IdentityInterface;
use MongoDB\BSON\ObjectID;
use \yii\web\UploadedFile;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property integer $amountofproject
 */
class User extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
	const STATUS_ACTIVE = 10;
	
	const LIMIT = 1;
	const UNLIMIT = 2;

	const ROLE_USER = 'user';
	const ROLE_ADMIN = 'admin';
	
	const UPLOAD_FOLDER ='uploads';
	
	const SUPER_ADMIN = 'Super Admin';

	/**
	 * @inheritdoc
	 */
	public static function CollectionName()
	{
 		return ['wu-dev','user'];
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
				'timestamp' => [
						'class' => 'yii\behaviors\TimestampBehavior',
						'attributes' => [
								ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
								ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
						]
				]
		];
	}

	public function attributes(){
		return [
				'_id',
				'username',
				'password_hash',
				'password_reset_token',
				'auth_key',
				'role',
				'status',
				'created_at',
				'updated_at',
				'amountofproject',
				'limit',
				'avatar',
				
				'empCode',
				'createTime',
				'createBy',
				'nameTh',
				'sernameTh',
				'nameEn',
				'sernamemEn',
				'sex',
				'email',
				'sendemail',
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
				['status', 'default', 'value' => self::STATUS_ACTIVE],
				['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
				['role', 'default', 'value' => self::ROLE_USER],
				['role', 'in', 'range' => [self::ROLE_USER, self::ROLE_ADMIN]],
				[['avatar'], 'file',
					'skipOnEmpty' => true,
					'extensions' => 'png,jpg',
					'maxSize' => 512000, 'tooBig' => 'ขนาดรูปภาพต้องไม่เกิน 500KB'
				],
		];
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentity($id)
	{
		return static::findOne($id);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return static|null
	 */
	public static function findByUsername($username)
	{
		return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token)
	{
		if (!static::isPasswordResetTokenValid($token)) {
			return null;
		}

		return static::findOne([
				'password_reset_token' => $token,
				'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param string $token password reset token
	 * @return bool
	 */
	public static function isPasswordResetTokenValid($token)
	{
		if (empty($token)) {
			return false;
		}
		$expire = Yii::$app->params['user.passwordResetTokenExpire'];
		$parts = explode('_',$token);
		$timestamp = (int) substr($token, strrpos($token, '_') + 1);

		return $timestamp + $expire >= time();
	}

	/**
	 * @inheritdoc
	 */
	public function getId()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey()
	{
		return $this->auth_key;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey)
	{
		return $this->getAuthKey() === $authKey;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		return Yii::$app->security->validatePassword($password, $this->password_hash);
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password_hash = Yii::$app->security->generatePasswordHash($password);
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey()
	{
		$this->auth_key = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates new password reset token
	 */
	public function generatePasswordResetToken()
	{
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken()
	{
		$this->password_reset_token = null;
	}

	public function findAllUserByStatus($status){
		$userId = Yii::$app->user->identity->_id;
		$conditions = [];
		$query = User::find();
		if(!empty($status)){
			$conditions['status'] = $status;
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		 
		$query->addOrderBy(['firstname'=>SORT_ASC]);

		$listUser = $query->all();
		return $listUser;
	}
	
	public function findAllUserByName($userName){
		$conditions = [];
		$query = User::find();
		if(!empty($userName)){
			$query->andWhere(['like', "categoryName", $categoryName]);
		}
			
		$listUser = $query->all();
		return $listUser;
	}
	
	public function getArrUser(){
		$arrUser = [];
		$arrEmployee = [];
	
		$listEmployee =  Employee::find()->all();
		foreach ($listEmployee as $obj){
			$arrEmployee[(string)$obj->_id] = $obj->nameTh." ".$obj->sernameTh;
		}
	
		$listUser = User::find()->all();
		foreach ($listUser as $obj){
			$arrUser[(string)$obj->_id] = $arrEmployee[(string)$obj->employeeId];
		}
	
		return $arrUser;
	}
	
	public function getUserName($userId){
		$userModel = User::findOne($userId);
		$userName = $userModel->nameTh." ".$userModel->sernameTh;
		return $userName;
	}
	
	public function getUploadPath(){
		return Yii::getAlias('@webroot').'/'.User::UPLOAD_FOLDER.'/';
	}
	
	public function getUploadUrl(){
		return Yii::getAlias('@web').'/'.User::UPLOAD_FOLDER.'/';
	}
	
	public function getPhotoViewer(){
		return empty($this->avatar) ? Yii::getAlias('@web').'/images/none.png' : User::getUploadUrl().$this->avatar;
	}
	
	public function getPhotoUserViewer($userId){
		$model = User::findOne($userId);
		return empty($model->avatar) ? Yii::getAlias('@web').'/images/none.png' : User::getUploadUrl().$model->avatar;
	}
	
	public function getPhotoUser($userId){
		$model = User::findOne($userId);
		return empty($model->avatar) ? Yii::getAlias('@web').'/images/none.png' : User::getUploadPath().$model->avatar;
	}
	
	public function upload($model,$attribute)
	{
		$photo  = UploadedFile::getInstance($model, $attribute);
		$path = $this->getUploadPath();
		if ($this->validate() && $photo !== null) {
	
			$fileName = md5($photo->baseName.time()) . '.' . $photo->extension;
			//$fileName = $photo->baseName . '.' . $photo->extension;
			if($photo->saveAs($path.$fileName)){
				return $fileName;
			}
		}
		return $model->isNewRecord ? false : $model->getOldAttribute($attribute);
	}
	
}
