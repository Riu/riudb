<?php namespace RiuDB\Json;

class Delete extends \RiuDB\Json\Json {
    
    public function delfile($file = FALSE)
    {
        if($file){
        $remove = $this->_path.DIRECTORY_SEPARATOR.$file;
        }
        else{
        $remove = $this->_path;
        }
        $file = $remove.$this->_ext;
        if (file_exists($file)){
            unlink($file);
        }
        return $this;
    }

    // usuwania katalogu

     public function deldir($folder = FALSE)
    {
        $this->_folder = $folder;
        $this->del();
        return $this;
    }

    // usuwania wybranego katalogu

     public function del()
    {

        if($this->_folder){
        $file = $this->_path.DIRECTORY_SEPARATOR.$this->_folder;
        }
        else{
        $file = $this->_path;
        }
        $path = $file.DIRECTORY_SEPARATOR;

            if (substr($path, -1, 1) != "/") {
            $path .= "/";
            }
        
            $normal = glob($path . "*");
            $hidden = glob($path . "\.?*");
            $all = array_merge($normal, $hidden);
        
            foreach ($all as $a) {

                if (preg_match("/(\.|\.\.)$/", $a))
                {
                    continue;
                }
        
                if (is_file($a) === TRUE) {
                    unlink($a);
                }
                else if (is_dir($a) === TRUE) {
                    removeDir($a);
                }
            }

            if (is_dir($path) === TRUE) {
                rmdir($path);
            }

        return $this;
    }

}
