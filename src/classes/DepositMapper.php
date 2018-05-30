<?php

class DepositMapper extends Mapper
{
	
    public function getDeposit() {
        $sql = "SELECT ds.id, ds.waktu, ds.nilai, d.depot
            from deposit ds
            join depot d on (ds.depot = d.id)";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new DepositEntity($row);
        }
        return $results;
    }
	
	public function getDepositById($deposit_id) {
        $sql = "SELECT ds.id, ds.waktu, ds.nilai, d.depot
            from deposit ds
            join depot d on (ds.depot = d.id) where ds.id = :deposit_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["deposit_id" => $deposit_id]);

        if($result) {
            return new DepositEntity($stmt->fetch());
        }

    }
	
	public function delete(DepositEntity $deposit) {
		 $sql = "DELETE FROM deposit WHERE id=:id";
		 
				 $stmt = $this->db->prepare($sql);
				 $result = $stmt->execute([
				 "id" => $deposit->getId(),
				 ]);
				$db = null;
		}
		
	public function save(DepositEntity $deposit) {
        $sql = "insert into deposit
            (waktu, nilai, depot) values
            (:waktu, :nilai, 
            (select id from depot where depot = :depot))";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "waktu" => $deposit->getWaktu(),
            "nilai" => $deposit->getNilai(),
            "depot" => $deposit->getDepot(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
}
