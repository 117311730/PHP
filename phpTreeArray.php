<?php
	$data = array(array('id'=>1,'pid'=>0),array('id'=>2,'pid'=>0),array('id'=>3,'pid'=>2),array('id'=>4,'pid'=>0),array('id'=>5,'pid'=>3),array('id'=>6,'pid'=>1),array('id'=>7,'pid'=>1),array('id'=>8,'pid'=>6),array('id'=>9,'pid'=>7),array('id'=>10,'pid'=>9));
	
	function treeArray($d) {
		$result = array();
		$I = array();
		foreach($d as $val) {
			if($val['pid'] == 0) {
			   	$i = count($result);
				$result[$i] = $val;
				$I[$val['id']] = & $result[$i];
			}else {
				$i = count($I[$val['pid']]['child']);
				$I[$val['pid']]['child'][$i] = $val;
				$I[$val['id']] = & $I[$val['pid']]['child'][$i];
			}
		}
		return $result;
	}
	print_r(treeArray($data));
?>