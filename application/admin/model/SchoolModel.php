<?php

namespace app\admin\model;

use app\admin\library\BaseModel;

class SchoolModel extends BaseModel
{
    protected $table = 'school';

    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'id':
                    case 'school_type_id':
                        $result[] = [$k,'=', $v];
                        break;
                    case 'city_id':
                        $result[] = [$k,'in', $v];
                        break;
                    case 'name':
                        $result[] = [$k,'like', '%'.$v.'%'];
                        break;
                }
            }
        }
        return $result;
    }
}