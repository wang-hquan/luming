<?php

namespace app\admin\controller;

use PHPExcel;
use think\Controller;
use think\Db;

require_once "../extend/PHPExcel.php";

class Upload extends Controller
{
    private $image_suffix = ['bmp', 'ogv', 'jpg', 'xls', 'jpeg', 'png', 'mp4', 'tif', 'gif', 'pcx', 'tga', 'exif', 'fpx', 'svg', 'psd', 'cdr', 'pcd', 'dxf', 'ufo', 'eps', 'ai', 'raw', 'wmf', 'webp'];
    private $image_size = 2097152000;// 2M
    private $Http = 'http://s.bkgaoshou.com/img/';

    public function images()
    {
        try {
            if (!$this->request->file()) {
                return toJson('500', '上传失败', '请选择文件上传');
            }
            $file = $this->request->file('file');
            $file->validate(['size' => $this->image_size, 'ext' => $this->image_suffix]);
            $upload = $file->move(APP_PATH . '/img');
            if ($upload) {
                $url = $this->Http . $upload->getSaveName();
                $url = str_replace("\\", "/", $url);
                return toJson('200', 'success', $url);
            } else {
                return toJson('500', '上传失败', $file->getError());
            }
        } catch (\Exception $ex) {
            return toJson('500', '上传失败', $ex->getMessage());
        }
    }

    public function excel()
    {
        $data = $this->request->param('test');
        foreach ($data as $k => $v) {
            //            echo $v['一级学科（学科大类）'];
            $param1 = [
                'name' => $v['学科门类'],
                'parent_id' => 0,
                'specialty_code' => $v['学科类代码'],
            ];
            $param2 = [
                'name' => $v['专业类'],
                'parent_id' => $v['学科类代码'],
                'specialty_code' => $v['专业类代码'],
            ];
            $param3 = [
                'name' => $v['专业名称'],
                'parent_id' => $v['专业类代码'],
                'specialty_code' => $v['专业代码'],
                'type' => 1,
            ];
            $txt1[] = $param1;
            $txt2[] = $param2;
            $txt3[] = $param3;
        }
        $txt = array_merge($txt1, $txt2, $txt3);
        $txt = array_unique($txt, SORT_REGULAR);
        print_r($txt);
        Db::table('specialty')->insertAll($txt);
        return toJson('200', '上传成功,请勿重复上传');

    }

    //学校上传EXCEL
    public function toSchoolExcel()
    {
        try {
            if (!$this->request->file()) {
                return toJson('500', '上传失败', '请选择文件上传');
            }
            $file = $_FILES['file'];
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');//配置成2003版本，因为office版本可以向下兼容
            $objPHPExcel = $objReader->load($file['tmp_name'], $encode = 'utf-8');//$file 为解读的excel文件
            //dump($objPHPExcel);die;
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            for ($j = 2; $j <= $highestRow; $j++) {
                $school_id = $objPHPExcel->getActiveSheet()->getCell('A' . $j)->getValue();
                $parent_id = $objPHPExcel->getActiveSheet()->getCell('B' . $j)->getValue();
                $name = $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue();
                $degree = $objPHPExcel->getActiveSheet()->getCell("E" . $j)->getValue();
                $found_time = $objPHPExcel->getActiveSheet()->getCell("F" . $j)->getValue();
                $city_id = $objPHPExcel->getActiveSheet()->getCell("G" . $j)->getValue();
                $nature = $objPHPExcel->getActiveSheet()->getCell("H" . $j)->getValue();
                $school_type_id = $objPHPExcel->getActiveSheet()->getCell("I" . $j)->getValue();
                $school_belong = $objPHPExcel->getActiveSheet()->getCell("J" . $j)->getValue();
                $school_hot = $objPHPExcel->getActiveSheet()->getCell("K" . $j)->getValue();
                $school_url = $objPHPExcel->getActiveSheet()->getCell("L" . $j)->getValue();
                $school_admissions_url = $objPHPExcel->getActiveSheet()->getCell("M" . $j)->getValue();
                $school_panoramic = $objPHPExcel->getActiveSheet()->getCell("N" . $j)->getValue();
                $desc = $objPHPExcel->getActiveSheet()->getCell("O" . $j)->getValue();
                $school_tip_id = $objPHPExcel->getActiveSheet()->getCell("P" . $j)->getValue();
                $has_master = $objPHPExcel->getActiveSheet()->getCell("Q" . $j)->getValue();
                $has_doctor = $objPHPExcel->getActiveSheet()->getCell("R" . $j)->getValue();
                $address = $objPHPExcel->getActiveSheet()->getCell("S" . $j)->getValue();
                $tes = Db::table('school_type')->where('name', $school_type_id)->value('id');
                $param = [
                    'school_id' => $school_id,
                    'parent_id' => $parent_id ? $parent_id : 0,
                    'name' => $name,
                    'degree' => $degree == '本科' ? 0 : 1,
                    'found_time' => $found_time,
                    'city_id' => Db::table('city')->where('city', $city_id)->value('city_code'),
                    'nature' =>  Db::table('school_nature')->where('name', $nature)->value('id'),
                    'school_type_id' => $tes ? $tes : 0,
                    'school_belong' => $school_belong,
                    'school_hot' => $school_hot ? $school_hot : 0,
                    'school_url' => $school_url,
                    'school_admissions_url' => $school_admissions_url ? $school_admissions_url : '',
                    'school_panoramic' => $school_panoramic ? $school_panoramic : '',
                    'desc' => $desc,
                    'school_tip_id' => $school_tip_id,
                    'has_master' => $has_master ? $has_master : 0,
                    'has_doctor' => $has_doctor ? $has_doctor : 0,
                    'address' => $address,
                ];
                if (isset($school_tip_id)) {
                    $tip = explode('、', $school_tip_id);
                    $d = [];
                    foreach ($tip as $key => $val) {
                        $d[] = Db::table('school_tip')->where('name', $val)->value('id');
                    }
                    $param['school_tip_id'] = implode(',', $d);
                    $param['school_tip_id'] = $param['school_tip_id'] ? $param['school_tip_id'] : '';
                }
                Db::table('school')->insert($param);
            }
            return toJson('200', 'success');
        } catch (\Exception $ex) {
            return toJson('500', $ex->getMessage());
        }
    }

    //学院上传EXCEL
    public function toAcademyExcel()
    {
        try {
            if (!$this->request->file()) {
                return toJson('500', '上传失败', '请选择文件上传');
            }
            $file = $_FILES['file'];
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');//配置成2003版本，因为office版本可以向下兼容
            $objPHPExcel = $objReader->load($file['tmp_name'], $encode = 'utf-8');//$file 为解读的excel文件
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            for ($j = 2; $j <= $highestRow; $j++) {
                $school_id = $objPHPExcel->getActiveSheet()->getCell('B' . $j)->getValue();
                $academy_name = $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue();
                $res = Db::table('school_academy')->where('school_id',$school_id)->where('name',$academy_name)->find();
                if ($res){
                    return toJson('500', '请勿重复上传');
                }
                $param[] = [
                    'school_id' => $school_id,
                    'name' => $academy_name
                ];
            }
            $param = array_unique($param, SORT_REGULAR);
            Db::table('school_academy')->insertAll($param);
            return toJson('200', 'success');
        } catch (\Exception $ex) {
            return toJson('500', $ex->getMessage());
        }
    }

    //专业上传EXCEL
    public function toSpecialtyExcel()
    {
        try {
            if (!$this->request->file()) {
                return toJson('500', '上传失败', '请选择文件上传');
            }
            $file = $_FILES['file'];
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');//配置成2003版本，因为office版本可以向下兼容
            $objPHPExcel = $objReader->load($file['tmp_name'], $encode = 'utf-8');//$file 为解读的excel文件
//dump($objPHPExcel);die;
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            for ($j = 2; $j <= $highestRow; $j++) {
                $school_id = $objPHPExcel->getActiveSheet()->getCell('B' . $j)->getValue();
                $academy_name = $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue();
                $specialty_id = $objPHPExcel->getActiveSheet()->getCell("G" . $j)->getValue();
                $specialty_name = $objPHPExcel->getActiveSheet()->getCell("H" . $j)->getValue();
                $country_feature = $objPHPExcel->getActiveSheet()->getCell("J" . $j)->getValue();
                $city_feature = $objPHPExcel->getActiveSheet()->getCell("K" . $j)->getValue();
                $res = Db::table('school_specialty')
                    ->where('school_id',$school_id)
                    ->where('name',$academy_name)->find();
                if ($res){
                    return toJson('500', '请勿重复上传'.$school_id.'，学院:'.$academy_name.'，专业:'.$specialty_name);
                }
                $param[] = [
                    'school_id' => $school_id,
                    'academy_id' => Db::table('school_academy')->where('school_id', $school_id)->where('name', $academy_name)->value('id'),
                    'specialty_name' => $specialty_name,
                    'specialty_id' => $specialty_id,
                    'country_feature' => $country_feature,
                    'city_feature' => $city_feature,
                ];
            }
            Db::table('school_specialty')->insertAll($param);
            return toJson('200', 'success');
        } catch (\Exception $ex) {
            return toJson('500', $ex->getMessage());
        }


    }

    //专业详情上传
    public function toSpecialtyOverview()
    {
        try {
            if (!$this->request->file()) {
                return toJson('500', '上传失败', '请选择文件上传');
            }
            $file = $_FILES['file'];
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');//配置成2003版本，因为office版本可以向下兼容
            $objPHPExcel = $objReader->load($file['tmp_name'], $encode = 'utf-8');//$file 为解读的excel文件

//dump($objPHPExcel);die;
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 取得总行数
            for ($j = 2; $j <= $highestRow; $j++) {
                $specialty_id = $objPHPExcel->getActiveSheet()->getCell('B' . $j)->getValue();
                $qualification = $objPHPExcel->getActiveSheet()->getCell('C' . $j)->getValue();
                $wenli_proportion = $objPHPExcel->getActiveSheet()->getCell('L' . $j)->getValue();
                $nannv_proportion = $objPHPExcel->getActiveSheet()->getCell('K' . $j)->getValue();
                $introduction = $objPHPExcel->getActiveSheet()->getCell('E' . $j)->getValue();
                $discipline_required = $objPHPExcel->getActiveSheet()->getCell('F' . $j)->getValue();
                $main_course = $objPHPExcel->getActiveSheet()->getCell('G' . $j)->getValue();
                $train_objective = $objPHPExcel->getActiveSheet()->getCell('H' . $j)->getValue();
                $train_requirements = $objPHPExcel->getActiveSheet()->getCell('I' . $j)->getValue();
                $pg_direction = $objPHPExcel->getActiveSheet()->getCell('J' . $j)->getValue();
                $yuwen = $objPHPExcel->getActiveSheet()->getCell('Q' . $j)->getValue();
                $shuxue = $objPHPExcel->getActiveSheet()->getCell('R' . $j)->getValue();
                $yingyu = $objPHPExcel->getActiveSheet()->getCell('S' . $j)->getValue();
                $wuli = $objPHPExcel->getActiveSheet()->getCell('T' . $j)->getValue();
                $huaxue = $objPHPExcel->getActiveSheet()->getCell('U' . $j)->getValue();
                $shengwu = $objPHPExcel->getActiveSheet()->getCell('V' . $j)->getValue();
                $zhengzhi = $objPHPExcel->getActiveSheet()->getCell('W' . $j)->getValue();
                $lishi = $objPHPExcel->getActiveSheet()->getCell('X' . $j)->getValue();
                $dili = $objPHPExcel->getActiveSheet()->getCell('Y' . $j)->getValue();
                $best_school = $objPHPExcel->getActiveSheet()->getCell('P' . $j)->getValue();
                $jiuyelv = $objPHPExcel->getActiveSheet()->getCell('M' . $j)->getValue();
                $shenzaolv = $objPHPExcel->getActiveSheet()->getCell('N' . $j)->getValue();
                $zhiyefenbu = $objPHPExcel->getActiveSheet()->getCell('O' . $j)->getValue();
                $param[] = [
                    'specialty_id' => $specialty_id,
                    'qualification' => $qualification,
                    'wenli_proportion' => $wenli_proportion,
                    'nannv_proportion' => $nannv_proportion,
                    'introduction' => $introduction,
                    'discipline_required' => $discipline_required,
                    'main_course' => $main_course,
                    'train_objective' => $train_objective,
                    'train_requirements' => $train_requirements,
                    'pg_direction' => $pg_direction,
                    'yuwen' => $yuwen,
                    'shuxue' => $shuxue,
                    'yingyu' => $yingyu,
                    'wuli' => $wuli,
                    'huaxue' => $huaxue,
                    'shengwu' => $shengwu,
                    'zhengzhi' => $zhengzhi,
                    'lishi' => $lishi,
                    'dili' => $dili,
                    'best_school' => $best_school,
                    'jiuyelv' => $jiuyelv,
                    'shenzaolv' => $shenzaolv,
                    'zhiyefenbu' => $zhiyefenbu,
                ];
            }
//        $param = array_unique($param, SORT_REGULAR);
            Db::table('specialty_overview')->insertAll($param);
            return toJson('200', 'success');
        } catch (\Exception $ex) {
            return toJson('500', $ex->getMessage());
        }

    }

    //上传排名
    public function ranking()
    {
        if (!$this->request->file()) {
            return toJson('500', '上传失败', '请选择文件上传');
        }
        $file = $_FILES['file'];
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');//配置成2003版本，因为office版本可以向下兼容
        $objPHPExcel = $objReader->load($file['tmp_name'], $encode = 'utf-8');//$file 为解读的excel文件
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        for ($j = 2; $j <= $highestRow; $j++) {
            $ranking = $objPHPExcel->getActiveSheet()->getCell('A' . $j)->getValue();
            $name = $objPHPExcel->getActiveSheet()->getCell('B' . $j)->getValue();
            $res = Db::table('school_ranking')->where('name', $name)->find();
            if ($res) {
                Db::table('school_ranking')->where('name', $name)->update(['xiaoyouhui' => $ranking]);
            } else {
                Db::table('school_ranking')->insert(['name' => $name, 'xiaoyouhui' => $ranking]);
            }
        }
        return toJson('200', 'success');
    }
}