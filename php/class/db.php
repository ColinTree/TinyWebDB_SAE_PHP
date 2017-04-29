<?
define('prefix','tinywebdb_');
define('settingPrefix','tinywebdbMANAGE_');

class db{
    public static function get($tag){
        $kv=new SaeKV();
        return $kv->get(prefix.$tag);
    }
    public static function set($tag,$value){
        $kv=new SaeKV();
        return $kv->set(prefix.$tag,$value);
    }
    public static function del($tag){
        $kv=new SaeKV();
        return $kv->delete(prefix.$tag);
    }
    public static function getall($prefix='',$return_plain_list=false){
        $kv=new SaeKV();
        $tags=[];
        $ret=$kv->pkrget(prefix.$prefix,100);
        while(true){
            foreach($ret as $k=>$v){
                if($return_plain_list){
                    $tags[]=[substr($k,strlen(prefix)),''.$v];
                }else{
                    $tags[substr($k,strlen(prefix))]=''.$v;
                }
            }
            end($ret);
            $start_key=key($ret);
            $i=count($ret);
            if($i<100)break;
            $ret=$kv->pkrget(prefix.$prefix,100,$start_key);
        }
        return $tags;
    }
    public static function havetag(){
        $kv=new SaeKV();
        return 0+count($kv->pkrget(prefix,1))>0;
    }
    public static function isempty(){
        $kv=new SaeKV();
        return 0+count($kv->pkrget(prefix,1))==0;
    }
    public static function clear($prefix=''){
        foreach(db::getall($prefix) as $k=>$v)
            db::del($k);
    }
    public static function search($keyword, $ignoreCase=true, $searchInTag=true, $searchInValue=false,$prefix='',$return_plain_list=false){
        $keyword=$ignoreCase ? strtolower($keyword) : $keyword;
        $kv=new SaeKV();
        $tags=[];
        $ret=$kv->pkrget(prefix.$prefix,100);
        while(true){
            foreach($ret as $k=>$v){
                $k=substr($k,strlen(prefix));
                $kk=$ignoreCase ? strtolower($k) : $k;
                $vv=$ignoreCase ? strtolower($v) : $v;
                if(($searchInTag===true && strpos($kk,$keyword)!==false) || ($searchInValue===true && strpos($vv,$keyword)!==false)){
                    if($return_plain_list){
                        $tags[]=[$k,''.$v];
                    }else{
                        $tags[$k]=''.$v;
                    }
                }
            }
            end($ret);
            $start_key=key($ret);
            $i=count($ret);
            if($i<100)break;
            $ret=$kv->pkrget(prefix.$prefix,100,$start_key);
        }
        return $tags;
    }
    public static function count($prefix=''){
        $count=0;
        foreach(db::getall($prefix) as $k=>$v)
            $count++;
        return $count;
    }
    public static function mget($taglist,$return_plain_list=false){
        $ret=[];
        foreach($taglist as $k){
            if($return_plain_list){
                $ret[]=[$k,''.db::get($k)];
            }else{
                $ret[$k]=''.db::get($k);
            }
        }
        return $ret;
    }
    public static function mdelete($taglist){
        foreach($taglist as $k){
            db::del($k);
        }
    }
}

class setting{
    public static function get($tag){
        $kv=new SaeKV();
        return $kv->get(settingPrefix.$tag);
    }
    public static function set($tag,$value){
        $kv=new SaeKV();
        return $kv->set(settingPrefix.$tag,$value);
    }
    public static function clear($prefix=''){
        foreach(setting::getall($prefix) as $k=>$v)
            setting::del($k);
    }
}