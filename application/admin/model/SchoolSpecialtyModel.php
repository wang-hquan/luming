<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class SchoolSpecialtyModel extends BaseModel
{
    protected $table = 'school_specialty';

    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'school_id':
                    case 'academy_id':
                        $result[] = [$k,'=', $v];
                        break;
                }
            }
        }
        return $result;
    }
}