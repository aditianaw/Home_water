<?php

class DepositEntity
{
    protected $id;
	protected $waktu;
    protected $nilai;
    protected $depot;

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        // no id if we're creating
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->waktu = $data['waktu'];
        $this->nilai = $data['nilai'];
        $this->depot = $data['depot'];
    }

	public function getId() {
        return $this->id;
    }

    public function getWaktu() {
        return $this->waktu;
    }

    public function getNilai() {
        return $this->nilai;
    }

    public function getDepot() {
        return $this->depot;
    }

  
}
