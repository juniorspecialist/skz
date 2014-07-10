<?php

/**
 * This is the model class for table "{{phone_regions}}".
 *
 * The followings are the available columns in table '{{phone_regions}}':
 * @property integer $id
 * @property string $phone
 * @property string $region
 */
class PhoneRegions extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{phone_regions}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('phone, region_id, site_id', 'required'),
			array('phone', 'length', 'max'=>60),
            array('region_id, site_id', 'numerical', 'integerOnly'=>true),
			//array('region', 'length', 'max'=>80),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, phone, region_id, site_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'region' => array(self::BELONGS_TO, 'Region', 'region_id'),
            'site' => array(self::BELONGS_TO, 'Site', 'site_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'phone' => 'Телефон',
			'region_id' => 'Регион,название направления для звонка',
            'site_id'=>'Сайт',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('region_id',$this->region,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PhoneRegions the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /*
     * ищим по номеру ДИД-номер, есть ли он в базе
     */
    static function findPhoneByNumber($phone){
        $sql = 'SELECT id FROM {{phone_regions}} WHERE phone=:phone';
        $query = YiiBase::app()->db->createCommand($sql);
        $query->bindParam(':phone', $phone, PDO::PARAM_STR);
        $row = $query->queryRow();

        return $row['id'];
    }

    /*
     * получаем список всех номеров(DID)
     */
    static function getPhonesList(){
        $sql = 'SELECT phone FROM {{phone_regions}}';
        $query = YiiBase::app()->db->createCommand($sql)->queryAll();
        return $query;
    }

    /*
     * определяем сайт Входящего звонка по его ДИД
     * $did - номер, на который звонит клиент
     */
    static function getSiteByDid($did){

        $sql = 'SELECT * FROM {{phone_regions}} WHERE phone=:phone';

        $query = YiiBase::app()->db->cache(1000)->createCommand($sql);

        $query->bindValue(':phone', $did, PDO::PARAM_STR);

        return $query->queryRow();
    }
}
