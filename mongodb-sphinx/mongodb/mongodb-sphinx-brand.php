<?php
ini_set('display_errors', 0);
include 'SphinxXMLFeed.php';
include 'EMongoClient.php';
$config = include('db.config.php');
$doc = new SphinxXMLFeed();
$EMongoClient = new EMongoClient($config);

    $doc->setFields(array(
        'f_bname',
        'f_summary',
    ));

    $doc->setAttributes(array(
        array('name' => 'bname', 'type' => 'string'),
        array('name' => 'summary', 'type' => 'string'),
        array('name' => 'logo', 'type' => 'string'),
        array('name' => 'coverimg', 'type' => 'string'),
        array('name' => 'sort', 'type' => 'int'),
        array('name' => 'updatetime', 'type' => 'int'),
    ));

    $doc->beginOutput();
    
    $brand = $EMongoClient->getBrandIjie();
    foreach ($brand as $v) {

	$doc->addDocument(array(
            'id' => $v['_id'],
            'f_bname' => $v['bName'],
            'f_summary' => $v['summary'],
            'bname' => $v['bName'],
            'summary' =>$v['summary'],
            'logo' => $v['logo'],
            'coverimg' => $v['coverImg'],
            'sort' => $v['sort'],
            'updatetime' => $v['updateTime']
        ));
    }

    $doc->endOutput();
