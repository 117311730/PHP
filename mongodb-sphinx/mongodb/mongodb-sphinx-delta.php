<?php
ini_set('display_errors', 0);
include 'SphinxXMLFeed.php';
include 'EMongoClient.php';
$config = include('db.config.php');
$doc = new SphinxXMLFeed();
$EMongoClient = new EMongoClient($config);

    $doc->setFields(array(
        'f_pname',
        'f_catname',
        'f_brandname',
        'f_tag',
        'f_attr',

    ));

    $doc->setAttributes(array(
        array('name' => 'pname', 'type' => 'string'),
        array('name' => 'price_default', 'type' => 'float'),
        array('name' => 'price_member', 'type' => 'float'),
        array('name' => 'price_market', 'type' => 'float'),
        array('name' => 'catid', 'type' => 'multi'),
        array('name' => 'catname', 'type' => 'string'),
        array('name' => 'brandid', 'type' => 'int'),
        array('name' => 'brandname', 'type' => 'string'),
        array('name' => 'tag', 'type' => 'string'),
        array('name' => 'coverImg', 'type' => 'string'),
        array('name' => 'status', 'type' => 'int'),
        array('name' => 'like', 'type' => 'int'),
        array('name' => 'weight', 'type' => 'int'),
        array('name' => 'totalComment', 'type' => 'int'),
        array('name' => 'totalSold', 'type' => 'int'),
        array('name' => 'hot', 'type' => 'int'),
        array('name' => 'trial', 'type' => 'int'),
        array('name' => 'recommend', 'type' => 'int'),
        array('name' => 'popularity', 'type' => 'int'),
        array('name' => 'updatetime', 'type' => 'int'),
        array('name' => 'publishDate', 'type' => 'int'),
        array('name' => 'attr', 'type' => 'string'),
    ));

    $doc->beginOutput();
    
    $products = $EMongoClient->getProducts(array('updateTime'=>array('$gte'=>time()-(31*60),'$lte'=>time())));
    foreach ($products as $v) {
       	$catname = $EMongoClient->showCatName($v['catId']);
        $brandname = $EMongoClient->showBrandName($v['brandId']);
        $tag = $EMongoClient->showTag($v['tag']);
        $attr = $EMongoClient->showAttr($v['attr']);
        $doc->addDocument(array(
            'id' => $v['_id'],
            'f_pname' => $v['pName'],
            'f_catname' =>$catname,
            'f_brandname' =>$brandname,
            'f_tag' =>$tag, 
            'f_attr' =>$attr,
            'pname' => $v['pName'],
            'price_default' => $v['price']['default'],
            'price_member' => $v['price']['member'],
            'price_market' => $v['price']['market'],
            'catid' => json_encode($v['catId']),
            'catname' => $catname,
            'brandid' => $v['brandId'],
            'brandname' =>$brandname,
          //  'tag' => $EMongoClient->showTag($v['tag']),
            'coverImg' => isset($v['coverImg'])?$v['coverImg']:'',
            'status' => $v['status'],
            'like' => $v['like'],
            'weight' => $v['weight'],
            'totalComment' => !empty($v['totalComment'])?$v['totalComment']:0,
            'totalSold' => !empty($v['totalSold'])?$v['totalSold']:0,
            'hot' => $v['hot'],
            'trial'=> $v['trial'],
            'recommend' => $v['recommend'],
            'popularity' => $v['popularity'],
            'updatetime' => $v['updateTime'],
            'publishDate' => !empty($v['publishDate'])?$v['publishDate']:0,
            'attr' =>$attr,              
        ));
        $doc->addKillLists($v['_id']);
    }
    
    $doc->setKillLists();
    $doc->endOutput();
