<?php
$config = include('db.config.php');
class EMongoClient
{
    private $server;
    private $options;
    private $db;
    private $_db;
    public function __construct($config)
    {
        foreach($config as $k=>$v){
            $this->$k = $v;
        }
        $this->connect();
    }
    
    public function connect()
    {
        if($this->db)
            $this->options['db'] = $this->db;
        if(version_compare(phpversion('mongo'), '1.3.0', '<')){
            $this->_db = new Mongo($this->server, $this->options);
        }else{
             $this->_db = new MongoClient($this->server, $this->options);
        }
        $this->_db = $this->_db->selectDb($this->db);
    }
   
    public function getDB()
    {
        return $this->_db;
    }
   
    public function getTestData()
    {
        return $this->_db->products_test->find();
    }

    public function getProducts($w = array())
    {
        return $this->_db->products->find($w);
    }
    
    public function getCategories($w = array())
    {
        return $this->_db->categories->find($w);
    }
    
    public function getBrands($w = array())
    {
        return $this->_db->brands->find($w);
    }
    
    public function getBrandIjie($w = array())
    {
        return $this->_db->brandIjie->find($w);
    }
    
    public function showCatName($catId)
    {
        $id = 0;
        if(is_array($catId)){
            $id = end($catId);
        }else{
            $id = (int)$catId;
        }
        static $_cat;
        if(empty($_cat)){
            $res = $this->getCategories();
            foreach($res as $v){
                $_cat[$v['_id']] = $v['catName'];
            }
        }
        return isset($_cat[$id])?$_cat[$id]:' ';
    }
    
    public function showBrandName($brandId)
    {
        static $_b;
        if(empty($_b)){
            $res = $this->getBrands();
            foreach($res as $v){
                $_b[$v['_id']] = $v['bName'];
            }
        }
        return isset($_b[$brandId])?$_b[$brandId]:' ';
    }
    
    public function showTag($tag){
        return implode(' ',$tag);
    }
    
    public function showPrice($p)
    {
        return number_format($p, 2, '.', '');
    }
    
    public function showAttr($attr){
        if(empty($attr))
            return null;
        $res = array();
        foreach($attr as $k=>$v){
            if(is_array($v)){
                foreach($v as $_v){
                    $res[] = (string)$k.'_'.(string)$_v;
                }
            }else{
                $res[] = (string)$k.'_'.(string)$v;
            }
        }
        return implode(' ',$res);
    }
}
