<?php

/**
 * This is the model class for table "{{relation_office_manager}}".
 *
 * The followings are the available columns in table '{{relation_office_manager}}':
 * @property integer $id
 * @property integer $office_id
 * @property integer $manager_id
 *
 * The followings are the available model relations:
 * @property OfficeManager $office
 * @property Manager $manager
 */
class RelationOfficeManager extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{relation_office_manager}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('office_id, manager_id', 'required'),
			array('office_id, manager_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, office_id, manager_id', 'safe', 'on'=>'search'),
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
			'office' => array(self::BELONGS_TO, 'OfficeManager', 'office_id'),
			'manager' => array(self::BELONGS_TO, 'Manager', 'manager_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'office_id' => 'Office',
			'manager_id' => 'Manager',
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
		$criteria->compare('office_id',$this->office_id);
		$criteria->compare('manager_id',$this->manager_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return RelationOfficeManager the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /*
     * список менеджеров по ID_офис
     */
    static function getListManagers($office_id){

        $sql = 'SELECT * FROM tbl_relation_office_manager  WHERE office_id=:office_id';

        $query = YiiBase::app()->db->createCommand($sql);

        $query->bindParam(':office_id', $office_id, PDO::PARAM_INT);

        return $query->queryAll();
    }
}
