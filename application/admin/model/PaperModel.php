<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class PaperModel extends BaseModel
{
    protected $table = 'class';

    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'id':
                        $result[] = [$k,'=', $v];
                        break;
                }
            }
        }
        return $result;
    }
}