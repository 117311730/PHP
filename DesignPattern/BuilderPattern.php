<?php
//設計模式：生成器模式
//Coder:  rollenc ( http://www.rollenc.com )
interface Builder {
	function buildPartA(); //创建部件A比如创建汽车车轮
	//创建部件B 比如创建汽车方向盘
	function buildPartB(); 
	//创建部件C 比如创建汽车发动机
	function buildPartC(); 

	//返回最后组装成品结果 (返回最后装配好的汽车)
	//成品的组装过程不在这里进行,而是转移到下面的Director类中进行.
	//从而实现了解耦过程和部件
	//return Product
	function getResult(); 
}

class Director {
	private $builder; 

	public function __construct($builder ) { 
		$this->builder = $builder; 
	} 
	// 将部件partA partB partC最后组成复杂对象
	//这里是将车轮 方向盘和发动机组装成汽车的过程
	public function construct() {
		$this->builder->buildPartA();
		$this->builder->buildPartB();
		$this->builder->buildPartC();
	}
}

class ConcreteBuilder implements Builder {
	public $partA, $partB, $partC;
	public function buildPartA() {
		echo 'partA is builded' . "\n";
	}
	public function buildPartB() {
		echo 'partB is builded' . "\n";
	}
	public function buildPartC() {
		echo 'partC is builded' . "\n";
	}
	public function getResult () {
		echo 'Return product.' . "\n";
		return 1;
	}
}

$builder = new ConcreteBuilder();
$director = new Director( $builder ); 

$director->construct(); 
$product = $builder->getResult(); 