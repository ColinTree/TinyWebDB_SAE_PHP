<?
define('MANAGEversion','201705151');
require('class/db.php');

//default
$autobackupTypeSettingDefault='json';
$autobackupAuthSettingDefault='';
$autobackupPrefixSettingDefault='';

//load settings
$autobackupSetting=setting::get('autobackup');
$autobackupTypeSetting=setting::get('autobackup_type');
$autobackupAuthSetting=setting::get('autobackup_auth');
$autobackupPrefixSetting=setting::get('autobackup_prefix');

//check if default
if(empty($autobackupTypeSetting)){
    $autobackupTypeSetting=$autobackupTypeSettingDefault;
}
if(empty($autobackupAuthSetting)){
    $autobackupAuthSetting=$autobackupAuthSettingDefault;
}
if(empty($autobackupPrefixSetting)){
    $autobackupPrefixSetting=$autobackupPrefixSettingDefault;
}

//web request for debug
if(isset($_REQUEST['autobackup'])){
    $autobackupSetting=$_REQUEST['autobackup'];
}
if(isset($_REQUEST['autobackup_type'])){
    $autobackupTypeSetting=$_REQUEST['autobackup_type'];
}
if(isset($_REQUEST['autobackup_auth'])){
    $autobackupAuthSetting=$_REQUEST['autobackup_auth'];
}
if(isset($_REQUEST['autobackup_prefix'])){
    $autobackupPrefixSetting=$_REQUEST['autobackup_prefix'];
}

//run
if($autobackupSetting==='on'){
    $backupResult=false;
    if(class_exists('backup')){
        if($autobackupTypeSetting=='json'){
            $backup=new backup($autobackupPrefixSetting);
            $backup->autobackupAuthSetting=$autobackupAuthSetting;
            if($backup->toExtJson()!==false){
                $backupResult=true;
            }else{
                $backupResult='unknown error';
            }
        }elseif($autobackupTypeSetting=='excel'){
            $backup=new backup($autobackupPrefixSetting);
            $backup->autobackupAuthSetting=$autobackupAuthSetting;
            if($backup->toExcel()!==false){
                $backupResult=true;
            }else{
                $backupResult='unknown error';
            }
        }else{
            $backupResult='backup type not exist: '.$autobackupTypeSetting;
        }
    }else{
        $backupResult='class: "backup" not exist';
    }
    if($backupResult===true){
        exit('已备份');
    }else{
        exit('备份不成功：'.$backupResult);
    }
}else{
    exit('未开启定时备份');
}

//class
use sinacloud\sae\Storage as Storage;
class backup{
    public $appid;
    public $ex;
    public $filename;
    public $autobackupAuthSetting;
    public function __construct($ex,$appid=''){
        $this->appid=$appid;
        $this->ex=$ex;
        $this->fileName=date('Y_m_d_H_i_s');
        if($ex!=''){
            $this->fileName=$this->fileName.'-'.$ex;
        }
    }
    public function findByKVDB(){
        return db::getall($this->ex);
    }
    public function saveToKvfile($content,$type){
        $rtn=kvfile::save('/AutoBackup/'.$this->fileName.($type=='json' ? '.json' : '.xls'), $content);
        if($rtn!==false){
            kvfile::auth('/AutoBackup/',$this->autobackupAuthSetting);
            return $rtn;
        }else{
            return false;
        }
    }
    public function toExtJson(){
        $results=$this->findByKVDB();
        $json=json_encode($results);
        return $this->saveToKvfile($json,'json');
    }
    public function toExcel(){
        $ExcelStoreToStorage=setting::get('backup_excel_store_to_storage')=='on';
        $ExcelAutoWidth=setting::get('backup_excel_auto_width')=='on';
        
        if($ExcelStoreToStorage){
            $s=new Storage;
            if(!in_array('files',$s->listBuckets())){$s->putBucket('files');}
            unset($s);
            $file_name='saestor://files/ExcelExportFiles/'.$this->fileName.'.xls';
        }else{
            $file_name=SAE_TMP_PATH.$this->fileName;
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

        return $this->saveToKvfile(file_get_contents($file_name),'xls');
    }
}