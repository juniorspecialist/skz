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
    public $caller_id;

    public $total_count = 0;

    /*
     * получаем список фильтров которые были применены для таблицы отчётов
     * анализируем массив $_GET данных и выводим список применяемых фильтров к таблице
     */
    public function getAcceptList(){

        $list = array();

        //TODO проверить, чтобы все фильтра писались в список через

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
            if(!empty($this->site)){$list[] = 'Сайт';}
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

            if(!empty($this->groups)){$list[] = 'Группа';}

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
        if($this->status_call==Report::CALL_BUSY){return 'Занято';}
        if($this->status_call==Report::CALL_RESET_CLIENT){return 'Сброшен клиентом';}
        if($this->status_call==Report::CALL_FAILED){return 'Не удалось';}
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
			array('uniqueid,  date_call, time_start_call,  call_diraction, status_call,  caller_id', 'required'),
            //проверим заполнение менеджера по звонку, если статус у звонка отвечен
            array('manager_call_id', 'check_manager'),
            array('rec_call','check_rec'),//проверки записи разговора
			array('duration_call,  call_diraction, status_call, manager_call_id, waiting_time, count_redirect,   call_back_status, user_id', 'numerical', 'integerOnly'=>true),
			array('uniqueid, caller_id, destination_call, call_city, call_back_linkdid', 'length', 'max'=>60),
			array('did', 'length', 'max'=>40),
			array('rec_call, busy_manager, guilty_manager', 'length', 'max'=>256),
			// @todo Please remove those attributes that should not be searched.
			array('id, site,groups, user_id,guilty_manager, busy_manager,call_id, caller_id, did, call_city, date_call, time_start_call, time_end_call, duration_call, destination_call,  call_diraction, status_call, manager_call_id, waiting_time, count_redirect, chain_passed_redirects, rec_call', 'safe', 'on'=>'search'),
		);
	}

    /*
     * валидация записи разговора
     * она должна быть уникальная+ не пустая
     */
    public function check_rec(){
        if(!$this->hasErrors()){
            if(!empty($this->rec_call)){
                $find = YiiBase::app()->db->createCommand('SELECT id FROM {{report}} WHERE rec_call="'.$this->rec_call.'"')->queryRow();
                if($find){
                    $this->addError('rec_call', 'Файл записи уже существует по данному звонку');
                }
            }
        }
    }

    /*
     * правило валидации для заполнения менеджера по звонку
     * если звонок отвечен - значит должен быть заполнен ОБЯЗАТЕЛЬНО менеджер,
     * если не отвечен - не указан менеджер
     * или если исходящий звонок, тогда менеджер звонивший есть и менеджер подвязанный к звонку
     */
    public function check_manager(){
        if(!$this->hasErrors()){
            if($this->call_diraction==self::INCOMING_CALL){
                if($this->status_call!=self::CALL_BUSY && $this->status_call!=self::CALL_NO_ANSWER &&$this->status_call!=self::CALL_RESET_CLIENT  && empty($this->manager_call_id)){
                    $this->addError('manager_call_id', 'Не указан менеджер принявший звонок');
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

			'managerCall' => array(self::BELONGS_TO, 'Manager', 'manager_call_id'),
            'callcity' => array(self::BELONGS_TO, 'City', 'call_city'),

		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'uniqueid' => 'ID звонка',//это уникальный номер очереди которая входит с состав звонка
            //'linkedid'=>'Уникальный идентификатор звонка',//
			'caller_id' => 'Номер клиента (Caller ID)',
			'did' => 'Виртуальный номер на который позвонил клиент (DID)',
			'call_city' => 'Город звонка',
			'date_call' => 'дата звонка в формате год месяц число',
			'time_start_call' => 'Время начала разговора',
			'time_end_call' => 'Время конца разговора',
			'duration_call' => 'Продолжительность звонка',
			'destination_call' => 'Destination звонка',
			'call_diraction' => 'Направление звонка',
			'status_call' => 'Статус обработки звонка',
			'manager_call_id' => 'Менеджер звонка',
			'waiting_time' => 'время от соединения с сервером до взятия трубки менеджером в секундах',
			'count_redirect' => 'сколько раз звонок был переадресован между менеджерами, прежде чем трубка была поднята',
			'chain_passed_redirects' => 'Цепочка пройденных переадресаций в формате имен менеджеров "сева-катя-джамал" и пр',
			'rec_call' => 'Запись звонка',
            'site'=>'Сайт',
            'groups'=>'Группа',
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
     * поиск звонка по linkedid
     */
    public static function issetRepostByLinkedid($linkedid){
        if($linkedid){
            $row = YiiBase::app()->db->createCommand('SELECT id FROM tbl_report WHERE uniqueid=:uniqueid')->bindParam(':uniqueid', $linkedid, PDO::PARAM_STR)->queryRow();
            if(empty($row)){
                return false;
            }else{
                return true;
            }
        }

        return false;
    }


    /*
      * формируем сслыку на скачивание файла аудио-записи разговора
      */
    public function getLinkDownloadRec(){

        $link = '';

        $url_download = '/records2/'.date('Y/m/d/',strtotime($this->date_call)).$this->rec_call;

        if($this->rec_call && file_exists('/var/www/polzovatel/data/www/skz.seosoft.su'.$url_download)){

            $url_download = 'http://89.108.105.108/restapi/rec.php?rec='.urlencode('/var/spool/asterisk/monitor/'.date('Y/m/d/',strtotime($this->date_call)).$this->rec_call);

            $link  = CHtml::link('Скачать',$url_download, array("target"=>"_blank"));

//            $div = '<div id="'.uniqid('tbl_').'">
//                            <audio>
//                                <source src="'.$url_download.'" type="audio/x-wav" >
//                            </audio>
//                         </div>';
//
//            return $div;
        }

        return $link;
    }

    /*
       * экспорт данных в файл экспорта
       */
    //
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
        $h11 = iconv('utf-8', 'windows-1251//IGNORE','Сайт');
        $h12 = iconv('utf-8', 'windows-1251//IGNORE','Направление звонка');
        $h13 = iconv('utf-8', 'windows-1251//IGNORE','Статус обработки звонка');
        $h14 = iconv('utf-8', 'windows-1251//IGNORE','Менеджер звонка');
        $h15 = iconv('utf-8', 'windows-1251//IGNORE','Время ожидания клиента');
        $h16 = iconv('utf-8', 'windows-1251//IGNORE','Кол-во переадресаций');
        $h17 = iconv('utf-8', 'windows-1251//IGNORE','Цепочка пройденных переадресаций');
        $h19 = iconv('utf-8', 'windows-1251//IGNORE','Группа');
        $h18 = iconv('utf-8', 'windows-1251//IGNORE','Автоперезвон');


        $header = array($h1, $h2, $h3, $h4, $h5, $h6, $h7, $h8, $h9,  $h11, $h12, $h13, $h14, $h15, $h16, $h17, $h19,$h18);

        //создаём файл для экспорта, и с помощью ИТЕРАТОРА выбираем данные порциями и записываем их в файл, чтобы не было нихватки памяти по большой выборке данных
        $out = fopen($nameFile, 'w');

        //запишим заголовки столбцов
        fputcsv($out, $header,';');

        //теперь выборка через ИТЕРАТОР
        // выбирем с помощью ИТЕРАТОРа по 5000 записей
        $iterator=new CDataProviderIterator($dataProvider,1000);

        // обходим данные для каждой строки из логов
        foreach($iterator as $row){

            //$callOffice = mb_convert_encoding($row->callOffice, "windows-1251", "utf-8");
            $call_city = mb_convert_encoding($row->call_city, "windows-1251", "utf-8");
            $StatusToTbl = mb_convert_encoding($row->StatusToTbl, "windows-1251", "utf-8");
            $manager = mb_convert_encoding(($row->manager_call_id!=0)?$row->managerCall->fio:"", "windows-1251", "utf-8");
            $callbackstatus = mb_convert_encoding($row->callbackstatus, "windows-1251", "utf-8");
            $calldiraction = mb_convert_encoding($row->calldiraction, "windows-1251", "utf-8");
            $site = mb_convert_encoding($row->site, "windows-1251", "utf-8");

            $data =  array(
                $row->uniqueid,
                $row->caller_id,
                $row->did,
                $call_city,
                $row->date_call,
                $row->time_start_call,
                $row->time_end_call,
                $row->duration_call,
                $row->destination_call,
                $site,
                $calldiraction,
                $StatusToTbl,
                $manager,
                $row->waiting_time,
                $row->count_redirect,
                $row->chain_passed_redirects,
                $row->groups,//группа
                $callbackstatus,
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
            //получаем номер дня в неделе текущего дня
            //$day = date('N', time());
            //проверяем статус звонка, чтобы добавить его в очередь автоперезвона
            if($this->call_diraction==Report::INCOMING_CALL){//ТОЛЬКО ВХОДЯЩИЙ

                if(($this->status_call==Report::CALL_NO_ANSWER || $this->status_call==Report::CALL_RESET_CLIENT)){//&& ($day<6)

                    //установим статус у пропущенной заявки - как ждёт отправки заявки на перезвон
                    YiiBase::app()->db->createCommand('UPDATE {{report}} SET call_back_status="'.self::CALL_BACK_WAIT.'" WHERE id="'.$this->id.'"')->execute();

                    //есть список номеров исключений, которые мы исключаем из перезвона
                    $exeption_list = Yii::app()->params['call_back_exeption_list'];

                    //проверим номер в списке исключений+ если номер определился корректно
                    if(!in_array($this->caller_id,$exeption_list) && preg_match('/[0-9]{7,15}/',$this->caller_id)){

                        $call_back = new Callback();
                        $call_back->client_number = CallPhone::preparePhone($this->caller_id);
                        $call_back->call_date = date('Y-m-d H:i:s',strtotime($this->date_call.' '.$this->time_start_call));
                        $call_back->linkedid = $this->uniqueid;
                        $call_back->status = 2;
                        $call_back->site = $this->site;//укажем для какого сайта был звонок
                        if($call_back->validate()){
                            $call_back->save();
                        }else{
                            //echo '<pre>'; print_r($call_back->attributes);
                            echo '<pre>'; print_r($call_back->errors);
                        }
                    }

                }else{
                    //проверим статус ЗВОНКА,  удалим его из списка автоперезвонов(если он туда записан)
                    if($this->status_call==Report::CALL_ANSWERED){//если он отвечен,
                        //удалим его из списка автоперезвонов(если он туда записан)
                        YiiBase::app()->db->createCommand('DELETE FROM {{call_back}} WHERE client_number=:client_number')
                            ->bindValue(':client_number', CallPhone::preparePhone($this->caller_id), PDO::PARAM_STR)
                            ->execute();
                    }
                }
            }
            //исходящие звонки - проверка перезвона по звонку
            //проверим ИСХОДЯЩИЕ - звонки
            if($this->call_diraction==Report::OUTGOING_CALL){
                $this->callBackUpdateInfo();
            }
        }
    }

    /*
     * обновим информацию о результатах обратного звонка
     */
    public function callBackUpdateInfo(){

        $sql = 'SELECT linkedid, id FROM tbl_call_back WHERE client_number="'.CallPhone::preparePhone($this->did).'" ORDER BY id DESC';//AND status="'.Callback::SEND_CALL.'"

        $find_call = YiiBase::app()->db->createCommand($sql);

        $find_row = $find_call->queryRow();

        if($find_row){
            //привяжем звонок из перезвона со входящим пропущенным звонком от клиента
            //т.е. сперва найдём пропущенный звонок из раннее сделанных+ привяжем к нему инфу о текущем звонке и удалим из очереди заявку на перезвон
            $sql_update = 'UPDATE tbl_report SET call_back_linkdid=:call_back_linkdid,call_back_status=:call_back_status WHERE uniqueid=:uniqueid';
            $query_update = YiiBase::app()->db->createCommand($sql_update);
            $query_update->bindValue(':call_back_linkdid',$this->uniqueid, PDO::PARAM_STR);
            $query_update->bindValue(':call_back_status',self::CALL_BACK_ACTION_CLIENT, PDO::PARAM_INT);
            $query_update->bindValue(':uniqueid',$find_row['linkedid'], PDO::PARAM_INT);
            $query_update->execute();

            /*
             * обновим статус у пропущенных звонков за сегодня по данному номеру
             */
            $upd_sql = 'UPDATE tbl_report SET call_back_linkdid=:call_back_linkdid,call_back_status=:call_back_status WHERE caller_id=:caller_id AND date_call=:date_call';
            $query_update_ = YiiBase::app()->db->createCommand($upd_sql);
            $query_update_->bindValue(':call_back_linkdid',$this->uniqueid, PDO::PARAM_STR);
            $query_update_->bindValue(':call_back_status',self::CALL_BACK_ACTION_CLIENT, PDO::PARAM_INT);
            $query_update_->bindValue(':caller_id',CallPhone::preparePhone($this->did), PDO::PARAM_STR);
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
