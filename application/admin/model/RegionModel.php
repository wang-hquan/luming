<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class RegionModel  extends BaseModel
{
    protected $table  = 'region';

    public  function formatWhere(array $where = null  ) {
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'status':
                        $result[] = [$k,'=', $v];
                        break;
                }
            }
        }
        return $result;
    }
}