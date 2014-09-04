<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.09.14
 * Time: 15:07
 */

Yii::import('zii.widgets.grid.CGridView');

class GridView extends CGridView {

    public $distinct_caller_phone = 0;//кол-во уникальных номеров в таблице вывода данных


    /**
     * Renders the summary text.
     */
    public function renderSummary()
    {
        if(($count=$this->dataProvider->getItemCount())<=0)
            return;

        echo '<div class="'.$this->summaryCssClass.'">';
        if($this->enablePagination)
        {
            $pagination=$this->dataProvider->getPagination();
            $total=$this->dataProvider->getTotalItemCount();
            $start=$pagination->currentPage*$pagination->pageSize+1;
            $end=$start+$count-1;
            if($end>$total)
            {
                $end=$total;
                $start=$end-$count+1;
            }
            if(($summaryText=$this->summaryText)===null)
                $summaryText=Yii::t('zii','Displaying {start}-{end} of 1 result.|Displaying {start}-{end} of {count} results.',$total);
            echo strtr($summaryText.'Уникальных контактов:{distinct_caller}',array(
                '{start}'=>$start,
                '{end}'=>$end,
                '{count}'=>$total,
                '{page}'=>$pagination->currentPage+1,
                '{pages}'=>$pagination->pageCount,
                '{distinct_caller}'=>$this->distinct_caller_phone,
            ));
        }
        else
        {
            if(($summaryText=$this->summaryText)===null)
                $summaryText=Yii::t('zii','Total 1 result.|Total {count} results.',$count);
            echo strtr($summaryText.'Уникальных контактов:{distinct_caller}',array(
                '{count}'=>$count,
                '{start}'=>1,
                '{end}'=>$count,
                '{page}'=>1,
                '{pages}'=>1,
                '{distinct_caller}'=>$this->distinct_caller_phone,
            ));
        }
        echo '</div>';
    }
} 