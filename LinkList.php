<?php
class LinkList {
	/**
	 * 成员变量
	 *
	 * @var array $linkList 链表数组
	 * @var number $listHeader 表头索引
	 * @var number $listLength 链表长度
	 * @var number $existedCounts 记录链表中出现过的元素的个数，和$listLength不同的是, 删除一
	 *      个元素之后，该值不需要减1，这个也可以用来为新元素分配索引。
	 */
	protected $linkList = array ();
	protected $listLength = 0;
	protected $listHeader = null;
	protected $existedCounts = 0;
	/**
	 * 构造函数
	 * 构造函数可以带一个数组参数，如果有参数，则调用成员方法
	 * createList将数组转换成链表，并算出链表长度.如果没有参
	 * 数，则生成一空链表.空链表可以通过调用成员方法createList
	 * 生成链表.
	 *
	 * @access public
	 * @param array $arr
	 *        	需要被转化为链表的数组
	 */
	public function __construct($arr = '') {
		$arr != null && $this->createList ( $arr );
	}
	/**
	 * 生成链表的函数
	 * 将数组转变成链表，同时计算出链表长度。分别赋值给成员标量
	 * $linkList和$listLength.
	 *
	 * @access public
	 * @param array $arr
	 *        	需要被转化为链表的数组
	 * @return boolean true表示转换成功，false表示失败
	 */
	public function createList($arr) {
		if (! is_array ( $arr ))
			return false;
		$length = count ( $arr );
		for($i = 0; $i < $length; $i ++) {
			if ($i == $length - 1) {
				// 每个链表结点包括var和next两个索引，var表示结点值，next为下一个结点的索引
				// 最后一个结点的next为null
				$list [$i] ['var'] = $arr [$i];
				$list [$i] ['next'] = null;
			} else {
				$list [$i] ['var'] = $arr [$i];
				$list [$i] ['next'] = $i + 1;
			}
		}
		$this->linkList = $list;
		$this->listLength = $length;
		$this->existedCounts = $length;
		$this->listHeader = 0;
		return true;
	}
	/**
	 * 将链表还原成一维数组
	 *
	 * @access public
	 * @return array $arr 生成的一维数组
	 */
	public function returnToArray() {
		$arr = array ();
		$tmp = $this->linkList [$this->listHeader];
		for($i = 0; $i < $this->listLength; $i ++) {
			$arr [] = $tmp ['var'];
			if ($i != $this->listLength - 1) {
				$tmp = $this->linkList [$tmp ['next']];
			}
		}
		return $arr;
	}
	public function getLength() {
		return $this->listLength;
	}
	/**
	 * 计算一共删除过多少个元素
	 *
	 * @access public
	 * @return number $count 到目前为止删除过的元素个数
	 */
	public function getDeletedNums() {
		$count = $this->existedCounts - $this->listLength;
		return $count;
	}
	/**
	 * 通过元素索引返回元素序号
	 *
	 * @access protected
	 * @param $index 元素的索引号        	
	 * @return $num 元素在链表中的序号
	 */
	public function getElemLocation($index) {
		if (! array_key_exists ( $index, $this->linkList ))
			return false;
		$arrIndex = $this->listHeader;
		for($num = 1; $tmp = $this->linkList [$arrIndex]; $num ++) {
			if ($index == $arrIndex)
				break;
			else {
				$arrIndex = $tmp ['next'];
			}
		}
		return $num;
	}
	/**
	 * 获取第$i个元素的引用
	 * 这个保护方法不能被外界直接访问，许多服务方法以来与次方法。
	 * 它用来返回链表中第$i个元素的引用，是一个数组
	 *
	 * @access protected
	 * @param number $i
	 *        	元素的序号
	 * @return reference 元素的引用
	 */
	protected function &getElemRef($i) {
		// 判断$i的类型以及是否越界
		$result = false;
		if (! is_numeric ( $i ) || ( int ) $i <= 0 || ( int ) $i > $this->listLength)
			return $result;
			// 由于单链表中的任何两个元素的存储位置之间没有固定关系，要取得第i个元素必须从
			// 表头开始查找，因此单链表是非随机存储的存储结构。
		$j = 0;
		$value = &$this->linkList [$this->listHeader];
		while ( $j < $i - 1 ) {
			$value = &$this->linkList [$value ['next']];
			$j ++;
		}
		return $value;
	}
	/**
	 * 返回第i个元素的值
	 *
	 * @access public
	 * @param number $i
	 *        	需要返回的元素的序号，从1开始
	 * @return mixed 第i个元素的值
	 */
	public function getElemvar($i) {
		$var = $this->getElemRef ( $i );
		if ($var != false) {
			return $var ['var'];
		} else
			return false;
	}
	/**
	 * 在第i个元素之后插入一个值为var的新元素
	 * i的取值应该为[1,$this->listLength]，如果i=0，表示在表的最前段插入，
	 * 如果i=$this->listLength，表示在表的末尾插入,插入的方法为，将第$i-1个元素
	 * 的next指向第$i个元素，然后将第$i个元素的next指向第$i+1个元素，这样就实现了插入
	 *
	 * @access public
	 * @param number $i
	 *        	在位置i插入新元素
	 * @param mixed $var
	 *        	要插入的元素的值
	 * @return boolean 成功则返回true,否则返回false
	 */
	public function insertIntoList($i, $var) {
		if (! is_numeric ( $i ) || ( int ) $i < 0 || ( int ) $i > $this->listLength)
			return false;
		if ($i == 0) {
			// 如果$i-0，则在表最前面添加元素，新元素索引为$listLength，这样是确保不会
			// 覆盖原来的元素，另外这种情况需要重新设置$listHeader
			$this->linkList [$this->existedCounts] ['var'] = $var;
			$this->linkList [$this->existedCounts] ['next'] = $this->listHeader;
			$this->listHeader = $this->existedCounts;
			$this->listLength ++;
			$this->existedCounts ++;
			return true;
		}
		$value = &$this->getElemRef ( $i );
		$this->linkList [$this->existedCounts] ['var'] = $var;
		$this->linkList [$this->existedCounts] ['next'] = ($i == $this->listLength ? null : $value ['next']);
		$value ['next'] = $this->existedCounts;
		$this->listLength ++;
		$this->existedCounts ++;
		return true;
	}
	/**
	 * 删除第$i个元素
	 * 删除第$i个元素，该元素为取值应该为[1，$this->listLength],需要注意，删除元素之后，
	 * $this->listLength减1,而$this->existedCounts不变。删除的方法为将第$i-1个元素的
	 * next指向第$i+1个元素，那么第$i个元素就从链表中删除了。
	 *
	 * @access public
	 * @param number $i
	 *        	将要被删除的元素的序号
	 * @return boolean 成功则返回true,否则返回false
	 */
	public function delFromList($i) {
		if (! is_numeric ( $i ) || ( int ) $i <= 0 || ( int ) $i > $this->listLength)
			return false;
		if ($i == 1) {
			// 若删除的结点为头结点，则需要从新设置链表头
			$tmp = $this->linkList [$this->listHeader];
			unset ( $this->linkList [$this->listHeader] );
			$this->listHeader = $tmp ['next'];
			$this->listLength --;
			return true;
		} else {
			$value = &$this->getElemRef ( $i );
			$prevValue = &$this->getElemRef ( $i - 1 );
			unset ( $this->linkList [$prevValue ['next']] );
			$prevValue ['next'] = $value ['next'];
			$this->listLength --;
			return true;
		}
	}
	/**
	 * 对链表的元素排序
	 * 谨慎使用此函数，排序后链表将被从新初始化，原有的成员变量将会被覆盖
	 * @accse public
	 *
	 * @param boolean $sortType='true'
	 *        	排序方式,true表示升序，false表示降序，默认true
	 */
	public function listSort($sortType = 'true') {
		// 从新修改关联关系可能会更复杂，所以我选择先还原成一维数组，然后对数组排序，然后再生成链表
		$arr = $this->returnToArray ();
		$sortType ? sort ( $arr ) : rsort ( $arr );
		$this->createList ( $arr );
	}
}
?> 



<?php
class Node {
	private $Data; // 节点数据
	private $Next; // 下一节点
	public function setData($value) {
		$this->Data = $value;
	}
	public function setNext($value) {
		$this->Next = $value;
	}
	public function getData() {
		return $this->Data;
	}
	public function getNext() {
		return $this->Next;
	}
	public function __construct($data, $next) {
		$this->setData ( $data );
		$this->setNext ( $next );
	}
}
class LinkList {
	private $header; // 头节点
	private $size; // 长度
	public function getSize() {
		$i = 0;
		$node = $this->header;
		while ( $node->getNext () != null ) {
			$i ++;
			$node = $node->getNext ();
		}
		return $i;
	}
	public function setHeader($value) {
		$this->header = $value;
	}
	public function getHeader() {
		return $this->header;
	}
	public function __construct() {
		header ( "content-type:text/html; charset=utf-8" );
		$this->setHeader ( new Node ( null, null ) );
	}
	/**
	 *
	 * @param
	 *        	$data--要添加节点的数据
	 *        	
	 */
	public function add($data) {
		$node = $this->header;
		while ( $node->getNext () != null ) {
			$node = $node->getNext ();
		}
		$node->setNext ( new Node ( $data, null ) );
	}
	/**
	 *
	 * @param
	 *        	$data--要移除节点的数据
	 *        	
	 */
	public function removeAt($data) {
		$node = $this->header;
		while ( $node->getData () != $data ) {
			$node = $node->getNext ();
		}
		$node->setNext ( $node->getNext () );
		$node->setData ( $node->getNext ()->getData () );
	}
	/**
	 *
	 * @param
	 *        	遍历
	 *        	
	 */
	public function get() {
		$node = $this->header;
		if ($node->getNext () == null) {
			print ("数据集为空!") ;
			return;
		}
		while ( $node->getNext () != null ) {
			print ('[' . $node->getNext ()->getData () . '] -> ') ;
			if ($node->getNext ()->getNext () == null) {
				break;
			}
			$node = $node->getNext ();
		}
	}
	/**
	 *
	 * @param
	 *        	$data--要访问的节点的数据
	 * @param
	 *        	此方法只是演示不具有实际意义
	 *        	
	 */
	public function getAt($data) {
		$node = $this->header->getNext ();
		if ($node->getNext () == null) {
			print ("数据集为空!") ;
			return;
		}
		while ( $node->getData () != $data ) {
			if ($node->getNext () == null) {
				break;
			}
			$node = $node->getNext ();
		}
		return $node->getData ();
	}
	/**
	 *
	 * @param $value--需要更新的节点的原数据 --$initial---更新后的数据        	
	 *
	 */
	public function update($initial, $value) {
		$node = $this->header->getNext ();
		if ($node->getNext () == null) {
			print ("数据集为空!") ;
			return;
		}
		while ( $node->getData () != $data ) {
			if ($node->getNext () == null) {
				break;
			}
			$node = $node->getNext ();
		}
		$node->setData ( $initial );
	}
}
$lists = new LinkList ();
$lists->add ( 1 );
$lists->add ( 2 );
$lists->get ();
echo '<pre>';
print_r ( $lists );
echo '</pre>';
?>
