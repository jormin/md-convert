<?php

namespace Jormin\MDConvert;

class MDConvert{

    /**
     * 失败
     *
     * @param $message
     * @param null $data
     * @return array
     */
    public static function error($message, $data=null){
        is_object($data) && $data = (array)$data;
        $return = ['success' => false, 'message' => $message, 'data'=>$data];
        return $return;
    }
    /**
     * 成功
     *
     * @param $message
     * @param null $data
     * @return array
     */
    public static function success($message, $data=null){
        is_object($data) && $data = (array)$data;
        $return = ['success' => true, 'message' => $message, 'data'=>$data];
        return $return;
    }

    /**
     * 转换成Html
     *
     * @param $mdFile
     * @param $savePath
     * @param null $saveName
     * @return array
     */
    public static function toHtml($mdFile, $savePath, $saveName=null){
        if(!$mdFile || !file_exists($mdFile)){
            return self::error('源文件不能为空');
        }
        if(!in_array(pathinfo($mdFile, PATHINFO_EXTENSION), ['md', 'markdown'])){
            return self::error('源文件不是Markdown文件');
        }
        !$savePath && $savePath = pathinfo($mdFile, PATHINFO_DIRNAME);
        !$saveName && $saveName = pathinfo($mdFile, PATHINFO_BASENAME);
        $htmlContent = self::getHtmlContent($mdFile, 'html');
        $saveFile = $savePath.DIRECTORY_SEPARATOR.$saveName;
        $result = file_put_contents($saveFile, $htmlContent);
        if(!$result){
            return self::error('写入Html内容失败');
        }
        return self::success('转换成功', ['saveFile'=>$saveFile]);
    }

    /**
     * 转换成PDF
     *
     * @param $mdFile
     * @param $savePath
     * @param null $saveName
     * @return array
     */
    public static function toPDF($mdFile, $savePath, $saveName=null){
        if(!$mdFile || !file_exists($mdFile)){
            return self::error('源文件不能为空');
        }
        if(!in_array(pathinfo($mdFile, PATHINFO_EXTENSION), ['md', 'markdown'])){
            return self::error('源文件不是Markdown文件');
        }
        !$savePath && $savePath = pathinfo($mdFile, PATHINFO_DIRNAME);
        !$saveName && $saveName = pathinfo($mdFile, PATHINFO_BASENAME);
        $htmlContent = self::getHtmlContent($mdFile, 'html');
        $saveFile = $savePath.DIRECTORY_SEPARATOR.$saveName;
        try{
            $mpdf = new \Mpdf\Mpdf(['mod'=>'utf-8']);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->WriteHTML($htmlContent);
            $result = $mpdf->Output($saveFile);
            if(!$result){
                return self::error('写入PDF内容失败');
            }
            return self::success('转换成功', ['saveFile'=>$saveFile]);
        }catch(\Exception $e){
            return self::error($e->getMessage());
        }
    }

    /**
     * 获取Html内容
     *
     * @param $mdFile
     * @param $template
     * @return mixed|string
     */
    public static function getHtmlContent($mdFile, $template){
        $templateFile = './template/'.$template.'.php';
        $parseDown = new \Parsedown();
        $htmlContent = $parseDown->parse(file_get_contents($mdFile));
        $htmlContent = str_replace('{$content}', $htmlContent, file_get_contents($templateFile));
        return $htmlContent;
    }
}
