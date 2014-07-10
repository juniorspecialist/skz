<?php

/**
 * This is the model class for table "{{manager}}".
 *
 * The followings are the available columns in table '{{manager}}':
 * @property integer $id
 * @property string $fio
 * @property integer $office_manager_id
 *
 * The followings are the available model relations:
 * @property OfficeManager $officeManager
 * @property Report[] $reports
 */
class Manager extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{manager}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fio, code', 'required'),
			array('code', 'length', 'max'=>20),
			array('fio', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, fio, code', 'safe', 'on'=>'search'),
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
			//'officeManager' => array(self::BELONGS_TO, 'OfficeManager', 'office_manager_id'),
			'reports' => array(self::HAS_MANY, 'Report', 'manager_call_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'fio' => 'ФИО',
			'office_manager_id' => 'группа менеджеров',
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
		$criteria->compare('fio',$this->fio,true);
		$criteria->compare('office_manager_id',$this->office_manager_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Manager the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    static function getCodeList(){
        $sql = 'SELECT code FROM {{manager}}';
        $rows = YiiBase::app()->db->createCommand($sql)->queryAll();
        return $rows;
    }

    /*
     * поиск менеджера по коду, возвращаем ID
     */
    static function getIdByCode($code){
        $sql = 'SELECT id  FROM {{manager}} WHERE code=:code';
        $query = YiiBase::app()->db->createCommand($sql);
        $query->bindValue(':code', $code, PDO::PARAM_STR);
        $result = $query->queryRow();
        return $result['id'];
    }

    static function findByCodeList($codeList){

        /*echo '<pre>'; print_r($codeList);

        return array();*/

        $rows = array();

        //for($i=0;$i<sizeof($codeList);$i++){
        foreach($codeList as $j=>$code){
            //$row = $codeList[$i];
            //$where = '';
            $sql = 'SELECT * FROM {{manager}} WHERE code=:code';
            $query = YiiBase::app()->db->createCommand($sql);
            $rows[] = $query->bindValue(':code', $code,PDO::PARAM_STR)->queryRow();
        }

        return $rows;
    }

    /*
     * получаем список всех менеджеров
     */
    static function getListManagers(){

        $sql = 'SELECT * FROM tbl_manager';

        return YiiBase::app()->db->createCommand($sql)->queryAll();
    }
}
