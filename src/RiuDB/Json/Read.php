<?php namespace RiuDB\Json;

class Read extends \RiuDB\Json\Json {

    public function join($keys, $attach = FALSE, $limit = FALSE, $reverse = FALSE)
    {
        if(is_array($keys)){
        $key = $keys[0];
        $module = $keys[1];
        }
        else{
        $key = $keys;
        $module = 'records';
        }

        foreach($this->_joins as $i){
            if (!empty($this->_data[$i]) AND array_key_exists($key, $this->_data[$i])) {
                $value = $this->_data[$i][$key];
                $this->attach($i,$key,$value,$attach,$module,$limit,$reverse);
            }
        }
        return $this;
        

    }

    protected function attach($i,$key,$value,$a,$module,$limit,$reverse)
    {

        if(is_array($a)){
        $attach = $a[0];
        $amodule = $a[1];
        }
        else{
        $attach = $a;
        $amodule = 'records';
        }

        $files = array();
        if(is_array($value)){

            if(!empty($limit)){
                $value = array_reverse($value);
                $value = array_slice($value, 0, $limit);
            }

            if($reverse === TRUE){
                $value = array_reverse($value);
            }
            foreach($value as $v){

                $file = $this->getarray($v,$module);
                if (!empty($file) AND !empty($attach) AND array_key_exists($attach, $file)){
                    $files[$v] = $file;
                    $files[$v][$attach] = $this->getarray($files[$v][$attach],$amodule);
                }
                else{
                    if (!empty($file)){
                    $files[$v] = $file;
                    }
                    else return FALSE;
                }
            }

        }
        else{
            $files = $this->getarray($value,$module);
            if (!empty($attach) AND array_key_exists($attach, $files)){
                $files[$attach] = $this->getarray($files[$attach],$amodule);
            }
        }
        
        $this->_data[$i][$key] = $files;
        return $this;
    }

}
