<?php


namespace app\admin\model;


use app\admin\library\BaseModel;
use think\Db;

class OrderModel extends BaseModel
{
    protected $table = 'order';

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

    public function profile()
    {
        return $this->hasOne('UserModel','id','user_id')->field('id,user_name')->selfRelation();
    }
}