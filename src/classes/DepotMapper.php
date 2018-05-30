<?php

class DepotMapper extends Mapper
{
    public function getDepot() {
        $sql = "SELECT id,nama, depot
            from depot order by id asc";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new DepotEntity($row);
        }
        return $results;
    }
	
	public function getDepotDelivery() {
        $sql = "SELECT id,nama, depot
            from depot where nama = 'delivery' order by id asc ";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new DepotEntity($row);
        }
        return $results;
    }
	
	public function save(DepotEntity $depot) {
        $sql = "insert into depot
            (id, nama,depot) values
            (:id,:nama, :depot)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" => $depot->getId(),	
			"nama" => $depot->getNama(),
            "depot" => $depot->getDepot(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
	

    public function getDepotById($depot_id) {
        $sql = "SELECT id, depot, nama
         from depot where id = :depot_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["depot_id" => $depot_id]);

        return new DepotEntity($stmt->fetch());
    }
	
	public function delete(DepotEntity $depot) {
		 $sql = "DELETE FROM depot WHERE id=:id";
		 
				 $stmt = $this->db->prepare($sql);
				 $result = $stmt->execute([
				 "id" => $depot->getId(),
				 ]);
				$db = null;
		}

	
}	
	

