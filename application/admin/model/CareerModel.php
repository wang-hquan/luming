<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class CareerModel extends BaseModel
{
    protected $table = 'career';

    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'id':
                    case 'specialty_id':
                        $result[] = [$k,'=', $v];
                        break;
                }
            }
        }
        return $result;
    }

}