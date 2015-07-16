<?php
class Teacher implements SplObserver{
    protected $tipo = "Teacher";
    private $nome;
    private $endereco;
    private $telefone;
    private $email;
    private $_classes = array();

    public function GET_tipo() {
        return $this->tipo;
    }

    public function GET_nome() {
        return $this->nome;
    }

    public function GET_email() {
        return $this->email;
    }

    public function GET_telefone() {
        return $this->nome;
    }

    function __construct($nome) {
        $this->nome = $nome;
    }

    public function update(SplSubject $object){
        $object->SET_log("Comes from ".$this->nome.": I teach in ".$object->GET_materia());
    }
}