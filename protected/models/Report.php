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
    const CALL_RESET_CLIENT = 5;//сброшено клиентом


    //флаг, что звонок является автоперезвоном, т.е. был создан по заявке через сокеты
    public $call_back = false;

    //public $accept_list = array();


    //список статусов для автоперезвона
    const CALL_BACK_SEND = 1;//отправили заявку на перезвон
    const CALL_BACK_ACTION_CLIENT = 2;// перезвонили клиенту
    const CALL_BACK_WAIT = 3;//ждёт отправки заявки на перезвон


    public $cnt;

    /*
     * получаем список фильтров которые были применены для таблицы отчётов
     * анализируем массив $_GET данных и выводим список применяемых фильтров к таблице
     */
    public function getAcceptList(){

        $list = array();

        //TODO проверить, чтобы все фильтра писались в список через запятую($_GET['Report[office_call_id]'])

        foreach($_GET as $j=>$value){

            //статус автоперезвона
            if($this->call_back_status){$list[] = 'Статус автоперезвона';}

            //ID звонка
            if(!empty($_GET['search_word_accept_reg_uniqueid'])){$list[] = 'ID звонка';}
            //Номер клиента
            if(!empty($_GET['search_word_accept_reg_CallerId'])){$list[] = 'Номер клиента';}
            //
            if(!empty($_GET['search_word_accept_reg_Did'])){$list[] = 'DID';}
            //
            if(!empty($_GET['search_word_accept_reg_call_city'])){$list[] = 'Город звонка';}
            //
            if(!empty($_GET['DateCall_to']) || !empty($_GET['DateCall_from'])){$list[] = 'Дата звонка';}
            //
            if(!empty($_GET['TimeStartCall_from']) || !empty($_GET['TimeStartCall_to'])){$list[] = 'Время начала разговора';}
            //
            if(!empty($_GET['TimeEndCall_from']) || !empty($_GET['TimeEndCall_to'])){$list[] = 'Время конца разговора';}
            //
            if(!empty($_GET['DurationCallCall_from']) || !empty($_GET['DurationCallCall_to'])){$list[] = 'Продолжительность звонка';}
            //
            if(!empty($_GET['search_word_accept_reg_dest'])){$list[] = 'Destination звонка';}
            //

            //
            if(!empty($this->site_id)){$list[] = 'Сайт';}
            //
            if(!empty($this->call_diraction)){$list[] = 'Направление звонка';}
            //
            if(!empty($this->status_call)){$list[] = 'Статус обработки звонка';}
            //
            if(!empty($this->manager_call_id)){$list[] = 'Менеджер звонка';}
            //
            if(!empty($_GET['TimeWait_from']) || !empty($_GET['TimeWait_to'])){$list[] = 'Время ожидания клиента';}
            //
            if(!empty($_GET['CountRedirect_from']) || !empty($_GET['CountRedirect_to'])){$list[] = 'Кол-во переадресаций';}

            if(!empty($_GET['search_word_accept_reg_redirect'])){$list[] = 'Цепочка пройденных переадресаций';}

            if(!empty($this->office_call_id)){ $list[] = 'Офис звонка';}
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
            5=>'Сброшено клиентом',
        );
    }


    /*
     * определяем статус звонка для таблицы отчёта
     */
    public function getStatusToTbl(){
        if($this->status_call==Report::CALL_ANSWERED){return 'Отвечен';}
        if($this->status_call==Report::CALL_NO_ANSWER){return 'Не отвечен';}
        if($this->status_call==Report::CALL_RESET_CLIENT){return 'Сброшен клиентом';}
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
        if($string_status_call=='RESET_CLIENT'){return 5;}
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
            //call_city,did,destination_call,time_end_call, destination_call,
			array('uniqueid,  date_call, time_start_call,  call_diraction, status_call,  linkedid, caller_id', 'required'),

            //проверим заполнение менеджера по звонку, если статус у звонка отвечен
            array('manager_call_id', 'check_manager'),

			array('duration_call,  call_diraction, status_call, manager_call_id, waiting_time, count_redirect, phone_region_id, site_id, call_back_status', 'numerical', 'integerOnly'=>true),
			array('uniqueid, linkedid, caller_id, destination_call, call_city, office_call_id,call_back_linkdid', 'length', 'max'=>60),
			array('did', 'length', 'max'=>40),
			array('rec_call, search_word', 'length', 'max'=>256),
            //array('chain_passed_redirects', 'length', 'max'=>512),
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
            if(!$this->call_back){
                if($this->status_call!=self::CALL_BUSY && $this->status_call!=self::CALL_NO_ANSWER &&$this->status_call!=self::CALL_RESET_CLIENT  && empty($this->manager_call_id)){
                    $this->addError('manager_call_id', 'Не указан менеджер принвяший звонок');
                }
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
            'call_back_status'=>'Автоперезвон',
            'call_back_linkdid'=>'linkdid автоперезвона по этому пропущенному звонку',
		);
	}

    /*
     * получаем список автоперезвона
     */
    public function getCallbackstatus(){
        if(!empty($this->call_back_status) && $this->call_back_status!==0){
            //определяем статус автоперезвона
            if($this->call_back_status==Report::CALL_BACK_WAIT){
                return 'Ждёт отправки заявки на перезвон';
            }

            if($this->call_back_status==Report::CALL_BACK_SEND){
                return 'Отправили заявку на перезвон';
            }

            if($this->call_back_status==Report::CALL_BACK_ACTION_CLIENT){
                return 'Перезвонили клиенту';
            }
        }
    }

    /*
     * возвращаем список статусов для автоперезвона
     */
    static function getCallbackstatusList(){
        return array(
            0=>''
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

    /*
     * после записи информации о звонке:
     * запишим данные для очереди автодозвона, если они нужны
     */

    protected function afterSave() {
        parent::afterSave();
        if ($this->isNewRecord) {
            //проверяем статус звонка, чтобы добавить его в очередь автоперезвона
            if($this->call_diraction==Report::INCOMING_CALL && !$this->call_back){//ТОЛЬКО ВХОДЯЩИЙ
                if($this->status_call==Report::CALL_NO_ANSWER || $this->status_call==Report::CALL_RESET_CLIENT){

                    //установим статус у пропущенной заявки - как ждёт отправки заявки на перезвон
                    YiiBase::app()->db->createCommand('UPDATE {{report}} SET call_back_status="'.self::CALL_BACK_WAIT.'" WHERE id="'.$this->id.'"')->execute();

                    //есть список номеров исключений, которые мы исключаем из перезвона
                    $exeption_list = Yii::app()->params['call_back_exeption_list'];

                    //проверим номер в списке исключений+ если номер определился корректно
                    if(!in_array($this->caller_id,$exeption_list) && preg_match('/[0-9]{7,15}/',$this->caller_id)){

                        $call_back = new Callback();
                        $call_back->client_number = $this->caller_id;
                        //если офис не указан у звонка(статус - сброшен клиентом)
                        if(empty($this->office_call_id) || $this->office_call_id==0){
                            //укажим офис - 300 электрозавод
                            $call_back->office = 3;//электрозавод
                        }else{
                            $call_back->office = $this->office_call_id;
                        }
                        //0000-00-00 00:00:00
                        $call_back->call_date = date('Y-m-d H:i:s',strtotime($this->date_call.' '.$this->time_start_call));
                        $call_back->linkedid = trim($this->linkedid);
                        $call_back->status = 2;
                        $call_back->site = Site::getSiteById($this->site_id);//укажем для какого сайта был звонок
                        if(!$call_back->save()){
                            echo '<pre>'; print_r($call_back->errors);
                        }else{
                            //echo '<pre>'; print_r($this->linkedid);
                            //echo '<pre>'; print_r($call_back->linkedid);//die();
                        }
                    }

                }
            }

            //был автоперезвон, по ранее отправленной заявке
            //подвязываем к пропущенному звонку текущий звонок+укажем статус перезвона(для пропущенного звонка)
            if($this->call_back){
                $this->callBackUpdateInfo();
            }
        }
    }

    /*
     * обновим информацию о результатах обратного звонка
     */
    public function callBackUpdateInfo(){

        if($this->office_call_id==0 || empty($this->office_call_id)){
            $office_call_id = 3;//электрозавод
        }else{
            $office_call_id = $this->office_call_id;
        }

        $sql = 'SELECT linkedid, id FROM tbl_call_back WHERE client_number="'.$this->caller_id.'" AND office="'.$office_call_id.'"  ORDER BY id DESC';//AND status="'.Callback::SEND_CALL.'"

        $find_call = YiiBase::app()->db->createCommand($sql);
        $find_row = $find_call->queryRow();

        if(!empty($find_row)){
            //привяжем звонок из перезвона со входящим пропущенным звонком от клиента
            //т.е. сперва найдём пропущенный звонок из раннее сделанных+ привяжем к нему инфу о текущем звонке и удалим из очереди заявку на перезвон
            $sql_update = 'UPDATE tbl_report SET call_back_linkdid=:call_back_linkdid,call_back_status=:call_back_status WHERE linkedid=:linkedid';
            $query_update = YiiBase::app()->db->createCommand($sql_update);
            $query_update->bindValue(':call_back_linkdid',$this->uniqueid, PDO::PARAM_STR);
            $query_update->bindValue(':call_back_status',self::CALL_BACK_ACTION_CLIENT, PDO::PARAM_INT);
            $query_update->bindValue(':linkedid',$find_row['linkedid'], PDO::PARAM_INT);
            $query_update->execute();

            /*
             * обновим статус у пропущенных звонков за сегодня по данному номеру
             */
            $upd_sql = 'UPDATE tbl_report SET call_back_linkdid=:call_back_linkdid,call_back_status=:call_back_status WHERE caller_id=:caller_id AND date_call=:date_call';
            $query_update_ = YiiBase::app()->db->createCommand($upd_sql);
            $query_update_->bindValue(':call_back_linkdid',$this->uniqueid, PDO::PARAM_STR);
            $query_update_->bindValue(':call_back_status',self::CALL_BACK_ACTION_CLIENT, PDO::PARAM_INT);
            $query_update_->bindValue(':caller_id',$this->caller_id, PDO::PARAM_STR);
            $query_update_->bindValue(':date_call',$this->date_call, PDO::PARAM_STR);
            $query_update_->execute();


            //теперь удалим из очереди автодозвона заявку, по которой был авто-дозвон
            $sql_delete = 'DELETE FROM tbl_call_back WHERE id=:id';
            $query_delete = YiiBase::app()->db->createCommand($sql_delete)->bindValue(':id', $find_row['id'], PDO::PARAM_INT)->execute();
        }else{
            echo $this->linkedid.'|empty find linkedid,id from tbl_call_back='.$sql.PHP_EOL;
        }
    }
}
