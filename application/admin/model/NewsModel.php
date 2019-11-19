<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class NewsModel extends BaseModel
{
    protected $table = 'news';
    public  function formatWhere(array $where = null  ){
        $result = [];
        if ( $where ) {
            foreach ( $where as $k => $v ) {
                switch ( $k ) {
                    case 'id':
                        $result[] = [$k,'=', $v];
                        break;
                    case 'name':
                        $result[] = [$k,'like', $v];
                }
            }
        }
        return $result;
    }

    public function adminUser()
    {
        return $this->hasOne('AdminUserModel','id','admin_user_id');
    }

    public function comments()
    {
        return $this->hasMany('CommentModel','id_value');
    }

    public function getCity()
    {
        return $this->hasOne('CityModel','id','city');
    }

}