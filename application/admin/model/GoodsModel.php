<?php


namespace app\admin\model;


use app\admin\library\BaseModel;

class GoodsModel extends BaseModel
{
    protected $table = 'goods';

    public  function formatWhere(array $where = null  )
    {
        $result = [];
        if ($where) {
            foreach ($where as $k => $v) {
                switch ($k) {
                    case 'id':
                        $result[] = [$k, '=', $v];
                        break;
                    case 'name':
                        $result[] = [$k, 'like', $v];
                }
            }
        }
        return $result;
    }
}