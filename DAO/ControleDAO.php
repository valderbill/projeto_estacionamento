
<?php

// ControleDAO.php
require_once '../Conexao.php';
require_once 'ControleDTO.php';

class ControleDAO {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexao::getInstance();
    }

    public function registrarEntrada(ControleDTO $controle) {
        $sql = "INSERT INTO controle (placa, tipo, entrada, motorista_entrada, marca, modelo, cor) VALUES (:placa, :tipo, :entrada, :motorista_entrada, :marca, :modelo, :cor)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':placa', $controle->getPlaca());
        $stmt->bindValue(':tipo', $controle->getTipo());
        $stmt->bindValue(':entrada', $controle->getEntrada());
        $stmt->bindValue(':motorista_entrada', $controle->getMotoristaEntrada());
        $stmt->bindValue(':marca', $controle->getMarca());
        $stmt->bindValue(':modelo', $controle->getModelo());
        $stmt->bindValue(':cor', $controle->getCor());
        return $stmt->execute();
    }

    public function listarEntradas() {
        $sql = "SELECT * FROM controle WHERE saida IS NULL";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// registrarEntrada.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controleDTO = new ControleDTO($_POST['placa'], $_POST['tipo'], $_POST['motorista'], $_POST['marca'], $_POST['modelo'], $_POST['cor']);
    $controleDAO = new ControleDAO();
    if ($controleDAO->registrarEntrada($controleDTO)) {
        echo json_encode(["success" => "Entrada registrada com sucesso!"]);
    } else {
        echo json_encode(["error" => "Erro ao registrar entrada!"]);
    }
}

?>