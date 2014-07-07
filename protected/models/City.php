<?php

/**
 * This is the model class for table "{{city}}".
 *
 * The followings are the available columns in table '{{city}}':
 * @property integer $id
 * @property string $city
 * @property string $region
 * @property string $code
 */
class City extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{city}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('city, region, code', 'required'),
			array('city', 'length', 'max'=>255),
			array('region', 'length', 'max'=>256),
			array('code', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, city, region, code', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'city' => 'Город',
			'region' => 'Область',
			'code' => 'Код',
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
		$criteria->compare('city',$this->city,true);
		$criteria->compare('region',$this->region,true);
		$criteria->compare('code',$this->code,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return City the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /*
     * определяем код города из номера телефона и по коду города из номера - определяем город
     */
    static function getCityByPhone($phone){

        if(strlen($phone)<6){
            return '';
        }else{
            $phone = str_replace('+', '', $phone);
            $phone = str_replace('-', '', $phone);

            if(!empty($phone)){
                //код города может состоять из 3,4,5,6 цифр
                $code1 = substr($phone,1,4);
                $code2 = substr($phone,1,5);
                $code3 = substr($phone,1,5);
                $code4 = substr($phone,1,3);

                //зависимости от КЕШа
                $dependency = new CDbCacheDependency('SELECT MAX(id) FROM {{city}}');

                //запрос поиска города по совпадению по коду
                $sql = 'SELECT city FROM {{city}} WHERE code LIKE  "%'.$code1.'%" OR code LIKE "%'.$code2.'%"  OR code LIKE "%'.$code3.'%" OR code LIKE "%'.$code4.'%"';

                //echo 'sql='.$sql.'<br>';

                $data = YiiBase::app()->db->cache(10000, $dependency)->createCommand($sql)->queryRow();

                //echo $data['id'];

                return $data['city'];
            }else{
                return '';
            }
        }
    }

    static function getCityById($id){
        $sql = 'SELECT city FROM tbl_city WHERE id=:id';
        $query = YiiBase::app()->db->createCommand($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        return $query->queryScalar();
    }
}
