<?php namespace RiuDB\Json;

class Update extends \RiuDB\Json\Json {
	
	public function save($data = array(),$file = FALSE,$puts = FALSE)
	{

        $this->_filepath = $file;
        
		
		$array = $this->getarray($this->_id);

		$keys = array_keys($array);

		// dopisywanie nowych kluczy do tablicy
		if(!empty($puts) AND is_array($puts )){
			foreach($puts as $p){
				$keys[] = $p;
			}
		}

		$newarray = array();
		foreach($keys as $key){

			if (array_key_exists($key, $data)) {
				if($data[$key] === NULL)
				{
					$data[$key] = '';
				}
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

}
