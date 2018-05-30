<?php

class UserEntity
{
    protected $username;
    protected $password;
	protected $nama;
	protected $usertype;

    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        $this->username = $data['username'];
        $this->password = $data['password'];
		$this->nama 	= $data['nama'];
		$this->usertype = $data['usertype'];
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }
	
	public function getNama() {
        return $this->nama;
    }
	
	public function getUserType() {
        return $this->usertype;
    }
}
