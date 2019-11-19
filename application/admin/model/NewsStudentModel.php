<?php


namespace app\admin\model;

use app\admin\library\BaseModel;

class NewsStudentModel extends BaseModel
{
    protected $table = 'news_student';

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