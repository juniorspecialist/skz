<?php

/**
 * This is the model class for table "{{office_manager}}".
 *
 * The followings are the available columns in table '{{office_manager}}':
 * @property integer $id
 * @property string $title
 *
 * The followings are the available model relations:
 * @property Manager[] $managers
 * @property Report[] $reports
 */
class OfficeManager extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{office_manager}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title,code', 'required'),
			array('title', 'length', 'max'=>256),
            array('code','length', 'max'=>40),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title', 'safe', 'on'=>'search'),
		);
	}

    /*
     * получаем список офисов
     * если указан код($code) - то возвращаем название конкретного офиса
     */
    static function getListOffice($code=''){
        if(!empty($code)){
            $list = self::getListOffice();
            return isset($list[$code])?$list[$code]:$code;
        }else{
            return array(
                3=>'Электрозаводская',
                4=>'Юбилейный',
                5=>'Яблочково',
            );
        }
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'managers' => array(self::HAS_MANY, 'Manager', 'office_manager_id'),
			'reports' => array(self::HAS_MANY, 'Report', 'office_call_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Название категории менеджеров',
            'code'=>'Код',
            'rus_title'=>'Офис',
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
		$criteria->compare('title',$this->title,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return OfficeManager the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    /*
     * находим по коду ID Офиса
     */
    static function getIdByCode($code){

        $sql = 'SELECT id FROM tbl_office_manager WHERE code=:code';

        $query = YiiBase::app()->db->createCommand($sql);

        $query->bindValue(':code', $code, PDO::PARAM_STR);

        $row = $query->queryRow();

        return $row['id'];
    }

    /*
     * находим Название Офиса по его ID
     */
    static function getOfficeById($id){

        $sql = 'SELECT title FROM tbl_office_manager WHERE id=:id';

        $query = YiiBase::app()->db->cache(10000)->createCommand($sql);

        $query->bindValue(':id', $id, PDO::PARAM_INT);

        $row = $query->queryRow();

        return $row['title'];
    }

    /*
     * получаем список КОДОВ офисов, чтобы фильтровать те что УЖЕ есть и добавить лишь НОВЫЕ
     */
    static function getCodeList(){

        $sql = 'SELECT code FROM tbl_office_manager';

        $rows = YiiBase::app()->db->createCommand($sql)->queryAll();

        return $rows;
    }


}
