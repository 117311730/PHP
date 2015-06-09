--TEST--
MongoBinData insertion with various types
--SKIPIF--
<?php require dirname(__FILE__) . "/skipif.inc";?>
--FILE--
<?php
require_once dirname(__FILE__) . "/../utils.inc";
$mongo = mongo();
$coll = $mongo->selectCollection(dbname(), 'mongobindata');
$coll->drop();

$coll->insert(array('bin' => new MongoBinData('abc', MongoBinData::FUNC)));
$coll->insert(array('bin' => new MongoBinData('def', MongoBinData::BYTE_ARRAY)));
$coll->insert(array('bin' => new MongoBinData('ghi', MongoBinData::UUID)));
$coll->insert(array('bin' => new MongoBinData('jkl', MongoBinData::MD5)));
$coll->insert(array('bin' => new MongoBinData('mno', MongoBinData::CUSTOM)));

$cursor = $coll->find();

foreach ($cursor as $result) {
    printf("Type %d with data \"%s\"\n", $result['bin']->type, $result['bin']->bin);
}
?>
--EXPECT--
Type 1 with data "abc"
Type 2 with data "def"
Type 3 with data "ghi"
Type 5 with data "jkl"
Type 128 with data "mno"
