<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class SchoolScoreModel extends BaseModel
{
    protected $table = 'school_score';

    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'school_id':
                        $result[] = [$k,'=', $v];
                        break;
                }
            }
        }
        return $result;
    }
}