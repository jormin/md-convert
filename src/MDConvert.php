<?php

namespace Jormin\MDConvert;

use Alchemy\Zippy\Zippy;

class MDConvert{

    /**
     * 获取对象
     *
     * @return MDConvert
     */
    public static function load(){
        return new self();
    }

    /**
     * 失败
     *
     * @param $message
     * @param null $data
     * @return array
     */
    private function error($message, $data=null){
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
    private function success($message, $data=null){
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
    public function toHtml($mdFile, $savePath=null, $saveName=null){
        $array = $this->beforeConvert($mdFile, $savePath, $saveName);
        if(isset($array['success'])){
            return $array;
        }
        list($mdFile, $savePath, $saveName) = $array;
        $tmpPath = $savePath.DIRECTORY_SEPARATOR.$saveName;
        !file_exists($tmpPath) && mkdir($tmpPath);
        copy(dirname(__FILE__).'/template/style.css', $tmpPath.'/style.css');
        $htmlContent = $this->getHtmlContent($mdFile, 'html');
        $htmlFile = $tmpPath.DIRECTORY_SEPARATOR.$saveName.'.html';
        $result = file_put_contents($htmlFile, $htmlContent);
        if(!$result){
            return $this->error('写入Html内容失败');
        }
        $saveFile = $savePath.DIRECTORY_SEPARATOR.$saveName.'.zip';
        $zippy = Zippy::load();
        $zippy->create($saveFile, array(
            $saveName => $tmpPath
        ), true);
        $this->delDir($tmpPath);
        return $this->success('转换成功', ['saveFile'=>$saveFile]);
    }

    /**
     * 转换成PDF
     *
     * @param $mdFile
     * @param $savePath
     * @param null $saveName
     * @return array
     */
    public function toPDF($mdFile, $savePath=null, $saveName=null){
        $array = $this->beforeConvert($mdFile, $savePath, $saveName);
        if(isset($array['success'])){
            return $array;
        }
        list($mdFile, $savePath, $saveName) = $array;
        $saveName .= '.pdf';
        $htmlContent = $this->getHtmlContent($mdFile, 'pdf');
        $saveFile = $savePath.DIRECTORY_SEPARATOR.$saveName;
        $styleFile = $savePath.'/style.css';
        !file_exists($styleFile) && copy(dirname(__FILE__).'/template/style.css', $styleFile);
        try{
            error_reporting(E_ALL^E_NOTICE);
            $mpdf = new \Mpdf\Mpdf(['mod'=>'utf-8']);
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->SetHTMLFooter('<div class="footer" align="center">- {PAGENO} -</div>');
            $mpdf->WriteHTML($htmlContent);
            $mpdf->Output($saveFile);
            $mpdf->cleanup();
            unlink($styleFile);
            return $this->success('转换成功', ['saveFile'=>$saveFile]);
        }catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取Html内容
     *
     * @param $mdFile
     * @param $template
     * @return mixed|string
     */
    private function getHtmlContent($mdFile, $template){
        $templateFile = dirname(__FILE__).'/template/'.$template.'.php';
        $parseDown = new \Parsedown();
        $htmlContent = $parseDown->parse(file_get_contents($mdFile));
        $htmlContent = str_replace('{$content}', $htmlContent, file_get_contents($templateFile));
        return $htmlContent;
    }

    /**
     * 预处理
     *
     * @param $mdFile
     * @param null $savePath
     * @param null $saveName
     * @return array
     */
    private function beforeConvert($mdFile, $savePath=null, $saveName=null){
        if(!$mdFile || !file_exists($mdFile) || !in_array(pathinfo($mdFile, PATHINFO_EXTENSION), ['md', 'markdown'])){
            return $this->error('源文件不存在或不是Markdown文件');
        }
        !$savePath && $savePath = pathinfo($mdFile, PATHINFO_DIRNAME);
        if(!$saveName){
            $baseName = pathinfo($mdFile, PATHINFO_BASENAME);
            $baseNameArr = explode('.', $baseName);
            array_pop($baseNameArr);
            $saveName = implode('.', $baseNameArr);
        }
        return array($mdFile, $savePath, $saveName);
    }

    /**
     * 删除目录
     *
     * @param $dir
     */
    private function delDir($dir){
        if($dirHandle = @opendir($dir)){
            while($filename=readdir($dirHandle)){
                if(!in_array($filename, ['.', '..'])){
                    is_dir($filename) ? $this->delDir($dir.'/'.$filename) : unlink($dir."/".$filename);
                }
            }
            closedir($dirHandle);
            rmdir($dir);
        }
    }
}
