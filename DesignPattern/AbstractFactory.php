<?php
    abstract class AbstractFactory {
        abstract public function CreateButton();
        abstract public function CreateBorder();
    }

    class MacFactory extends AbstractFactory{
        public function CreateButton()
        {
            
            return new MacButton();
        }
        public function CreateBorder()
        {
            return new MacBorder();
        }
    }
    class WinFactory extends AbstractFactory{
        public function CreateButton()
        {
            return new WinButton();
        }
        public function CreateBorder()
        {
            return new WinBorder();
        }
    }
    class Button{}
    class Border{}

    class MacButton extends Button{
        function __construct()
        {
            echo 'MacButton is created' . "\n";
        }
    }
    class MacBorder extends Border{
        function __construct()
        {
            echo 'MacBorder is created' . "\n";
        }
    }

    class WinButton extends Button{
        function __construct()
        {
            echo 'WinButton is created' . "\n";
        }
    }
    class WinBorder extends Border{
        function __construct()
        {
            echo 'WinBorder is created' . "\n";
        }
    }
    
    
    
    
$type = 'Mac'; //value by user.
if(!in_array($type, array('Win','Mac')))
    die('Type Error');
$factoryClass = $type.'Factory';
$factory=new $factoryClass;
$factory->CreateButton();
$factory->CreateBorder();