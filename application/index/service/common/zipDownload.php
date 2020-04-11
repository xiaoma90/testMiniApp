<?php
/**
 * Developer: Zhaozewu.
 * Date: 2019/5/14
 * Time: 15:11
 */


namespace app\index\service\common;

class zipDownload
{

    //下载Excel文件
    public function downloadExcel($path, $name)
    {
        header("Content-type:text/html;charset=utf-8");
        $file_name = iconv("utf-8", "gb2312", $name);
        $file_path = $path . $file_name;
        if (!file_exists($file_path)) {
            echo "没有该文件文件";
            exit;
        }

        $fp = fopen($file_path, "r");
        $file_size = filesize($file_path);
        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:" . $file_size);
        Header("Content-Disposition: attachment; filename=" . $file_name);

        $buffer = 1024;
        $file_count = 0;
        while (!feof($fp) && $file_count < $file_size) {
            $file_con = fread($fp, $buffer);
            $file_count += $buffer;
            echo $file_con;
        }
        fclose($fp);

    }

}