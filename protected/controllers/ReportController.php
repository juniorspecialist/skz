<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.07.14
 * Time: 15:47
 */

class ReportController extends Controller {
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

        if(!empty($model->site_id)){
            //$criteria->compare('site_id',$model->site_id);
            $criteria->condition = 'site_id='.$model->site_id;
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


        if ($model->call_diraction){
            $criteria->addCondition("call_diraction='".$model->call_diraction."'");
        }
        //фильтр по менеджеру
        if($model->manager_call_id){
            $criteria->addCondition("manager_call_id='".$model->manager_call_id."'");
        }

        //статус обработки Звонка
        if($model->status_call){
            $criteria->addCondition("status_call='".$model->status_call."'");
        }

        //фильтруем по офису
        if($model->office_call_id){
            $criteria->addCondition("office_call_id='".$model->office_call_id."'");
        }

        //фильтруем по статусу автоперезвона
        if($model->call_back_status){
            $criteria->addCondition("call_back_status='".$model->call_back_status."'");
        }

        //==========фильтрация по регулярному выражению или отрацание по регулярке============================

        //проверим и примениним - ФИЛЬТРАЦИЮ по идентификатор звонка(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_uniqueid']) && isset($_GET['search_word_accept_reg_uniqueid'])){
            if(!empty($_GET['radio_selected_uniqueid']) && !empty($_GET['search_word_accept_reg_uniqueid'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_uniqueid']=='search_word_accept_uniqueid'){//удовлетворяет регулярному выражению
                    //$criteria->addCondition("uniqueid REGEXP '".$_GET['search_word_accept_reg_uniqueid']."'");
                    $criteria->addCondition('uniqueid  REGEXP "'.$_GET['search_word_accept_reg_uniqueid'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    //$criteria->addCondition("uniqueid NOT REGEXP '".$_GET['search_word_accept_reg_uniqueid']."'");
                    $criteria->addCondition('uniqueid NOT  REGEXP "'.$_GET['search_word_accept_reg_uniqueid'].'"');
                }
                //$criteria->params = array(':uniqueid'=>$_GET['search_word_accept_reg_uniqueid']);
            }
        }

        //проверим и примениним - ФИЛЬТРАЦИЮ по Номер клиента(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_CallerId']) && isset($_GET['search_word_accept_reg_CallerId'])){
            if(!empty($_GET['radio_selected_CallerId']) && !empty($_GET['search_word_accept_reg_CallerId'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_CallerId']=='search_word_accept_CallerId'){//удовлетворяет регулярному выражению
                    //$criteria->addCondition("caller_id REGEXP '".$_GET['search_word_accept_reg_CallerId']."'");
                    $criteria->addCondition('caller_id  REGEXP "'.$_GET['search_word_accept_reg_CallerId'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    //$criteria->addCondition("caller_id NOT REGEXP '".$_GET['search_word_accept_reg_CallerId']."'");
                    $criteria->addCondition('caller_id NOT REGEXP "'.$_GET['search_word_accept_reg_CallerId'].'"');
                }
                //$criteria->params = array(':caller_id'=>$_GET['search_word_accept_reg_CallerId']);
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по DID(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_Did']) && isset($_GET['search_word_accept_reg_Did'])){
            if(!empty($_GET['radio_selected_Did']) && !empty($_GET['search_word_accept_reg_Did'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_Did']=='search_word_accept_Did'){//удовлетворяет регулярному выражению
                    //$criteria->addCondition("did REGEXP '".$_GET['search_word_accept_reg_Did']."'");
                    $criteria->addCondition('did REGEXP "'.$_GET['search_word_accept_reg_Did'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    //$criteria->addCondition("did NOT REGEXP '".$_GET['search_word_accept_reg_Did']."'");
                    $criteria->addCondition('did NOT REGEXP "'.$_GET['search_word_accept_reg_Did'].'"');
                }
                //$criteria->params = array(':did'=>$_GET['search_word_accept_reg_Did']);
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по call_city(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_call_city']) && isset($_GET['search_word_accept_reg_call_city'])){
            if(!empty($_GET['radio_selected_call_city']) && !empty($_GET['search_word_accept_reg_call_city'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_call_city']=='search_word_accept_call_city'){//удовлетворяет регулярному выражению
                    //$criteria->addCondition(" call_city REGEXP '".$_GET['search_word_accept_reg_call_city']."'");
                    $criteria->addCondition('call_city REGEXP "'.$_GET['search_word_accept_reg_call_city'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    //$criteria->addCondition(" call_city NOT REGEXP '".$_GET['search_word_accept_reg_call_city']."'");
                    $criteria->addCondition('call_city NOT REGEXP "'.$_GET['search_word_accept_reg_call_city'].'"');
                }
                //$criteria->params = array(':call_city'=>$_GET['search_word_accept_reg_call_city']);
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по Destination(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_dest']) && isset($_GET['search_word_accept_reg_dest'])){
            if(!empty($_GET['radio_selected_dest']) && !empty($_GET['search_word_accept_reg_dest'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_dest']=='search_word_accept_dest'){//удовлетворяет регулярному выражению
                    //$criteria->addCondition("destination_call REGEXP '".$_GET['search_word_accept_reg_dest']."'");
                    $criteria->addCondition('destination_call REGEXP "'.$_GET['search_word_accept_reg_dest'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    //$criteria->addCondition("destination_call NOT REGEXP '".$_GET['search_word_accept_reg_dest']."'");
                    $criteria->addCondition('destination_call NOT REGEXP "'.$_GET['search_word_accept_reg_dest'].'"');
                }
                //$criteria->params = array(':destination_call'=>$_GET['search_word_accept_reg_dest']);
            }
        }
        //проверим и примениним - ФИЛЬТРАЦИЮ по Цепочка пройденных переадресаций(РЕГУЛЯРКА)
        if(isset($_GET['radio_selected_redirect']) && isset($_GET['search_word_accept_reg_redirect'])){
            if(!empty($_GET['radio_selected_redirect']) && !empty($_GET['search_word_accept_reg_redirect'])){
                //2 типа удовлетворяет регулярке или нет по регулярке
                if($_GET['radio_selected_redirect']=='search_word_accept_redirect'){//удовлетворяет регулярному выражению
                    //$criteria->addCondition("chain_passed_redirects REGEXP ".$_GET["search_word_accept_reg_redirect"])."";
                    //$criteria->addCondition("chain_passed_redirects REGEXP ".$_GET["search_word_accept_reg_redirect"])."";
                    $criteria->addCondition('chain_passed_redirects REGEXP "'.$_GET['search_word_accept_reg_redirect'].'"');
                }else{
                    //не удовлетворяет регулярному выражению
                    //$criteria->addCondition("chain_passed_redirects NOT REGEXP '".$_GET["search_word_accept_reg_redirect"]."'");
                    $criteria->addCondition('chain_passed_redirects NOT REGEXP "'.$_GET['search_word_accept_reg_redirect'].'"');
                }
                //$criteria->params = array(':chain_passed_redirects'=>$_GET["search_word_accept_reg_redirect"]);
            }
        }

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


    public function actionExport(){

        //Yii::import('ext.ECSVExport', true);

        $model=new Report('search');

        $model->unsetAttributes();  // clear any default values

        if(isset($_GET['Report'])){
            $model->attributes=$_GET['Report'];
        }

        $criteria=new CDbCriteria;
        $criteria->compare('stat_phrases_accounts_id',$model->stat_phrases_accounts_id);

        $dataProvider = new CActiveDataProvider('StatisticsParsing', array(
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
        if(isset($_POST['ContactForm']))
        {
            $model->attributes=$_POST['ContactForm'];
            if($model->validate())
            {
                $name='=?UTF-8?B?'.base64_encode($model->name).'?=';
                $subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
                $headers="From: $name <{$model->email}>\r\n".
                    "Reply-To: {$model->email}\r\n".
                    "MIME-Version: 1.0\r\n".
                    "Content-Type: text/plain; charset=UTF-8";

                mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
                Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
                $this->refresh();
            }
        }
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

} 