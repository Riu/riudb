<?php namespace RiuDB\Json;

class Alias extends \RiuDB\Json\Create {


    public function id($alias)
    {
        // tworzenie zmiennych opartych o alias
        $alias = strtolower($alias);
        $this->_id = $alias;
        $this->_iddir = str_split(substr($alias, 0, 4),1);
        $this->_collection = 'aliases';
        $this->path();
        return $this;

    }
    
    public function delete()
    {
        $file = $this->_path.$this->_ext;
        if (file_exists($file)){
            unlink($file);
        }
        return $this;
    }

    public function read()
    {
        $file = $this->_path.$this->_ext;
        if(is_file($file)){
            if(is_array($file)){
                return json_decode(file_get_contents($file));
            }
            else{
            return get_object_vars(json_decode(file_get_contents($file)));
            }
        }
        else{
            return false;
        }
    }
}
