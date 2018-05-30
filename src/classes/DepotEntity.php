<?php

class DepotEntity
{
    protected $id;
    protected $depot;  
	protected $nama;

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->depot = $data['depot']; 
		$this->nama = $data['nama'];
    }

    public function getId() {
        return $this->id;
    }
	 public function getNama() {
        return $this->nama;
    }

    public function getDepot() {
        return $this->depot;
    }
	
}
