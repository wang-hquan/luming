<?php

namespace app\api\controller;

class Upload extends Api
{
    protected $noNeedLoginPC = [];
    protected $noNeedLoginAPP = [];

    private $image_suffix = ['bmp', 'ogv', 'jpg', 'jpeg', 'png', 'mp4', 'tif', 'gif', 'pcx', 'tga', 'exif', 'fpx', 'svg', 'psd', 'cdr', 'pcd', 'dxf', 'ufo', 'eps', 'ai', 'raw', 'wmf', 'webp', 'docx'];
    private $image_size = 2097152000;// 2M
    private $Http = 'http://s.bkgaoshou.com';

    public function upload()
    {
        try {
            if (!$this->request->file()) {
                return toJson('500', '上传失败', '请选择文件上传');
            }
            $file = $this->request->file('file');
            $type = $this->request->post('type');
            $file->validate(['size' => $this->image_size, 'ext' => $this->image_suffix]);
            switch ($type) {
                case 'img':
                    $upload = $file->move(APP_PATH . '/img');
                    break;
                case 'video':
                    $upload = $file->move(APP_PATH . '/video');
                    break;
                case 'test':
                    $upload = $file->move(APP_PATH . '/test');
                    break;
                case 'extra':
                    $upload = $file->move(APP_PATH . '/extra');
                    break;
                default :
                    return toJson('500', '请选择上传类型');
                    break;
            }

            if ($upload) {
                $url = $this->Http. '/'.$type .'/' . $upload->getSaveName();
                $url = str_replace("\\", "/", $url);
                return toJson('200', 'success', $url);
            } else {
                return toJson('500', '上传失败', $file->getError());
            }
        } catch (\Exception $ex) {
            return toJson('500', '上传失败', $ex->getMessage());
        }
    }
}