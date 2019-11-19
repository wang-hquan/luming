<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class SpecialtyOverviewModel extends BaseModel
{
    protected $table = 'specialty_overview';

    protected  function formatWhere(array $where = null  ){
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