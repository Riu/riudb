<?php namespace RiuDB\Json;

abstract class Json {

    /*
    Kolelekcja określa w jakim folderze mają być składowane pliki json.
    Domyślnie jest to folder 'db/records' gdzie 'db' jest określane przez
    wartość zmiennej $config->app->riudb
    */
    protected $_collection = 'records';

    /*
    Scieżka do bazy riudb określna w  $config->app->riudb
    */
    protected $_dbdir = '../../public/db/';

    /*
    Identyfikator dokumentu jest unikalną wartością typu int na 
    podstawie której  określamy położenie danych w kolekcji RiuDB
    */
    protected $_id;

    /*
    Każdy rekord na podstawie posiadanego id otrzmuje tzw. ścieżkę
    dojścia. Jest to folder w którym znajduje się plik json, którego 
    nazwa odpowiada wartości identyfikatora i który zawiera tablicę 
    danych odpowiadających zestawowi danych modułu do którego
    należy identyfikator. Ścieżka dojścia do identyfikatora 
    przekazywana jest w formacie XX/XX/XX/XX
    */
    protected $_iddir;

    /*
    Domyślne rozszerzenie dla plików RiuDB
    */
    protected $_ext = '.json';

    /*
    Pełna ścieżka folderu lub pliku wraz z podaniem kolekcj
    */
    protected $_path;

    /*
    Pełna ścieżka folderu lub pliku wraz z podaniem kolekcji. Ścieżka ta 
    może być też ścieżką do podokumentu w folderze głównym 
    dokumentu lub jego podfolderze
    */
    protected $_folder = '';

    /*
    Pełna ścieżka dokumentu lub podkumentu z domyślnym 
    rozszerzeniem
    */
    protected $_filepath = '';

    /*
    Tablica danych do zapisu
    */
    protected $_data = array();


    public function collection($collection)
    {
        if(!empty($collection))
        {
            $this->_collection = $collection;
        }
        return $this;
    }

    /*
    
    */
    public function id($record)
    {
        $this->_id = $record;
        $this->_iddir = str_split(substr(10000000000+$this->_id, -10, 8),2);
        $this->path();
        return $this;

    }

    protected function path()
    {
        $pathdir= implode(DIRECTORY_SEPARATOR,$this->_iddir);
        $this->_path = $this->_dbdir.$this->_collection.DIRECTORY_SEPARATOR.$pathdir.DIRECTORY_SEPARATOR.$this->_id;
        return $this;
    }
    
    public function getpath($folder = FALSE)
    {
        if (!empty($folder)){
            return $this->_path.DIRECTORY_SEPARATOR.$folder;
        }
        else{
            return $this->_path;
        }
    }

    // zapisywanie pliku

     protected function savefile()
    {
        if($this->_filepath)
        {
            $file = $this->_path.DIRECTORY_SEPARATOR.$this->_filepath;
        }
        else
        {
            $file = $this->_path;
        }

        $file = $file.$this->_ext;
        $fp = fopen($file, 'w+');

        if(is_array($this->_data))
        {
            $this->_data = json_encode($this->_data);
        }

        fwrite($fp,$this->_data);
        fclose($fp);
    }

    public function get($data = array())
    {

        if(is_array($data)){
            foreach($data as $d){
                $this->_joins[] = $d;
                $this->_data[$d] = $this->getarray($d);
            }
        }
        else{
            $this->_joins[] = $data;
            $this->_data[$data] = $this->getarray($data);
        }

        return $this;
    }

     public function getfile($file)
    {
        $file = $this->getpath($file);
        $file = $file.$this->_ext;
        if(is_file($file)){
            $data  = json_decode(file_get_contents($file));
            if(is_object($data)){
            return get_object_vars($data);
            }
            else{
            return $data;
            }
            //return $file;
        }
        else{
            return false;
        }

    }

	// dodawanie pliku w folderze głównym dokumentu

 	public function addfile($file, $data = array())
	{
		$this->createdir();
		$this->_filepath = $file;
		$this->_data = $data;
		$this->savefile();
		return $this;
	}
    protected function getarray($id, $module = FALSE)
    {
        if($module){
        $this->_collection = $module;
        }
        $this->id($id);
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
     public function render()
    {
        return $this->_data;
    }
     public function debug()
    {
        echo $this->_path;
    }
}
