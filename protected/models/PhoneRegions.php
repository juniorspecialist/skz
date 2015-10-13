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

    public $countrec;

    public $report_count;



    public $siteArray;
    public $regionArray;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{phone_regions_site}}';
	}


    public function getSiteArray()
    {
        if ($this->siteArray===null)
            $this->categories_array=CHtml::listData($this->categories, 'id', 'id');
        return $this->categories_array;
    }

    public function setSiteArray($value)
    {
        $this->siteArray[]=$value;
    }
    /**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('phone, region, site', 'required'),
            array('siteArray,regionArray','safe'),
			array('phone, site', 'length', 'max'=>60),
            array('region,report_count', 'length', 'max'=>255),
            //array('region, site', 'numerical', 'integerOnly'=>true),
			//array('region', 'length', 'max'=>80),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, phone, region, site,report_count', 'safe', 'on'=>'search'),
		);
	}

    public function getCityList(){
        return YiiBase::app()->db->createCommand('SELECT distinct(region) FROM {{phone_regions_site}}')->queryAll();
    }

    public function getSiteList(){
        return YiiBase::app()->db->createCommand('SELECT distinct(site) FROM {{phone_regions_site}}')->queryAll();
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            //'region' => array(self::BELONGS_TO, 'Region', 'region_id'),
            //'site' => array(self::BELONGS_TO, 'Site', 'site_id'),
            //report.caller_id = phoneregions.phone
           // 'phoneCount'=>array(self::STAT, 'Report', 'caller_id', 'select' => 'count( DISTINCT (caller_id) )'),
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
			'region' => 'Регион,название направления для звонка',
            'site'=>'Сайт',
            'report_count'=>'Счётчик звонков',
            'siteArray'=>'Список сайтов',
            'regionArray'=>'Список городов',
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
        $criteria->with = 'site';
		$criteria->compare('id',$this->id);
        $criteria->compare('site',$this->site);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('region',$this->region,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /*
     * получаем список сайтов для фильтрации в общей таблице отчётов
     */
    public static function siteList(){
        $sql = 'SELECT DISTINCT(site) as site FROM {{phone_regions_site}}';
        $rows = YiiBase::app()->db->createCommand($sql)->queryAll();
        return $rows;
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
     * определяем сайт Входящего звонка по его ДИД
     * $did - номер, на который звонит клиент
     */
    static function getInfoByDid($did){

        $sql = 'SELECT * FROM {{phone_regions_site}} WHERE phone=:phone';

        $query = YiiBase::app()->db->createCommand($sql);

        $query->bindValue(':phone', $did, PDO::PARAM_STR);

        return $query->queryRow();
    }
}
