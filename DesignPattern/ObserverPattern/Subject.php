<?php

class Subject implements SplSubject {
    private $nome_materia;
    private $_observadores = array();
    private $_log = array();

    public function GET_materia() {
        return $this->nome_materia;
    }

    function SET_log($valor) {
        $this->_log[] = $valor ;
    }
    function GET_log() {
        return $this->_log;
    }

    function __construct($nome) {
        $this->nome_materia = $nome;
        $this->_log[] = " Subject $nome was included";
    }
    /* Adiciona um observador */
    public function attach(SplObserver $classes) {
        $this->_classes[] = $classes;
        $this->_log[] = " The ".$classes->GET_tipo()." ".$classes->GET_nome()." was included";
    }

    /* Remove um observador */
    public function detach(SplObserver $classes) {
        foreach ($this->_classes as $key => $obj) {
            if ($obj == $classes) {
                unset($this->_classes[$key]);
                $this->_log[] = " The ".$classes->GET_tipo()." ".$classes->GET_nome()." was removed";
            }
        }
    }

    /* Notifica os observadores */
    public function notify(){
        foreach ($this->_classes as $classes) {
            $classes->update($this);
        }
    }
}