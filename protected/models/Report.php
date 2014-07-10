<?php

/**
 * This is the model class for table "{{report}}".
 *
 * The followings are the available columns in table '{{report}}':
 * @property integer $id
 * @property string $call_id
 * @property string $caller_id
 * @property string $did
 * @property integer $call_city
 * @property integer $date_call
 * @property integer $time_start_call
 * @property integer $time_end_call
 * @property integer $duration_call
 * @property integer $destination_call
 * @property integer $office_call_id
 * @property integer $call_diraction
 * @property integer $status_call
 * @property integer $manager_call_id
 * @property integer $waiting_time
 * @property integer $count_redirect
 * @property string $chain_passed_redirects
 * @property string $rec_call
 * @property string $source
 * @property string $search_word
 *
 * The followings are the available model relations:
 * @property OfficeManager $officeCall
 * @property Manager $managerCall
 */
class Report extends CActiveRecord
{

    const INCOMING_CALL = 1;//входящий звонок
    const OUTGOING_CALL = 2;//исходящий звонок

    const CALL_ANSWERED = 1;//ответили на звонок
    const CALL_FAILED = 2;//не удачно позвонили
    const CALL_BUSY = 3;//занято
    const CALL_NO_ANSWER = 4;//нет ответа


    //public $accept_list = array();


    /*
     * получаем список фильтров которые были применены для таблицы отчётов
     * анализируем массив $_GET данных и выводим список применяемых фильтров к таблице
     */
    public function getAcceptList(){

        $list = array();

        //TODO проверить, чтобы все фильтра писались в список через запятую($_GET['Report[office_call_id]'])

        foreach($_GET as $j=>$value){

            //ID звонка
            if($j=='search_word_accept_reg_uniqueid'){$list[] = 'ID звонка';}
            //Номер клиента
            if($j=='search_word_accept_reg_CallerId'){$list[] = 'Номер клиента';}
            //
            if($j=='search_word_accept_reg_Did'){$list[] = 'DID';}
            //
            if($j=='search_word_accept_reg_call_city'){$list[] = 'Город звонка';}
            //
            if($j=='DateCall_to' || $j=='DateCall_from'){$list[] = 'Дата звонка';}
            //
            if($j=='TimeStartCall_from' || $j=='TimeStartCall_to'){$list[] = 'Время начала разговора';}
            //
            if($j=='TimeEndCall_from' || $j=='TimeEndCall_to'){$list[] = 'Время конца разговора';}
            //
            if($j=='DurationCallCall_from' || $j=='DurationCallCall_to'){$list[] = 'Продолжительность звонка';}
            //
            if($j=='search_word_accept_reg_dest'){$list[] = 'Destination звонка';}
            //

            //
            if(!empty($_GET['Report[site_id]'])){$list[] = 'Сайт';}
            //
            if($j=='Report[call_diraction]'){$list[] = 'Направление звонка';}
            //
            if($j=='Report[status_call]'){$list[] = 'Статус обработки звонка';}
            //
            if($j=='Report[manager_call_id]'){$list[] = 'Менеджер звонка';}
            //
            if($j=='TimeWait_from' || $j=='TimeWait_to'){$list[] = 'Время ожидания клиента';}
            //
            if($j=='CountRedirect_from' || $j=='CountRedirect_to'){$list[] = 'Кол-во переадресаций';}
            if($j=='search_word_accept_reg_redirect'){$list[] = 'Цепочка пройденных переадресаций';}
        }

        $list = array_unique($list);

        return implode(',' , $list);
    }

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{report}}';
	}

    static function getStatusList(){
        return array(
            1=>'Принят',
            2=>'FAILED',
            3=>'Занято',
            4=>'Нет ответа',
        );
    }



    /*
     * определяем текстовое описание по ID статуса звонка
     */
    public function getStatusCall($status_call_id=''){
        if(empty($status_call_id)){
            $status_call_id = $this->status_call;
        }
        $list = self::getStatusList();
        return $list[$status_call_id];
    }

    /*
     * переводим текстовое описание статуса звонка в числовое
     */
    static function statusCallToInt($string_status_call){
        if($string_status_call=='BUSY'){ return 3; }
        if($string_status_call=='ANSWERED'){ return 1; }
        if($string_status_call=='FAILED'){ return 2; }
        if($string_status_call=='NO ANSWER'){ return 4; }
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            //, waiting_time, count_redirect, chain_passed_redirects, rec_call, source, search_word
            //call_city,caller_id,did,destination_call,time_end_call,
			array('uniqueid,  date_call, time_start_call,  duration_call, destination_call, call_diraction, status_call,  linkedid', 'required'),

            //проверим заполнение менеджера по звонку, если статус у звонка отвечен
            array('manager_call_id', 'check_manager'),

			array('duration_call,  call_diraction, status_call, manager_call_id, waiting_time, count_redirect, phone_region_id, site_id', 'numerical', 'integerOnly'=>true),
			array('uniqueid, linkedid, caller_id, destination_call, call_city, office_call_id', 'length', 'max'=>60),
			array('did', 'length', 'max'=>40),
			array('chain_passed_redirects, rec_call, search_word', 'length', 'max'=>256),
			array('source', 'length', 'max'=>250),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, call_id, caller_id, did, call_city, date_call, time_start_call, time_end_call, duration_call, destination_call, office_call_id, call_diraction, status_call, manager_call_id, waiting_time, count_redirect, chain_passed_redirects, rec_call, phone_region_id', 'safe', 'on'=>'search'),
		);
	}

    /*
     * правило валидации для заполнения менеджера по звонку
     * если звонок отвечен - значит должен быть заполнен ОБЯЗАТЕЛЬНО менеджер,
     * если не отвечен - не указан менеджер
     * или если исходящий звонок, тогда менеджер звонивший есть и менеджер подвязанный к звонку
     */
    public function check_manager(){
        if(!$this->hasErrors()){
            if($this->status_call!=self::CALL_BUSY && $this->status_call!=self::CALL_NO_ANSWER && empty($this->manager_call_id)){
                $this->addError('manager_call_id', 'Не указан менеджер принвяший звонок');
            }
        }
    }

    /*
     * выводим направление звонка
     */
    public function getCallDiraction(){
        //return $this->getStatusCall($this->status_call);
        if($this->call_diraction==self::OUTGOING_CALL){
            return 'Исходящий';
        }else{
            return 'Входящий';
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
			'officeсall' => array(self::BELONGS_TO, 'OfficeManager', 'office_call_id'),
			'managerCall' => array(self::BELONGS_TO, 'Manager', 'manager_call_id'),
            'callcity' => array(self::BELONGS_TO, 'City', 'call_city'),
            //'site' => array(self::BELONGS_TO, 'Site', 'site_id'),
		);
	}

    /*
     * Получаем название сайта по его ID
     */
    public function getSite(){
        if($this->site_id==0){
            return '';
        }else{
            return Site::getSiteById($this->site_id);
        }
    }

    /*
     * для таблицы отчётов получаем название офиса по строке, если там значение!=0
     */
    public function getCallOffice(){
        if($this->office_call_id!=0){
            return OfficeManager::getListOffice($this->office_call_id);
        }else{
            return '';
        }
        /*if(!empty($this->office_call_id)){
            if($this->office_call_id!=0){
                return OfficeManager::getOfficeById($this->office_call_id);
            }else{
                return '';
            }
        }else{
            return '';
        }*/
    }

    /*
    public function getSite(){
        if($this->site_id!=0){
            return $this->
        }
    }*/

    /*
    public function getCallCity(){
        if($this->call_city==0){
            return '';
        }else{
            return City::getCityById($this->call_city);
        }
    }*/

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'uniqueid' => 'ID звонка',//это уникальный номер очереди которая входит с состав звонка
            'linkedid'=>'Уникальный идентификатор звонка',//
			'caller_id' => 'Номер клиента (Caller ID)',
			'did' => 'Виртуальный номер на который позвонил клиент (DID)',
			'call_city' => 'Город звонка',
			'date_call' => 'дата звонка в формате год месяц число',
			'time_start_call' => 'Время начала разговора',
			'time_end_call' => 'Время конца разговора',
			'duration_call' => 'Продолжительность звонка',
			'destination_call' => 'Destination звонка',
			'office_call_id' => 'Офис',
			'call_diraction' => 'Направление звонка',
			'status_call' => 'Статус обработки звонка',
			'manager_call_id' => 'Менеджер звонка',
			'waiting_time' => 'время от соединения с сервером до взятия трубки менеджером в секундах',
			'count_redirect' => 'сколько раз звонок был переадресован между менеджерами, прежде чем трубка была поднята',
			'chain_passed_redirects' => 'Цепочка пройденных переадресаций в формате имен менеджеров "сева-катя-джамал" и пр',
			'rec_call' => 'Запись звонка',
			'source' => 'Источник звонка(API calltoch)',
			'search_word' => 'Поисковая фраза(API calltouch)',
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
		//$criteria->compare('call_id',$this->call_id,true);
		$criteria->compare('caller_id',$this->caller_id,true);
		$criteria->compare('did',$this->did,true);
		$criteria->compare('call_city',$this->call_city);
		$criteria->compare('date_call',$this->date_call);
		$criteria->compare('time_start_call',$this->time_start_call);
		$criteria->compare('time_end_call',$this->time_end_call);
		$criteria->compare('duration_call',$this->duration_call);
		$criteria->compare('destination_call',$this->destination_call);
		$criteria->compare('office_call_id',$this->office_call_id);
		$criteria->compare('call_diraction',$this->call_diraction);
		$criteria->compare('status_call',$this->status_call);
		$criteria->compare('manager_call_id',$this->manager_call_id);
		$criteria->compare('waiting_time',$this->waiting_time);
		$criteria->compare('count_redirect',$this->count_redirect);
		$criteria->compare('chain_passed_redirects',$this->chain_passed_redirects,true);
		$criteria->compare('rec_call',$this->rec_call,true);
		$criteria->compare('source',$this->source,true);
		$criteria->compare('search_word',$this->search_word,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Report the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /*
     * получаем список идентификаторов каналов для звонков
     * почему именно каналов а не идентификаторов звонк ? - по той таблице где мы делаем первоначальную выборку нет идентификаторов звонков
     * $intervalMinuts - за какой промежуток времени делаем выборку данных, в минутах
     */
    static function getListId($intervalMinuts = ''){

        //если указан интервал выборки делаем выборку за интервал времени
        if(!empty($intervalMinuts)){
            $sql = 'SELECT linkedid FROM tbl_report';
        }else{
            $sql = 'SELECT linkedid FROM tbl_report';
        }

        $rows = YiiBase::app()->db->createCommand($sql)->queryAll();

        return $rows;
    }

    /*
      * формируем сслыку на скачивание файла аудио-записи разговора
      */
    public function getLinkDownloadRec(){

        $link = '';

        if(!empty($this->rec_call)){
            $url_download = 'http://80.84.116.238/download.php?file='.date('Y/m/d/',strtotime($this->date_call)).$this->rec_call;
            $link  = CHtml::link('Скачать',$url_download);


            $div = $link.'<div id="'.$this->linkedid.'">
                            <audio>
                                <source src="'.$url_download.'" type="audio/x-wav" >
                            </audio>
                         </div>';

            //return $div;
        }

        return $link;
    }

    /*
       * экспорт данных в файл экспорта
       */
    //TODO доделать экспорт данных в файл, проверить все столбц ли экспортятся
    public function exportToFile($dataProvider, $nameFile){
        $h1 = iconv('utf-8', 'windows-1251//IGNORE','ID звонка');
        $h2 = iconv('utf-8', 'windows-1251//IGNORE','Номер клиента');
        $h3 = iconv('utf-8', 'windows-1251//IGNORE','DID');
        $h4 = iconv('utf-8', 'windows-1251//IGNORE','Город звонка');
        $h5 = iconv('utf-8', 'windows-1251//IGNORE','Дата звонка');
        $h6 = iconv('utf-8', 'windows-1251//IGNORE','Время начала разговора');
        $h7 = iconv('utf-8', 'windows-1251//IGNORE','Время конца разговора');
        $h8 = iconv('utf-8', 'windows-1251//IGNORE','Продолжительность звонка');
        $h9 = iconv('utf-8', 'windows-1251//IGNORE','Destination звонка');
        $h10 = iconv('utf-8', 'windows-1251//IGNORE','Офис звонка');
        $h11 = iconv('utf-8', 'windows-1251//IGNORE','Направление звонка');
        $h12 = iconv('utf-8', 'windows-1251//IGNORE','Статус обработки звонка');
        $h13 = iconv('utf-8', 'windows-1251//IGNORE','Менеджер звонка');
        $h14 = iconv('utf-8', 'windows-1251//IGNORE','Время ожидания клиента');
        $h15 = iconv('utf-8', 'windows-1251//IGNORE','Кол-во переадресаций');
        $h16 = iconv('utf-8', 'windows-1251//IGNORE','Цепочка пройденных переадресаций');
        $h17 = iconv('utf-8', 'windows-1251//IGNORE','Запись звонка');


        $header = array($h1, $h2, $h3, $h4, $h5, $h6, $h7, $h8, $h9);

        //создаём файл для экспорта, и с помощью ИТЕРАТОРА выбираем данные порциями и записываем их в файл, чтобы не было нихватки памяти по большой выборке данных
        $out = fopen($nameFile, 'w');

        //запишим заголовки столбцов
        fputcsv($out, $header,';');

        //теперь выборка через ИТЕРАТОР
        // выбирем с помощью ИТЕРАТОРа по 5000 записей
        $iterator=new CDataProviderIterator($dataProvider,1000);

        // обходим данные для каждой строки из логов
        foreach($iterator as $row){

            $iterator->callOffice = mb_convert_encoding($iterator->callOffice, "windows-1251", "utf-8");

            $data =  array(
                $iterator->uniqueid,
                $iterator->caller_id,
                $iterator->did,
                $iterator->call_city,
                $iterator->date_call,
                $iterator->time_start_call,
                $iterator->time_end_call,
                $iterator->duration_call,
                $iterator->destination_call,
                $iterator->callOffice,

            );

            fputcsv($out, $data,';');
        }
        fclose($out);
    }
}
