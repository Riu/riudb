<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * RiuDB
 *
 * @package    RiuDB
 * @author     Radosław "Riu" Muszyński
 * @copyright  (c) 2012 Radosław "Riu" Muszyński
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt
 */
class Kohana_Riudb{

	// definiowanie domyślnego modułu
	protected $_module = 'records';

	// identyfikator dokumentu
	protected $_id;

	// ścieżka dojścia do identyfikatora w formacie XX/XX/XX/XX
	protected $_iddir;

	// ścieżka pełna
	protected $_path;

	// folder główny dokumentu lub podokument .json
	protected $_folder = '';

	// ścieżka folderu głównego
	protected $_filepath = '';

	// dane do zpisu
	protected $_data = array();

	// dane do zpisu
	protected $_newdata = array();

	// rozszerzenie dla zapisywanych plików
	protected $_ext = '.json';

	// tablica kluczy o których wartość rozszerzane są dane z dokumentu
	protected $_keys = array();

	// wartość elementu dokumentu będąca tablicą
	protected $_joins = array();

	protected function __construct($module = FALSE)
	{
		// definiowanie modułu rekordów
		if($module){
			$this->_module = $module;
		}
	}

	public static function factory($module = FALSE)
	{

		return new Riudb($module);

	}


	public function id($record)
	{
		// tworzenie zmiennych opartych o identyfikator
		if(is_numeric($record)){
		$this->_id = $record;
		$this->_iddir = str_split(substr(10000000000+$record, -10, 8),2);
		
		// ścieżka
		$this->path();
		return $this;
		}
		else{
		return false;
		}
	}

	// tworzenie pełnej ścieżki do pliku lub katalogu

 	protected function path()
	{

		$this->_path = DBPATH.$this->_module.DIRECTORY_SEPARATOR.$this->_iddir[0].DIRECTORY_SEPARATOR.$this->_iddir[1].DIRECTORY_SEPARATOR.$this->_iddir[2].DIRECTORY_SEPARATOR.$this->_iddir[3].DIRECTORY_SEPARATOR.$this->_id;

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

	// tworzenie katalogów

 	private function mkdir($path)
	{
		if ((is_dir($path))){
			chmod($path, 0777);
		}
		else{
			mkdir($path, 0777);
			chmod($path, 0777);
		}
	}

	// tworzenie drzewa katalogu opartego o ścieżkę

 	protected function createdir()
	{

		$array = array_merge(array($this->_module), $this->_iddir);

		if(!empty($this->_id)){
			$array[] = $this->_id;
			if(!empty($this->_folder)){
				$array[] = $this->_folder;
			}
		}
				
		$path = DBPATH;
		foreach($array as $a){
			$path = $path.$a.DIRECTORY_SEPARATOR;
			$this->mkdir($path);
		}
		return $this;
	}


	// usuwania pliku

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

	// zapisywanie pliku

 	protected function savefile()
	{
		if($this->_filepath){
		$file = $this->_path.DIRECTORY_SEPARATOR.$this->_filepath;
		}
		else{
		$file = $this->_path;
		}
		$file = $file.$this->_ext;
		$fp = fopen($file, 'w+');
		if(is_array($this->_data)){
			$this->_data = json_encode($this->_data);
		}
		fwrite($fp,$this->_data);
		fclose($fp);

	}

	// dodawania folderu głównego z opcją dodania subfolderu

 	public function addfolder($folder = FALSE)
	{
		if(!empty($folder)){
		$this->_folder = $folder;
		}
		$this->createdir();
		return $this;
	}

	// dodawania dokumentu z opcją utworzenie folderu

 	public function add($folder = FALSE, $data = array())
	{
		if($folder === TRUE){
		$this->createdir();
		}
		else{
		$id = $this->_id;
		$this->_id = FALSE;
		$this->createdir();
		$this->_id = $id;
		}
		$this->_data = $data;
		$this->savefile();
		return $this;
	}
	
	// zapisywanie danych

	public function save($data = array(),$file = FALSE)
	{
		$this->_filepath = $file;
		$array = $this->getarray($this->_id);

		$keys = array_keys($array);
		$newarray = array();
		foreach($keys as $key){

			if (array_key_exists($key, $data)) {

				$newarray[$key] = $data[$key];
		
			}
			else{

			$newarray[$key] = $array[$key];

			}
		
		}

		$this->_data = $newarray;
		$this->savefile();
		return $this;
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

	/* funkcje pobierania danych */

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

	protected function getarray($id, $module = FALSE)
	{
		if($module){
		$this->_module = $module;
		}
		$this->id($id);
		$file = $this->_path.$this->_ext;

		if(is_file($file)){
			return get_object_vars(json_decode(file_get_contents($file)));
		}
		else{
			return false;
		}

	}

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

			if($reverse === TRUE){
				$value = array_reverse($value);
			}

			if(!empty($limit)){
				$value = array_slice($value, 0, $limit);
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

	// zwracanie danych
	
 	public function render()
	{
		return $this->_data;
	}
}

?>
