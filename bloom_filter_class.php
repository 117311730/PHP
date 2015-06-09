<?php

/*
* php Bloom Filter
*/

error_reporting(E_ALL);

class bloom_filter {

	function __construct($hash_func_num=1, $space_group_num=1) {
		$max_length = pow(2, 25);
		$binary = pack('C', 0);

		//1字节占用8位
		$this->one_num = 8;

		//默认32m*1
		$this->space_group_num = $space_group_num;
		$this->hash_space_assoc = array();

		//分配空间
		for($i=0; $i<$this->space_group_num; $i++){
			$this->hash_space_assoc[$i] = str_repeat($binary, $max_length);
		}

		$this->pow_array = array(
			0 => 1,
			1 => 2,
			2 => 4,
			3 => 8,
			4 => 16,
			5 => 32,
			6 => 64,
			7 => 128,
		);
		$this->chr_array = array();
		$this->ord_array = array();
		for($i=0; $i<256; $i++){
			$chr = chr($i);
			$this->chr_array[$i] = $chr;
			$this->ord_array[$chr] = $i;
		}

		$this->hash_func_pos = array(
			0 => array(0, 7, 1),
			1 => array(7, 7, 1),
			2 => array(14, 7, 1),
			3 => array(21, 7, 1),
			4 => array(28, 7, 1),
			5 => array(33, 7, 1),
			6 => array(17, 7, 1),
		);

		$this->write_num = 0;
		$this->ext_num = 0;

		if(!$hash_func_num){
			$this->hash_func_num = count($this->hash_func_pos);
		}
		else{
			$this->hash_func_num = $hash_func_num;
		}
	}

	function add($key) {
		$hash_bit_set_num = 0;
		$hash_basic = sha1($key);

		$hash_space = hexdec(substr($hash_basic, 0, 4));
		$hash_space = $hash_space % $this->space_group_num;

		for($hash_i=0; $hash_i<$this->hash_func_num; $hash_i++){
			$hash = hexdec(substr($hash_basic, $this->hash_func_pos[$hash_i][0], $this->hash_func_pos[$hash_i][1]));
			$bit_pos = $hash >> 3;
			$max = $this->ord_array[$this->hash_space_assoc[$hash_space][$bit_pos]];
			$num = $hash - $bit_pos * $this->one_num;
			$bit_pos_value = ($max >> $num) & 0x01;
			if(!$bit_pos_value){
				$max = $max | $this->pow_array[$num];
				$this->hash_space_assoc[$hash_space][$bit_pos] = $this->chr_array[$max];
				$this->write_num++;
			}
			else{
				$hash_bit_set_num++;
			}
		}
		if($hash_bit_set_num == $this->hash_func_num){
			$this->ext_num++;
			return true;
		}
		return false;
	}

	function get_stat() {
		return array(
			'ext_num' => $this->ext_num,
			'write_num' => $this->write_num,
		);
	}
}


//test
//取6个哈希值，目前是最多7个
$hash_func_num = 6;

//分配1个存储空间，每个空间为32M，理论上是空间越大误判率越低，注意php.ini中可使用的内存限制
$space_group_num = 1;

$bf = new bloom_filter($hash_func_num, $space_group_num);

$list = array(
	'http://test/1',
	'http://test/2',
	'http://test/3',
	'http://test/4',
	'http://test/5',
	'http://test/6',
	'http://test/1',
	'http://test/2',
);
foreach($list as $k => $v){

	if($bf->add($v)){
		echo $v, "\n";
	}
}
print_r($bf->get_stat());

//EOF