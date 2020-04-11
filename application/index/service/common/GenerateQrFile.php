<?php
/**
 * Developer: Zhaozewu.
 * Date: 2019/5/11
 * Time: 11:38
 */


namespace app\index\service\common;

class GenerateQrFile
{


    //将码存入表格保存至服务器
    public function exportExcel($codes, $generateInfo, $urlPrefix)
    {

     //    var_dump($generateInfo['id']);
     // die();
        include_once EXTEND_PATH . '/PHPExcel/PHPExcel.php';

        $objPHPExcel = new \PHPExcel();//方法二

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:F1');//合并单元格
        // die(var_dump($objPHPExcel));

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '批次id:' . $generateInfo['id'] . ', 总数量:' . $generateInfo['all_num']  . ',  生成时间:' . date('Y-m-d H:i:s'));

        //标题
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', 'CODE');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2', 'URL');

        for ($i = 0; $i < count($codes); $i++) {
            $objPHPExcel->getActiveSheet(0)->setCellValue('A' . ($i + 3), $codes[$i]);
            $objPHPExcel->getActiveSheet(0)->setCellValue('B' . ($i + 3), $urlPrefix . $codes[$i]);
        }

        $user_path = $_SERVER['DOCUMENT_ROOT'] . "/upfiles/qrExcel/";
        $filename = 'QR' . date('YmdHis', time()) . '.xlsx';
        $filename = iconv("utf-8", "gb2312", $filename);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $objWriter->save($user_path . $filename);

        $path = ['url' => $user_path, 'name' => $filename];
        return $path;
    }


}