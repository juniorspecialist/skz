<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 23.06.14
 * Time: 14:31
 */
if(isset($listManagers)){
    if(!empty($listManagers)){

        $managers = CHtml::listData($managers, 'id', 'manager_id');

        foreach($listManagers as $manager){

            $id = $manager['id'];

            if(in_array($manager['id'], $managers)){
                echo $manager['code'].CHtml::checkBox('managers['.$id.']', true, array('style'=>'margin-left:10px;')).'<br>';
            }else{
                echo $manager['code'].CHtml::checkBox('managers['.$id.']', false, array('style'=>'margin-left:10px;')).'<br>';
            }
        }
    }
}