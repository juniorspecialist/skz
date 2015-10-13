<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.07.14
 * Time: 15:47
 */

class ReportController extends AuthController {


    public $post_count;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
//    public function accessRules()
//    {
//        return array(
//            array('allow',  // allow all users to perform 'index' and 'view' actions
//                'actions'=>array('index','view'),
//                'users'=>array('*'),
//            ),
//            array('allow', // allow authenticated user to perform 'create' and 'update' actions
//                'actions'=>array('create','update'),
//                'users'=>array('@'),
//            ),
//            array('allow', // allow admin user to perform 'admin' and 'delete' actions
//                'actions'=>array('admin','delete'),
//                'users'=>array('admin'),
//            ),
//            array('deny',  // deny all users
//                'users'=>array('*'),
//            ),
//        );
//    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        $model=new Report('search');

        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['Report'])){$model->attributes=$_GET['Report'];}

        $criteria=new CDbCriteria;

        //echo '<pre>'; print_r($model->attributes); die();

        if(isset($model->manager_call_id) && !empty($model->manager_call_id)){
            $criteria->addCondition("manager_call_id='".$model->manager_call_id."'");
        }

        if(!empty($model->site)){
            //$criteria->compare('site_id',$model->site_id);
            //$criteria->condition = 'site="'.$model->site_id.'"';
            $criteria->addCondition("site='".$model->site."'");
        }

        //фильтрация - Кол-во переадресаций(интервал)
        if(isset($_GET['CountRedirect_from'])){
            if(!empty($_GET['CountRedirect_from'])){
                $criteria->addCondition("count_redirect>='".$_GET['CountRedirect_from']."'");
            }
        }
        if(isset($_GET['CountRedirect_to'])){
            if(!empty($_GET['CountRedirect_to'])){
                $criteria->addCondition("count_redirect<='".$_GET['CountRedirect_to']."'");
            }
        }

        //фильтрация - Время ожидаения(интервал)
        if(isset($_GET['TimeWait_from'])){
            if(!empty($_GET['TimeWait_from'])){
                $criteria->addCondition("waiting_time>='".$_GET['TimeWait_from']."'");
            }
        }
        if(isset($_GET['TimeWait_to'])){
            if(!empty($_GET['TimeWait_to'])){
                $criteria->addCondition("waiting_time<='".$_GET['TimeWait_to']."'");
            }
        }

        //фильтрация по "Продолжительность звонка"
        if(isset($_GET['DurationCallCall_from'])){
            if(!empty($_GET['DurationCallCall_from'])){
                $criteria->addCondition("duration_call>='".$_GET['DurationCallCall_from']."'");
            }
        }
        if(isset($_GET['DurationCallCall_to'])){
            if(!empty($_GET['DurationCallCall_to'])){
                $criteria->addCondition("duration_call<='".$_GET['DurationCallCall_to']."'");
            }
        }

        //фильитрация по Время конца разгоаора
        if(isset($_GET['TimeEndCall_from'])){
            if(!empty($_GET['TimeEndCall_from'])){
                $criteria->addCondition("time_end_call>='".$_GET['TimeEndCall_from']."'");
            }
        }
        if(isset($_GET['TimeEndCall_to'])){
            if(!empty($_GET['TimeEndCall_to'])){
                $criteria->addCondition("time_end_call<='".$_GET['TimeEndCall_to']."'");
            }
        }

        //фильитрация по Время начала разгоаора  TimeStartCall_from
        if(isset($_GET['TimeStartCall_from'])){
            if(!empty($_GET['TimeStartCall_from'])){
                $criteria->addCondition("time_start_call>='".$_GET['TimeStartCall_from']."'");
            }
        }
        if(isset($_GET['TimeStartCall_to'])){
            if(!empty($_GET['TimeStartCall_to'])){
                $criteria->addCondition("time_start_call<='".$_GET['TimeStartCall_to']."'");
            }
        }

        //фильтрацию по интервалу - Дата звонка
        if(isset($_GET['DateCall_from'])){
            if(!empty($_GET['DateCall_from'])){
                $criteria->addCondition("date_call>='".$_GET['DateCall_from']."'");

            }
        }


        if(isset($_GET['DateCall_to'])){
            if(!empty($_GET['DateCall_to'])){
                $criteria->addCondition("date_call<='".$_GET['DateCall_to']."'");
            }
        }


        if ($model->call_diraction){$criteria->addCondition("call_diraction='".$model->call_diraction."'");}
        //фильтр по группе
        if($model->groups){$criteria->addCondition("t.groups='".$model->groups."'");}

        //статус обработки Звонка
        if($model->status_call){$criteria->addCondition("status_call='".$model->status_call."'");}

        //фильтрация по пользователю
        $criteria->addCondition("user_id='".YiiBase::app()->user->id."'");

        //фильтруем по статусу автоперезвона
        if($model->call_back_status){$criteria->addCondition("call_back_status='".$model->call_back_status."'");}

        //==========фильтрация по регулярному выражению или отрацание по регулярке============================
        //проверим и примениним - ФИЛЬТРАЦИЮ по идентификатор звонка(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_uniqueid']) && isset($_GET['search_word_accept_reg_uniqueid'])){
            if(!empty($_GET['radio_selected_uniqueid']) && !empty($_GET['search_word_accept_reg_uniqueid'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_uniqueid']=='search_word_accept_uniqueid'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('uniqueid  REGEXP "'.$_GET['search_word_accept_reg_uniqueid'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('uniqueid NOT  REGEXP "'.$_GET['search_word_accept_reg_uniqueid'].'"');
                }
            }
        }

        //проверим и примениним - ФИЛЬТРАЦИЮ по Номер клиента(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_CallerId']) && isset($_GET['search_word_accept_reg_CallerId'])){
            if(!empty($_GET['radio_selected_CallerId']) && !empty($_GET['search_word_accept_reg_CallerId'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_CallerId']=='search_word_accept_CallerId'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('caller_id  REGEXP "'.$_GET['search_word_accept_reg_CallerId'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('caller_id NOT REGEXP "'.$_GET['search_word_accept_reg_CallerId'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по DID(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_Did']) && isset($_GET['search_word_accept_reg_Did'])){
            if(!empty($_GET['radio_selected_Did']) && !empty($_GET['search_word_accept_reg_Did'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_Did']=='search_word_accept_Did'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('did REGEXP "'.$_GET['search_word_accept_reg_Did'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('did NOT REGEXP "'.$_GET['search_word_accept_reg_Did'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по call_city(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_call_city']) && isset($_GET['search_word_accept_reg_call_city'])){
            if(!empty($_GET['radio_selected_call_city']) && !empty($_GET['search_word_accept_reg_call_city'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_call_city']=='search_word_accept_call_city'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('call_city REGEXP "'.$_GET['search_word_accept_reg_call_city'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('call_city NOT REGEXP "'.$_GET['search_word_accept_reg_call_city'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по Destination(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_dest']) && isset($_GET['search_word_accept_reg_dest'])){
            if(!empty($_GET['radio_selected_dest']) && !empty($_GET['search_word_accept_reg_dest'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_dest']=='search_word_accept_dest'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('destination_call REGEXP "'.$_GET['search_word_accept_reg_dest'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('destination_call NOT REGEXP "'.$_GET['search_word_accept_reg_dest'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по Цепочка пройденных переадресаций(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_redirect']) && isset($_GET['search_word_accept_reg_redirect'])){
            if(!empty($_GET['radio_selected_redirect']) && !empty($_GET['search_word_accept_reg_redirect'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_redirect']=='search_word_accept_redirect'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('chain_passed_redirects REGEXP "'.$_GET['search_word_accept_reg_redirect'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('chain_passed_redirects NOT REGEXP "'.$_GET['search_word_accept_reg_redirect'].'"');
                }
            }
        }
        http://skz.seosoft.su/report/index?DateCall_from=2015-02-13&DateCall_to=&Report%5Buniqueid%5D=&Report%5Bcaller_id%5D=&Report%5Bdid%5D=&Report%5Bcall_city%5D=&Report%5Bdate_call%5D=&Report%5Bdestination_call%5D=&Report%5Bsite%5D=&Report%5Bcall_diraction%5D=&Report%5Bstatus_call%5D=&Report%5Bmanager_call_id%5D=10&Report%5Bchain_passed_redirects%5D=&Report%5Bgroups%5D=&Report%5Bcall_back_status%5D=&Report_page=1&Report_sort=time_start_call.desc&radio_selected_Did=search_word_accept_Did&search_word_accept_reg_Did=

        //============================SUB QUERY======================================
        $criteria1=new CDbCriteria();
        $criteria1->mergeWith($criteria);
        $criteria1->select='count(DISTINCT(caller_id))';
        $subQuery = $model->getCommandBuilder()->createFindCommand($model->getTableSchema(),$criteria1)->getText();

        $model->cnt = YiiBase::app()->db->createCommand($subQuery)->queryScalar();

        //возможно хотим экспортнуть данные в файл
        if(isset($_GET['export'])){
            if($_GET['export']==1){

                $dataProvider = new CActiveDataProvider('Report', array(
                    'criteria'=>$criteria,
                ));

                //начинаем экспорт выбранных данных в файл
                $model->exportToFile($dataProvider, 'export.csv');

                Yii::app()->getRequest()->sendFile('export.csv', str_replace('"', '',file_get_contents('export.csv')), "text/csv", false);

                unlink('export.csv');

            }
        }


        $dataProvider = new CActiveDataProvider('Report', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>50,
            ),
            //'sort' => array('attributes' => array('uniqueid', 'caller_id', 'val')),
        ));


        $this->render('report',array(
            'model'=>$model,
            'dataProvider'=>$dataProvider,
        ));
    }


    /*
     * Таблица контроля номеров в СКЗ
     * https://seomanager.bitrix24.ru/company/personal/user/9/tasks/task/view/6935/
     * Для мониторинга работоспособности номеров нужна отдельная страница в СКЗ с таблицей вида как в аттаче.
        В таблице - список всех известных СКЗ номеров в 1 столбце.
        Во в столбце по каждому из номеров кол-во поступивших звонков за период времени.
        В третьем столбце кол-во отвеченных за период времени
        В 4 столбце сайт которому принадлежит номер.

        Можно в отдельном окне задавать период для которого считаются данные.
        Сортировки и фильтрация - как в аналогичных столбцах СКЗ.
     */
    public function actionControl(){

/*
 * SELECT
( SELECT COUNT(tbl1.id) FROM tbl_report AS tbl1 WHERE tbl1.call_diraction=1 AND tbl1.did!="" AND tbl1.did=tbl2.did) as count,
( SELECT COUNT(tbl3.id) FROM tbl_report AS tbl3 WHERE tbl3.call_diraction=1 AND tbl3.did!="" AND tbl3.did=tbl2.did AND tbl3.status_call=1) as countAnswer,
tbl2.did
FROM `tbl_report` as tbl2
WHERE tbl2.call_diraction=1 AND tbl2.did!=""
GROUP BY tbl2.did
HAVING (COUNT(tbl2.did)>1)
 */

    }

    public function actionExport(){

        //Yii::import('ext.ECSVExport', true);

        $model=new Report('search');

        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['Report'])){
            $model->attributes=$_GET['Report'];
        }

        $criteria=new CDbCriteria;
        $criteria->compare('stat_phrases_accounts_id',$model->stat_phrases_accounts_id);

        $dataProvider = new CActiveDataProvider('Report', array(
            'criteria'=>$criteria,
        ));


        $model->exportToFile($dataProvider, 'export.csv');

        Yii::app()->getRequest()->sendFile('export.csv', str_replace('"', '',file_get_contents('export.csv')), "text/csv", false);

        unlink('export.csv');

        YiiBase::app()->end();
    }
    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if($error=Yii::app()->errorHandler->error)
        {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the contact page
     */
    public function actionContact()
    {
       $model=new ContactForm;
//        if(isset($_POST['ContactForm']))
//        {
//            $model->attributes=$_POST['ContactForm'];
//            if($model->validate())
//            {
//                $name='=?UTF-8?B?'.base64_encode($model->name).'?=';
//                $subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
//                $headers="From: $name <{$model->email}>\r\n".
//                    "Reply-To: {$model->email}\r\n".
//                    "MIME-Version: 1.0\r\n".
//                    "Content-Type: text/plain; charset=UTF-8";
//
//                mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
//                Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
//                $this->refresh();
//            }
//        }
        $this->render('contact',array('model'=>$model));
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        $model=new LoginForm;

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login())
                $this->redirect(Yii::app()->user->returnUrl);
        }
        // display the login form
        $this->render('login',array('model'=>$model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }


    /*
     * формируем отчёт за кол-во звонков по указанный период времени по всему списку номеров
     * https://seomanager.bitrix24.ru/company/personal/user/9/tasks/task/view/9831/
     */
    public function actionCountcall(){

        $model=new PhoneRegions('search');

        $total_sum = 0;//сумарное кол-во знонков

        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['PhoneRegions'])){    $model->attributes=$_GET['PhoneRegions'];   }

        $criteria = new CDbCriteria;

        $where = '';
        //фильтрацию по интервалу - Дата звонка
        if(isset($_GET['DateCall_from'])){
            if(!empty($_GET['DateCall_from'])){
                $where = ' AND '.Report::model()->tableName().".date_call>='".$_GET['DateCall_from']."'";
            }
        }else{
            $where = ' AND '.Report::model()->tableName().".date_call>='".date('Y-m-d')."'";
        }



        $where.=" AND ".Report::model()->tableName().".user_id='".YiiBase::app()->user->id."'";



        if(isset($_GET['DateCall_to'])){
            if(!empty($_GET['DateCall_to'])){
                $where.=" AND ".Report::model()->tableName().".date_call<='".$_GET['DateCall_to']."'";
            }
        }else{
            $where.=" AND ".Report::model()->tableName().".date_call<='".date('Y-m-d')."'";
        }


        //если указали список городов в качестве фильтра-учитываем
        if(isset($_GET['PhoneRegions']['siteArray'])){
            if(!empty($_GET['PhoneRegions']['siteArray'])){
                $where.=' AND '.Report::model()->tableName().'.site IN("'.implode('","', $_GET['PhoneRegions']['siteArray']).'")';
            }
        }
        //если указали список городов в качестве фильтра-учитываем
        if(isset($_GET['PhoneRegions']['regionArray'])){
            if(!empty($_GET['PhoneRegions']['regionArray'])){
                $where.=' AND '.Report::model()->tableName().'. call_city IN("'.implode('","', $_GET['PhoneRegions']['regionArray']).'")';
            }
        }


        // sub query to retrieve the count of posts
        $report_table = Report::model()->tableName();
        $report_count_sql = "(select count(*) from $report_table  where $report_table.call_diraction=1 AND  $report_table.did = t.phone $where )";

        // select
        $criteria->select = array(
            '*',
            $report_count_sql . " as report_count",
        );

        //формируем выборку данных сгруппированную по сайтам, а не по телефонам, со всеми фильтрами
        $visual_count_sql = "(select count(*) as count, site,`date_call`  from $report_table  where $report_table.call_diraction=1  $where  GROUP BY DAY(date_call), site ORDER BY date_call)";

        $criteria_visual = new CDbCriteria;
        // select
        $criteria_visual->select = array(
            //'DAY(`date_call`) AS `DAY`',
            $visual_count_sql . " as report_count",
        );

        $subQuery = $model->getCommandBuilder()->createFindCommand($model->getTableSchema(),$criteria_visual)->getText();

        $data_visual = YiiBase::app()->db->createCommand($visual_count_sql)->queryAll();

        $date_interval = array();
        foreach($data_visual as $data){
            $date_interval[$data['date_call']] = $data['date_call'];
        }
        //[data,count_site1, count_site2, count_site3]

        $sites = $model->getSiteList();

        $total_data_array = array();

        foreach($date_interval as $date){
            $list_site_data = array();
            $list_site_data[] = '"'.date('Y_m_d',strtotime($date)).'"';
            foreach($sites as $site){
                $count = 0;
                foreach($data_visual as $data_info){
                    if($data_info['site']==$site['site'] && $date==$data_info['date_call']){
                        $count = $data_info['count'];
                    }
                }
                $list_site_data[] = $count;
            }
            $total_data_array[] = $list_site_data;
        }

        // where
        //$criteria->compare($report_count_sql, $this->post_count);

        $dataProvider = new CActiveDataProvider('PhoneRegions', array(
            'criteria'=>$criteria,
            'sort' => array(
                'defaultOrder' => 't.phone',
                'attributes' => array(
                // order by
                'report_count' => array(
                    'asc' => 'report_count ASC',
                    'desc' => 'report_count DESC',
                ),
                    '*',
                ),
            ),
            'pagination'=>false,//array('pageSize'=>50,)
        ));


        foreach($dataProvider->data as $attribute){
            $total_sum=$total_sum+$attribute['report_count'];
        }


        $this->render('countcall',array(
            'model'=>$model,
            'dataProvider'=>$dataProvider,
            'total_sum'=>$total_sum,
            'total_data_array'=>$total_data_array,
        ));
    }


    /*
     *перезвоны по пропущенным звонкам за выбранный интервал
     * получаем список пропущенных звонков за выбранный интервал, а потом по этим номерам находим список исходящих звонков за этот же интервал
     */
    public function actionMissed(){

        $model=new Report('search');

        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['Report'])){$model->attributes=$_GET['Report'];}


        //получаем список пропущенных звонков за интервал времени
        //фильтрацию по интервалу - Дата звонка
        if(isset($_GET['DateCall_from'])){
            if(!empty($_GET['DateCall_from'])){
                $where = ' AND '.Report::model()->tableName().".date_call>='".$_GET['DateCall_from']."'";
            }
        }else{
            //если не указан интервал используем начало недели
            //echo date('d.m.Y', strtotime('Mon this week')) . '—' . date('d.m.Y', strtotime('Sun this week'));
            $where = ' AND '.Report::model()->tableName().".date_call>='".date('Y-m-d', strtotime('Mon this week'))."'";
        }


        if(isset($_GET['DateCall_to'])){
            if(!empty($_GET['DateCall_to'])){
                //$criteria->addCondition("date_call<='".$_GET['DateCall_to']."'");
                $where.=" AND ".Report::model()->tableName().".date_call<='".$_GET['DateCall_to']."'";
            }
        }else{
            //если не указано, значит используем конец недели дату
            $where.=" AND ".Report::model()->tableName().".date_call<='".date('Y-m-d', strtotime('Sun this week'))."'";
        }

        $where.=" AND ".Report::model()->tableName().".user_id='".YiiBase::app()->user->id."'";

        $missed_calls_query = YiiBase::app()->db->createCommand('SELECT caller_id FROM tbl_report WHERE status_call!=:answer AND call_diraction=:incoming'. $where);
        $missed_calls_query->bindValue(':answer', Report::CALL_ANSWERED, PDO::PARAM_INT);
        $missed_calls_query->bindValue(':incoming', Report::INCOMING_CALL, PDO::PARAM_INT);
        $missed_calls_rows = $missed_calls_query->queryAll();

        $filter = array();

        foreach($missed_calls_rows as $filter_phone){
            $filter[] = $filter_phone['caller_id'];
        }


        $criteria=new CDbCriteria;

        $criteria->addInCondition('did', $filter, 'OR');

        if(isset($model->manager_call_id) && !empty($model->manager_call_id)){
            $criteria->addCondition("manager_call_id='".$model->manager_call_id."'");
        }

        if(!empty($model->site)){ $criteria->addCondition("site='".$model->site."'");}

        //фильтрация - Кол-во переадресаций(интервал)
        if(isset($_GET['CountRedirect_from'])){
            if(!empty($_GET['CountRedirect_from'])){
                $criteria->addCondition("count_redirect>='".$_GET['CountRedirect_from']."'");
            }
        }
        if(isset($_GET['CountRedirect_to'])){
            if(!empty($_GET['CountRedirect_to'])){
                $criteria->addCondition("count_redirect<='".$_GET['CountRedirect_to']."'");
            }
        }

        //фильтрация - Время ожидаения(интервал)
        if(isset($_GET['TimeWait_from'])){
            if(!empty($_GET['TimeWait_from'])){
                $criteria->addCondition("waiting_time>='".$_GET['TimeWait_from']."'");
            }
        }
        if(isset($_GET['TimeWait_to'])){
            if(!empty($_GET['TimeWait_to'])){
                $criteria->addCondition("waiting_time<='".$_GET['TimeWait_to']."'");
            }
        }

        //фильтрация по "Продолжительность звонка"
        if(isset($_GET['DurationCallCall_from'])){
            if(!empty($_GET['DurationCallCall_from'])){
                $criteria->addCondition("duration_call>='".$_GET['DurationCallCall_from']."'");
            }
        }
        if(isset($_GET['DurationCallCall_to'])){
            if(!empty($_GET['DurationCallCall_to'])){
                $criteria->addCondition("duration_call<='".$_GET['DurationCallCall_to']."'");
            }
        }

        //фильитрация по Время конца разгоаора
        if(isset($_GET['TimeEndCall_from'])){
            if(!empty($_GET['TimeEndCall_from'])){
                $criteria->addCondition("time_end_call>='".$_GET['TimeEndCall_from']."'");
            }
        }
        if(isset($_GET['TimeEndCall_to'])){
            if(!empty($_GET['TimeEndCall_to'])){
                $criteria->addCondition("time_end_call<='".$_GET['TimeEndCall_to']."'");
            }
        }

        //фильитрация по Время начала разгоаора  TimeStartCall_from
        if(isset($_GET['TimeStartCall_from'])){
            if(!empty($_GET['TimeStartCall_from'])){
                $criteria->addCondition("time_start_call>='".$_GET['TimeStartCall_from']."'");
            }
        }
        if(isset($_GET['TimeStartCall_to'])){
            if(!empty($_GET['TimeStartCall_to'])){
                $criteria->addCondition("time_start_call<='".$_GET['TimeStartCall_to']."'");
            }
        }

        //фильтрацию по интервалу - Дата звонка
        if(isset($_GET['DateCall_from'])){
            if(!empty($_GET['DateCall_from'])){
                $criteria->addCondition("date_call>='".$_GET['DateCall_from']."'");

            }
        }else{
            //если не указан интервал используем начало недели
            $criteria->addCondition("date_call>='".date('Y-m-d', strtotime('Mon this week'))."'");
        }


        if(isset($_GET['DateCall_to'])){
            if(!empty($_GET['DateCall_to'])){
                $criteria->addCondition("date_call<='".$_GET['DateCall_to']."'");
            }
        }else{
            //если не указано, значит используем конец недели дату
            $criteria->addCondition("date_call<='".date('Y-m-d', strtotime('Sun this week'))."'");
        }


        if ($model->call_diraction){$criteria->addCondition("call_diraction='".$model->call_diraction."'");}
        //фильтр по группе
        if($model->groups){$criteria->addCondition("t.groups='".$model->groups."'");}

        //статус обработки Звонка
        if($model->status_call){$criteria->addCondition("status_call='".$model->status_call."'");}


        //фильтруем по статусу автоперезвона
        if($model->call_back_status){$criteria->addCondition("call_back_status='".$model->call_back_status."'");}


        //выборка по пользователю - текущему
        $criteria->addCondition("user_id='".YiiBase::app()->user->id."'");

        //==========фильтрация по регулярному выражению или отрацание по регулярке============================
        //проверим и примениним - ФИЛЬТРАЦИЮ по идентификатор звонка(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_uniqueid']) && isset($_GET['search_word_accept_reg_uniqueid'])){
            if(!empty($_GET['radio_selected_uniqueid']) && !empty($_GET['search_word_accept_reg_uniqueid'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_uniqueid']=='search_word_accept_uniqueid'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('uniqueid  REGEXP "'.$_GET['search_word_accept_reg_uniqueid'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('uniqueid NOT  REGEXP "'.$_GET['search_word_accept_reg_uniqueid'].'"');
                }
            }
        }

        //проверим и примениним - ФИЛЬТРАЦИЮ по Номер клиента(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_CallerId']) && isset($_GET['search_word_accept_reg_CallerId'])){
            if(!empty($_GET['radio_selected_CallerId']) && !empty($_GET['search_word_accept_reg_CallerId'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_CallerId']=='search_word_accept_CallerId'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('caller_id  REGEXP "'.$_GET['search_word_accept_reg_CallerId'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('caller_id NOT REGEXP "'.$_GET['search_word_accept_reg_CallerId'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по DID(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_Did']) && isset($_GET['search_word_accept_reg_Did'])){
            if(!empty($_GET['radio_selected_Did']) && !empty($_GET['search_word_accept_reg_Did'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_Did']=='search_word_accept_Did'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('did REGEXP "'.$_GET['search_word_accept_reg_Did'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('did NOT REGEXP "'.$_GET['search_word_accept_reg_Did'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по call_city(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_call_city']) && isset($_GET['search_word_accept_reg_call_city'])){
            if(!empty($_GET['radio_selected_call_city']) && !empty($_GET['search_word_accept_reg_call_city'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_call_city']=='search_word_accept_call_city'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('call_city REGEXP "'.$_GET['search_word_accept_reg_call_city'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('call_city NOT REGEXP "'.$_GET['search_word_accept_reg_call_city'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по Destination(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_dest']) && isset($_GET['search_word_accept_reg_dest'])){
            if(!empty($_GET['radio_selected_dest']) && !empty($_GET['search_word_accept_reg_dest'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_dest']=='search_word_accept_dest'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('destination_call REGEXP "'.$_GET['search_word_accept_reg_dest'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('destination_call NOT REGEXP "'.$_GET['search_word_accept_reg_dest'].'"');
                }
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по Цепочка пройденных переадресаций(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_redirect']) && isset($_GET['search_word_accept_reg_redirect'])){
            if(!empty($_GET['radio_selected_redirect']) && !empty($_GET['search_word_accept_reg_redirect'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_redirect']=='search_word_accept_redirect'){//удовлетворяет регулярному выражению
                    $criteria->addCondition('chain_passed_redirects REGEXP "'.$_GET['search_word_accept_reg_redirect'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    $criteria->addCondition('chain_passed_redirects NOT REGEXP "'.$_GET['search_word_accept_reg_redirect'].'"');
                }
            }
        }
        http://skz.seosoft.su/report/index?DateCall_from=2015-02-13&DateCall_to=&Report%5Buniqueid%5D=&Report%5Bcaller_id%5D=&Report%5Bdid%5D=&Report%5Bcall_city%5D=&Report%5Bdate_call%5D=&Report%5Bdestination_call%5D=&Report%5Bsite%5D=&Report%5Bcall_diraction%5D=&Report%5Bstatus_call%5D=&Report%5Bmanager_call_id%5D=10&Report%5Bchain_passed_redirects%5D=&Report%5Bgroups%5D=&Report%5Bcall_back_status%5D=&Report_page=1&Report_sort=time_start_call.desc&radio_selected_Did=search_word_accept_Did&search_word_accept_reg_Did=

        //============================SUB QUERY======================================
//        $criteria1=new CDbCriteria();
//        $criteria1->mergeWith($criteria);
//        $criteria1->select='count(DISTINCT(caller_id))';
//        $subQuery = $model->getCommandBuilder()->createFindCommand($model->getTableSchema(),$criteria1)->getText();

        //$model->cnt = YiiBase::app()->db->createCommand($subQuery)->queryScalar();

        $dataProvider = new CActiveDataProvider('Report', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>50,
            ),
            //'sort' => array('attributes' => array('uniqueid', 'caller_id', 'val')),
        ));


        $this->render('missed',array(
            'model'=>$model,
            'dataProvider'=>$dataProvider,
        ));
    }
} 