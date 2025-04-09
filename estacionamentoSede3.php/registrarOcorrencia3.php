<?php
session_start(); // Iniciar a sessão
include('../Conexao.php'); // Conexão com o banco de dados
date_default_timezone_set('America/Sao_Paulo'); // Definir o fuso horário para São Paulo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placaMotorista = trim($_POST['placa_motorista']);
    $ocorrencia = trim($_POST['ocorrencia']);
    $usuario = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido'; 
    $horario = date('Y-m-d H:i:s'); 
    $localizacao = 'GARAGEM SEDE III'; 

    try {
       
        if (empty($placaMotorista) || empty($ocorrencia)) {
            echo "Erro: Placa ou ocorrência não podem estar vazios.";
            exit;
        }

        // Verificar se o veículo já está registrado como "dentro" do estacionamento
        $query = "INSERT INTO ocorrencias (placa, ocorrencia, horario, usuario, localizacao) VALUES (:placa, :ocorrencia, :horario, :usuario, :localizacao)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placaMotorista);
        $stmt->bindParam(':ocorrencia', $ocorrencia);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':localizacao', $localizacao); 
        $stmt->execute();

        echo "Ocorrência registrada com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao registrar ocorrência: " . $e->getMessage();
    }
}
?>
