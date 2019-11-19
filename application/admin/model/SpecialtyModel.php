<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class SpecialtyModel extends BaseModel
{
    protected $table = 'specialty';

    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'id':
                        $result[] = [$k,'=', $v];
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