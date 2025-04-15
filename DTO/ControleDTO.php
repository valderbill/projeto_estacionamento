<?php

// ControleDTO.php
class ControleDTO {
    private $idControle;
    private $placa;
    private $tipo;
    private $entrada;
    private $saida;
    private $motoristaEntrada;
    private $motoristaSaida;
    private $marca;
    private $modelo;
    private $cor;

    public function __construct($placa, $tipo, $motoristaEntrada, $marca, $modelo, $cor, $entrada = null, $saida = null, $idControle = null) {
        $this->idControle = $idControle;
        $this->placa = $placa;
        $this->tipo = $tipo;
        $this->entrada = $entrada ?: date('Y-m-d H:i:s');
        $this->saida = $saida;
        $this->motoristaEntrada = $motoristaEntrada;
        $this->motoristaSaida = $motoristaSaida;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->cor = $cor;
    }

    public function getIdControle() { return $this->idControle; }
    public function getPlaca() { return $this->placa; }
    public function getTipo() { return $this->tipo; }
    public function getEntrada() { return $this->entrada; }
    public function getSaida() { return $this->saida; }
    public function getMotoristaEntrada() { return $this->motoristaEntrada; }
    public function getMotoristaSaida() { return $this->motoristaSaida; }
    public function getMarca() { return $this->marca; }
    public function getModelo() { return $this->modelo; }
    public function getCor() { return $this->cor; }
}

?>