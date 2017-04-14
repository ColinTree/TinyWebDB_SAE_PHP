<?
$backup=new backup($_REQUEST['ex']);
switch($_REQUEST['type']){
    case'Json':   $backup->toExtJson();break;
    case'Xml':    $backup->toExtXml(); break;
    case'CSV':    $backup->toCSV();    break;
    case'Excel':  $backup->toExcel();  break;
    case'jsonUp': $backup->jsonToKVDB($_FILES['upFile']['tmp_name']); break;
    case'excelUp':$backup->excelToKVDB($_FILES['upFile']['tmp_name']); break;
}
?><div id="main"><div class="panel panel-default"><div class="panel-heading"><div class="panel-title">备份/恢复</div></div><div class="panel-body"><pre>======数据库备份======<form name="readform" action="" method="get"><input type="hidden" name="a" value="backup"/><input type="hidden" name="noecho" value="true"/>导出类型：<select name="type" class="form-control"><option value="Json">Json</option><option value="Xml">Xml</option><option value="CSV">CSV</option><option value="Excel">Excel</option></select>key前缀：<input type="text" class="form-control" name="ex" /><input type="submit" class="btn btn-default" value="备份" /></form>======数据库恢复======<form name="sendform" action="?a=backup&type=jsonUp" method="post" enctype="multipart/form-data">导入文件(.JSON格式)：<input type="file" accept="application/json" class="form-control" name="upFile" id="file"/><input type="submit" class="btn btn-default" value="恢复"/></form><form name="sendform" action="?a=backup&type=excelUp" method="post" enctype="multipart/form-data">导入文件(.xls .xlsx格式)：<input type="file" accept=".xls, .xlsx" class="form-control" name="upFile" id="file"/>读取格式：读取文件中的第一个表，并忽略第一行，其余读取每一行的第一格作为标签，第二格为值<br>样例：参考导出Excel的格式<br><input type="submit" class="btn btn-default" value="恢复"/></form></pre></div></div></div><div style="color:#bbb;padding-left:10px;font-size:80%">Backup/Restore By Cp0204 @ <a target="_blank" style="color:#bbb;text-decoration:underline" href="http://saebbs.com/forum.php?mod=viewthread&tid=3637">saebbs.com</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modify&amp;Merge By ColinTree @ <a target="_blank" style="color:#bbb;text-decoration:underline" href="http://www.colintree.cn">colintree.cn</a></div><?

use sinacloud\sae\Storage as Storage;
class backup{
    public $appid;
    public $ex;
    public $filename;
    private $kv;
    public function __construct($ex,$appid=''){
        $this->appid=$appid;
        $this->ex=$ex;
        $this->fileName=date('Y_m_d_H_i_s');
        if($ex!=''){
            $this->fileName=$this->fileName.'-'.$ex;
        }
        $this->kv=new SaeKV();
        $this->kv->init($this->appid);
    }
    public function findByKVDB(){
        $ret=$this->kv->pkrget(prefix.$this->ex,100);
        while(true){
            foreach($ret as $k=>$v)$results[substr($k,strlen(prefix))]=$v;
            end($ret);$start_key=key($ret);
            $i=count($ret);if($i<100)break;
            $ret=$this->kv->pkrget(prefix.$ex,100,$start_key);
        }
        return $results;
    }
    public function toExtJson(){
        header('Content-Type: text/json');header('Content-Disposition: attachment; filename="'.$this->fileName.'.json"');
        $results=$this->findByKVDB();
        $json=json_encode($results);
        exit($json);
    }
    public function toExtXml(){
        header('Content-Type: text/xml');header('Content-Disposition: attachment; filename="'.$this->fileName.'.xml"');
        echo '<?xml version="1.0"  encoding="utf-8" ?>',"\n";
        echo '<tinywebdb>',"\n";
        echo "\t",'<app>',$_SERVER['HTTP_APPNAME'],'</app>',"\n";
        $results=$this->findByKVDB();
        foreach($results as $tag=>$value)echo "\t",'<pair><tag>',$tag,'</tag><value>',$value,'</value></pair>',"\n";
        exit('</tinywebdb>');
    }
    public function toCSV(){
        header('Content-type:application/vnd.ms-excel; charset=gbk');header('Content-Disposition:filename='.$this->fileName.'.csv');
        echo iconv('utf-8','gbk','"标签/Tag","值/value"'."\n");
        $results=$this->findByKVDB();
        foreach($results as $tag=>$value)echo iconv('utf-8','gbk','"'.str_replace('"','""',$tag).'","'.str_replace('"','""',$value)."\"\n");
        exit;
    }
    public function toExcel(){
        $ExcelStoreToStorage=$this->kv->get('tinywebdbMANAGE_backup_excel_store_to_storage')=='on';
        $ExcelAutoWidth=$this->kv->get('tinywebdbMANAGE_backup_excel_auto_width')=='on';
        
        if($ExcelStoreToStorage){
            $s=new Storage;
            if(!in_array('files',$s->listBuckets())){$s->putBucket('files');}
            unset($s);
            $file_name='saestor://files/ExcelExportFiles/'.$this->fileName.'.xls';
        }else{
            $file_name=SAE_TMP_PATH.$this->fileName.'.xls';
        }

        require('class/PHPExcel.php');
        $objPHPExcel=new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($_SERVER['HTTP_APPNAME'].' - TinyWebDB_SAE_PHP By ColinTree');
        $objPHPExcel->getProperties()->setLastModifiedBy($_SERVER['HTTP_APPNAME'].' - TinyWebDB_SAE_PHP By ColinTree');
        $objPHPExcel->getProperties()->setTitle('Exported Data for SAE_APP: '.$_SERVER['HTTP_APPNAME'].'.');
        $objPHPExcel->getProperties()->setDescription('Exported Data for SAE_APP: '.$_SERVER['HTTP_APPNAME'].'.');
        $objPHPExcel->getActiveSheet()->setCellValue('A1','标签/Tag');$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setCellValue('B1','值/value');$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        if($ex!=''){$objPHPExcel->getActiveSheet()->setCellValue('D1','前缀');$objPHPExcel->getActiveSheet()->setCellValue('E1',$ex);}
        $rowsCount=2;$results=$this->findByKVDB();
        foreach($results as $tag=>$value){$objPHPExcel->getActiveSheet()->setCellValue('A'.$rowsCount,$tag);$objPHPExcel->getActiveSheet()->setCellValue('B'.$rowsCount++,$value);}
        if($ExcelAutoWidth)foreach(['A','B','D','E'] as $column)$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
        $objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);$objWriter->save($file_name);

        header('Content-Disposition:attachment;filename='.$this->fileName.'.xls');$data=fopen($file_name,'rb');echo fread($data,filesize($file_name));
        exit;
    }
    public function jsonToKVDB($fileName){
        $json=file_get_contents($fileName);
        $results=json_decode($json,true);
        foreach($results as $k=>$v)$this->kv->set(prefix.$k,$v);
        exit('恢复完成！');
    }
    public function excelToKVDB($fileName){
        require('class/PHPExcel.php');
        $objPHPExcel=new PHPExcel();
        $PHPReader=new PHPExcel_Reader_Excel5();
        if(!$PHPReader->canRead($fileName)){
            $PHPReader=new PHPExcel_Reader_Excel2007();
            if(!$PHPReader->canRead($fileName)){
                exit('错误：不是.xls、.xlsx文件格式 或 特殊格式不受支持');
            }
        }
        $PHPExcel=$PHPReader->load($fileName);
        $currentSheet=$PHPExcel->getSheet(0);
        $allRow=$currentSheet->getHighestRow();
        for($currentRow=2;$currentRow<=$allRow;$currentRow++){
            $this->kv->set(
                prefix.$currentSheet->getCellByColumnAndRow(0,$currentRow)->getValue(),
                $currentSheet->getCellByColumnAndRow(1,$currentRow)->getValue()
            );
        }
        exit('恢复完成！');
    }
}