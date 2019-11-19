<?php


namespace app\admin\model;

use app\admin\library\BaseModel;

class UserModel extends BaseModel
{
    protected $table = 'user';
    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'id':
                    case 'mobile':
                        $result[] = [$k,'=', $v];
                        break;
                }
            }
        }
        return $result;
    }

    public function getCity()
    {
        return $this->hasOne('CityModel','id','city');
    }
}