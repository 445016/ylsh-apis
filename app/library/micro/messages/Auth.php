<?php


namespace Micro\Messages;

class Auth {

	protected $_id;

	
	protected $_time;

	
	protected $_data;

	
	protected $_hash;


	public function __construct($id, $time, $hash, $data) {
            
		$this->_id = $id;
                
		$this->_hash = $hash;
                
		$this->_time = $time;
                
		$this->_data = $data;
                
	}

	
	public function getHash() {
            
		return $this->_hash;
                
	}

	public function getId() {
            
		return $this->_id;
                
	}

	public function getData() {
            
		return $this->_data;
                
	}

	public function getTime() {
            
		return $this->_time;
                
	}
}
