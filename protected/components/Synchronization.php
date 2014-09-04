<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 09.06.14
 * Time: 14:37
 */

/*
 * синхронизация справочников+копирование новых данных из БД Астерикса
 */
class Synchronization {


    /*
     * запускаем синхронизацию справочников
     */
    public function run(){

        //синхронизируем справочник - ОФИСЫ(группа менеджеров)
        $this->catalogCategoryManager();

        //синхронизируем справочник менеджеров
        $this->catalogManager();

        //синхроинизируем список DID-номеров
        //$this->DidNumbers();
    }

    /*
     * снихроанизация српавочника ОФИСЫ(категория менеджеров)
     */
    public function catalogCategoryManager(){
        /*
         * выгребаем все офисы из БД Астерикса и при  необходимости создаём в текущей БД записи
         */
        //получаем список КОДОВ, по которым фильтруем записи
        $codes = OfficeManager::getCodeList();

        if(!empty($codes)){
            $filtersCode = CHtml::listData($codes, 'code', 'code');
            $sql = 'SELECT extension,descr FROM queues_config WHERE extension NOT IN('.implode(',', $filtersCode).')';
        }else{
            $sql = 'SELECT extension,descr FROM queues_config';
        }

        $rows = YiiBase::app()->db1->createCommand($sql)->queryAll();

        if(!empty($rows)){
            foreach($rows as $row){
                $model = new OfficeManager();
                $model->title = $row['descr'];
                $model->code = $row['extension'];
                $model->save();
            }
        }

    }

    /*
     * СИНХРОНИЗАЦИЯ менеджеров, подвязанных к категориям(офисам)
     * получаем список менеджеров с ФИО и кодами из БД-астерикса и обновляем ФИО в текущей системе, ФИО может меняться+могут быть новые значения
     */
    public function catalogManager(){

        //$codes = Manager::getCodeList();
        /*
        if(!empty($codes)){
            $filtersCode = CHtml::listData($codes, 'code', 'code');
            $sql = 'SELECT extension,name FROM users WHERE extension NOT IN('.implode(',', $filtersCode).')';
        }else{

        }*/

        $sql = 'SELECT extension,name FROM users';

        $rows = YiiBase::app()->db1->createCommand($sql)->queryAll();

        if(!empty($rows)){
            foreach($rows as $row){
                //Если не нашли менеджера - создадаим, если нашли - обновим ФИО
                $sql_find = 'SELECT * FROM {{manager}} WHERE code=:code';
                $row_manager =  YiiBase::app()->db->createCommand($sql_find)->bindValue(':code', $row['extension'], PDO::PARAM_STR)->queryRow();
                //не нашли менеджера - ДОБАВИМ
                if(empty($row_manager)){
                    $model = new Manager();
                    $model->fio = $row['name'];
                    $model->code = $row['extension'];
                    $model->save();
                }else{//нашли менеджера, обновим ФИО текущего менеджера
                    $sql_update = 'UPDATE {{manager}} SET fio=:fio WHERE code=:code';
                    $query_update = YiiBase::app()->db->createCommand($sql_update);
                    $query_update->bindValue(':fio', $row['name'], PDO::PARAM_STR);
                    $query_update->bindValue(':code', $row['extension'], PDO::PARAM_STR);
                    $query_update->execute();
                }
            }
        }
    }

    /*
     * синхронизируем список номеров(DID)
     * на которые клиенты звонят
     */
    public function DidNumbers(){

        //получаем список номеров(DID)
        $phonelist = PhoneRegions::getPhonesList();

        //используем список ДИДов для фильтрации и поиска новых значений, для добавления в справочник
        $filter_ = CHtml::listData($phonelist, 'phone', 'phone');

        $filter = array();
        foreach($filter_ as $phone){
            $filter[] = '"'.$phone.'"';
        }

        if(empty($filter)){
            //выборка из БД астерикса на поиск НОВЫХ значений ДИД
            $sql = 'SELECT extension, description FROM incoming';
        }else{
            //выборка из БД астерикса на поиск НОВЫХ значений ДИД, с учётом фильтра
            $sql = 'SELECT extension, description FROM incoming WHERE extension NOT IN('.implode(',', $filter).')';
        }

        //die($sql);
        $query = YiiBase::app()->db1->createCommand($sql);

        //$query->bindValue(':phone_list',implode(',', $filter), PDO::PARAM_STR);

        $rows = $query->queryAll();

        if(!empty($rows)){
            foreach($rows as $row){

                $model = new PhoneRegions();

                $model->phone = $row['extension'];

                if(empty($row['description'])){
                    $model->region = $row['extension'];
                }else{
                    $model->region = $row['description'];
                }

                $model->save();
            }
        }
    }
} 