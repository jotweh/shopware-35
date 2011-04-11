<?php
class	Shopware_Components_Db_Export_Sql extends Shopware_Components_Db_Export_Abstract
{	
	public function createData($limit, $offset = 0)
	{
		$sql = 'SELECT * FROM '.$this->db->quoteIdentifier($this->table);
		$sql = $this->db->limit($sql, $limit, $offset);
		$result = $this->db->query($sql);
		if(!$result->rowCount()) {
			return false;
		}
		
		$rows = array();
		while ($values = $result->fetch(Zend_Db::FETCH_NUM)) {
			$row = array();
			foreach ($values as $value) {
				$row[] = $this->db->quote($value);
			}
			$rows[] = implode(', ', $row);
		}
		$rows = implode("),\n(", $rows);
		
		
		$fields = array();
		foreach ($this->fields as $field=>$fieldInfo) {
			$fields[] = $this->db->quoteIdentifier($field);
		}
		$fields = implode(', ', $fields);
		
		$return = 'INSERT INTO '.$this->db->quoteIdentifier($this->table)." ($fields) VALUES\n($rows);\n";

		return $return;
	}
	
	public function createTable($newTable = null)
	{
		if($newTable === null) {
			$newTable = $this->table;
		}
		$return_sql = "\nDROP TABLE IF EXISTS `$newTable`;";
		$return_sql .= "\nCREATE TABLE `$newTable` (\n";
		
		$sql = "SHOW FULL COLUMNS FROM `$this->table`";
		$result = $this->db->query($sql);
		
		if($result->rowCount()) {
			$lines = array();
			while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
				$line = "`{$row['Field']}` {$row['Type']} ";
				if($row['Null'] != 'YES') {
					$line .= 'NOT NULL ';
				}
				if($row['Default'] == 'CURRENT_TIMESTAMP') {
					$line .= 'default CURRENT_TIMESTAMP ';
				} elseif(!empty($row['Default']) || $row['Default'] === '0') {
					$line .= "default '{$row['Default']}'";
				} elseif ($row['Null'] == 'YES') {
					$line .= 'default NULL';
				}
				if(!empty($row['Extra'])) {
					$line .= $row['Extra'];
				}
				$lines[] = $line;
			}
			foreach ($this->getTableIndexes() as $type => $indexes) {
				foreach ($indexes as $name => $fields) {
					$line = $type;
					if($type != 'INDEX') {
						$line .= " KEY";
					}
					if($type != 'PRIMARY') {
						$line .= " `$name`";
					}
					$line .= " (`".implode("`, `", $fields)."`)";
					$lines[] = $line;
				}
			}
			$return_sql .= "\t".implode(",\n\t", $lines)."\n";
		}
		$return_sql .= ')';
				
		$sql = 'SHOW TABLE STATUS WHERE Name=?';
		$status = $this->db->fetchRow($sql, array($this->table), Zend_Db::FETCH_ASSOC);
		
		if(!empty($status['Engine'])) {
			$return_sql .= ' ENGINE='.$status['Engine'];
		}
		$return_sql .= ' DEFAULT CHARSET=latin1';
		if(!empty($status['Auto_increment'])) {
			$return_sql .= ' AUTO_INCREMENT='.$status['Auto_increment'];
		}
			
		$return_sql .= ";\n\n";
		return $return_sql;
	}
		
	public function getTableIndexes()
	{
		$keys = array('PRIMARY'=>array(), 'FULLTEXT'=>array(), 'UNIQUE'=>array(), 'INDEX'=>array());
		
		$sql = "SHOW KEYS FROM `$this->table`";
		$result = $this->db->query($sql);
		
		if($result->rowCount()) {
			while ($row = $result->fetch(Zend_Db::FETCH_ASSOC)) {
				if ($row['Key_name'] == 'PRIMARY') {
					$keys["PRIMARY"]["PRIMARY"][] = $row['Column_name'];
				} elseif ($row['Index_type'] == 'FULLTEXT') {
					$keys["FULLTEXT"][$row['Key_name']][] = $row['Column_name'];
				} elseif  ($row['Non_unique'] == 0) {
					$keys["UNIQUE"][$row['Key_name']][] = $row['Column_name'];
				} else {
					$keys["INDEX"][$row['Key_name']][] = $row['Column_name'];
				}
			}
		}
		
		return $keys;
	}
}