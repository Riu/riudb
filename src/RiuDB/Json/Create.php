<?php namespace RiuDB\Json;

class Create extends \RiuDB\Json\Json {

 	private function mkdir($path)
	{
		if (is_dir($path)){
			//chmod("$path", 0775);
		}
		else{
			mkdir($path, 0777);
			chmod($path, 0777);
		}
	}

	// tworzenie drzewa katalogu opartego o ścieżkę

 	protected function createdir()
	{

		$array = array_merge(array($this->_collection), $this->_iddir);

		if(!empty($this->_id)){
			$array[] = $this->_id;
			if(!empty($this->_folder)){
				$array[] = $this->_folder;
			}
		}
				
		$path = $this->_dbdir;
		foreach($array as $a){
			$path = $path.$a.DIRECTORY_SEPARATOR;
			$this->mkdir($path);
		}
		return $this;
	}

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

	// dodawanie pliku w folderze głównym dokumentu

 	public function addfile($file, $data = array())
	{
		$this->createdir();
		$this->_filepath = $file;
		$this->_data = $data;
		$this->savefile();
		return $this;
	}
}
