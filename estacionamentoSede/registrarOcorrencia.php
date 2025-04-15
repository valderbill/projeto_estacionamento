<?php
session_start(); // Iniciar a sessão
include('../Conexao.php'); // Conexão com o banco de dados
date_default_timezone_set('America/Sao_Paulo'); // Definir o fuso horário para São Paulo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placa_motorista = trim($_POST['placa_motorista']);
    $ocorrencia = trim($_POST['ocorrencia']);
    $usuario = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido';
    $horario = date('Y-m-d H:i:s');
    $localizacao = isset($_POST['localizacao']) ? trim($_POST['localizacao']) : 'Desconhecida'; // Valor padrão para localização

    if (empty($placa_motorista) || empty($ocorrencia) || empty($localizacao)) {
        echo "Erro: Todos os campos devem ser preenchidos.";
        exit;
    }

    try {
        $query = "
            INSERT INTO ocorrencias (placa, ocorrencia, horario, usuario, localizacao)
            VALUES (:placa_motorista, :ocorrencia, :horario, :usuario, :localizacao)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa_motorista', $placa_motorista);
        $stmt->bindParam(':ocorrencia', $ocorrencia);
        $stmt->bindParam(':horario', $horario);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':localizacao', $localizacao); // Adicionar a localização
        $stmt->execute();

        echo "Ocorrência registrada com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao registrar ocorrência: " . $e->getMessage();
    }
}
?>
