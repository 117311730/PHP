<?php
interface Component {
    public function getComposite(); //返回自己的实例
    public function operation();
}
 
class Composite implements Component { // 树枝组件角色
    private $_composites;
    public function __construct() { $this->_composites = array(); }
    public function getComposite() { return $this; }
     public function operation() {
         foreach ($this->_composites as $composite) {
            $composite->operation();
        }
     }
 
    public function add(Component $component) {  //聚集管理方法 添加一个子对象
        $this->_composites[] = $component;
    }
 
    public function remove(Component $component) { // 聚集管理方法 删除一个子对象
        foreach ($this->_composites as $key => $row) {
            if ($component == $row) { unset($this->_composites[$key]); return TRUE; }
        } 
        return FALSE;
    }

    public function getChild() { // 聚集管理方法 返回所有的子对象
       return $this->_composites;
    }
 
}
 
class Leaf implements Component {
    private $_name; 
    public function __construct($name) { $this->_name = $name; }
    public function operation() {}
    public function getComposite() {return null;}
}
 
// client
$leaf1 = new Leaf('first');
$leaf2 = new Leaf('second');

$composite = new Composite();
$composite->add($leaf1);
$composite->add($leaf2);
$composite->operation();

$composite->remove($leaf2);
$composite->operation();