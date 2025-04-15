<?php
session_start(); // Iniciar a sessão
include('../Conexao.php'); // Conexão com o banco de dados
date_default_timezone_set('America/Sao_Paulo'); // Definir o fuso horário para São Paulo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $placa = strtoupper(trim($_POST['placa_motorista']));
        $ocorrencia = trim($_POST['ocorrencia']);
        $localizacao = 'Estacionamento C'; // Set the location
        $horario = date('Y-m-d H:i:s');
        $usuario = $_SESSION['nome'] ?? 'Usuário';

        // Validate input
        if (empty($placa)) {
            echo json_encode(["error" => "Placa ou motorista não fornecido."]);
            exit;
        }
        if (empty($ocorrencia)) {
            echo json_encode(["error" => "Ocorrência não fornecida."]);
            exit;
        }

        // Insert the occurrence into the database
        $query = "
            INSERT INTO ocorrencias (placa, ocorrencia, horario, usuario, localizacao)
            VALUES (:placa, :ocorrencia, :horario, :usuario, :localizacao)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':ocorrencia', $ocorrencia);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->execute();

        echo "Ocorrência registrada com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao registrar ocorrência: " . $e->getMessage();
    }
    exit;
}
?>
