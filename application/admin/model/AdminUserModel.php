<?php

namespace app\admin\model;
use app\admin\library\BaseModel;

class AdminUserModel extends BaseModel
{
    public $table = 'admin_user';

    public function login($uname, $pwd)
    {
        $rec = $this->where(['user_name'=>$uname,'status'=>1])->find();

        if (empty($rec)) {
            return false;
        }
        if ($pwd !==$rec['password']) {
            return false;
        }
        unset($rec['password']);
        return $rec;
    }

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