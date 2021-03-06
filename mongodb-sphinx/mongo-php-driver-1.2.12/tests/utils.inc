<?php # vim: ft=php

function m(&$dsn, &$port, &$db, &$opts = array()) {
    $dsn  = "";
    $port = "27017";
    $opts = array();

    $type = $_ENV["MONGO_SERVER"];

    $prefix = "";
    switch($type) {
        case "BRIDGE_STANDALONE":
            $prefix = "BRIDGE_";
            /* break intentionally omitted */
        case "STANDALONE":
            $dsn                = $GLOBALS[$prefix."STANDALONE_HOSTNAME"];
            $port               = $GLOBALS["STANDALONE_PORT"];
            $db                 = $GLOBALS["STANDALONE_DATABASE"];
        return;

        case "BRIDGE_STANDALONE_AUTH":
            $prefix = "BRIDGE_";
        case "STANDALONE_AUTH":
            $dsn                = $GLOBALS[$prefix."STANDALONE_AUTH_HOSTNAME"];
            $port               = $GLOBALS["STANDALONE_AUTH_PORT"];
            if ($db == "admin") {
                $opts["username"]   = $GLOBALS["STANDALONE_AUTH_ADMIN_USERNAME"];
                $opts["password"]   = $GLOBALS["STANDALONE_AUTH_ADMIN_PASSWORD"];
            }
            else {
                $db                 = $GLOBALS["STANDALONE_AUTH_DATABASE"];
                $opts["username"]   = $GLOBALS["STANDALONE_AUTH_USERNAME"];
                $opts["password"]   = $GLOBALS["STANDALONE_AUTH_PASSWORD"];
            }
        return;

        case "REPLICASET":
            $dsn                = $GLOBALS["REPLICASET_PRIMARY"];
            $db                 = $GLOBALS["REPLICASET_DATABASE"];
            $port               = $GLOBALS["REPLICASET_PRIMARY_PORT"];
            $opts["replicaSet"] = $GLOBALS["REPLICASET_NAME"];
        return;

        case "REPLICASET_AUTH":
            $dsn                = $GLOBALS["REPLICASET_AUTH_PRIMARY"];
            $port               = $GLOBALS["REPLICASET_AUTH_PORT"];
            $opts["replicaSet"] = $GLOBALS["REPLICASET_NAME"];
            if ($db == "admin") {
                $opts["username"]   = $GLOBALS["REPLICASET_AUTH_ADMIN_USERNAME"];
                $opts["password"]   = $GLOBALS["REPLICASET_AUTH_ADMIN_PASSWORD"];
            } else {
                $opts["username"]   = $GLOBALS["REPLICASET_AUTH_USERNAME"];
                $opts["password"]   = $GLOBALS["REPLICASET_AUTH_PASSWORD"];
                $db                 = $GLOBALS["REPLICASET_AUTH_DATABASE"];
            }
        return;
    }
}
function mongo($usedb = null, $useprefix = true, $useport = true, $creds = array()) {

    m($dsn, $port, $usedb, $opts);

    if (!$dsn) {
        die(sprintf("skip The '%s' environment is not configured to be executed", $_ENV["MONGO_SERVER"]));
    }

    if ($useport) {
        $dsn .= ":$port";
    }

    if ($usedb) {
        if ($usedb[0] != "/") {
            $dsn .= "/";
        }
        $dsn .= $usedb;
    }

    if ($useprefix) {
        $dsn = "mongodb://$dsn";
    }

    if ($opts) {
        $m = new Mongo($dsn, $opts);
    }
    else {
        $m = new Mongo($dsn);
    }

    return $m;
}

function isauth() {
    if (empty($_ENV["MONGO_SERVER"])) {
        return false;
    }
    return (bool)strstr($_ENV["MONGO_SERVER"], "AUTH");
}
function dbname($db = null) {
    m($dsn, $port, $db, $opts);
    return $db;
}
function port($env = null) {
    if ($env) {
        $tmp = $_ENV["MONGO_SERVER"];
        $_ENV["MONGO_SERVER"] = $env;
    }

    m($dsn, $port, $db, $opts);

    if ($env) {
        $_ENV["MONGO_SERVER"] = $tmp;
    }
    return $port;
}

function hostname($env = null) {
    if ($env) {
        $tmp = $_ENV["MONGO_SERVER"];
        $_ENV["MONGO_SERVER"] = $env;
    }

    m($dsn, $port, $db, $opts);

    if ($env) {
        $_ENV["MONGO_SERVER"] = $tmp;
    }
    return $dsn;
}

function username($db = null) {
    m($dsn, $port, $db, $opts);
    if (isset($opts["username"])) {
        return $opts["username"];
    }
    return null;
}

function password($db = null) {
    m($dsn, $port, $db, $opts);
    if (isset($opts["password"])) {
        return $opts["password"];
    }
    return null;
}

if (!function_exists("iterator_to_array")) {
    function iterator_to_array($it) {
        $retval = array();
        foreach($it as $v) {
            $retval[] = $v;
        }
        return $retval;
    }
}
if (!defined("E_DEPRECATED")) {
    define("E_DEPRECATED", E_STRICT);
}


$STANDALONE_HOSTNAME = "localhost";
$BRIDGE_STANDALONE_HOSTNAME = "";
$STANDALONE_AUTH_HOSTNAME = "";
$BRIDGE_STANDALONE_AUTH_HOSTNAME = "";

$STANDALONE_AUTH_ADMIN_USERNAME = "";
$STANDALONE_AUTH_ADMIN_PASSWORD = "";
$STANDALONE_AUTH_USERNAME = "";
$STANDALONE_AUTH_PASSWORD = "";
$STANDALONE_AUTH_DATABASE = "";
$STANDALONE_DATABASE      = "";

$REPLICASET_AUTH_ADMIN_USERNAME = "";
$REPLICASET_AUTH_ADMIN_PASSWORD = "";
$REPLICASET_AUTH_USERNAME = "";
$REPLICASET_AUTH_PASSWORD = "";
$STANDALONE_AUTH_DATABASE = "";

$REPLICASET_NAME       = "RS";
$REPLICASET_DATABASE = "";
$REPLICASET_PRIMARY    = "";
$REPLICASET_SECONDARY  = "";
$REPLICASET_ARBITER    = "";

$REPLICASET_AUTH_PRIMARY = "";
$REPLICASET_AUTH_ADMIN_USERNAME = "";
$REPLICASET_AUTH_ADMIN_PASSWORD = "";
$REPLICASET_AUTH_USERNAME = "";
$REPLICASET_AUTH_PASSWORD = "";
$REPLICASET_AUTH_DATABASE = "";

$STANDALONE_PORT =
    $STANDALONE_AUTH_PORT =
    $REPLICASET_PORT =
    $REPLICASET_PRIMARY_PORT =
    $REPLICASET_SECONDARY_PORT =
    $REPLICASET_ARBITER_PORT =
    $REPLICASET_AUTH_PORT = 27017;

// Include user define config file to overwrite the defaults
require "mongo-test-cfg.inc";

$RS_HOSTS = array();
if ($REPLICASET_PRIMARY) {
    $RS_HOSTS[] = "$REPLICASET_PRIMARY:$REPLICASET_PRIMARY_PORT";
}
if ($REPLICASET_SECONDARY) {
    $RS_HOSTS[] = "$REPLICASET_SECONDARY:$REPLICASET_SECONDARY_PORT";
}

if ($REPLICASET_ARBITER) {
    $RS_HOSTS[] = "$REPLICASET_ARBITER:$REPLICASET_ARBITER_PORT";
}

$REPLICASET_HOSTNAME   = join(",", $RS_HOSTS);

if (empty($_ENV["MONGO_SERVER"])) {
    $self = $_SERVER["PHP_SELF"];
    $type = basename(dirname($self));
    $type = strtoupper($type);
    $type = str_replace("-", "_", $type);

    switch ($type) {
    case "GENERIC":
        $type = "STANDALONE";
        break;

    }
    $_ENV["MONGO_SERVER"] = $type;
}

