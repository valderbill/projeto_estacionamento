<?php
session_start(); // Iniciar a sessão
include('../Conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placa = strtoupper(trim($_POST['placa']));
    $motoristaSaida = trim($_POST['motorista_saida']);
    $qtdPassageirosSaida = isset($_POST['qtd_passageiros_saida']) ? intval($_POST['qtd_passageiros_saida']) : 0; // Verificar se a quantidade de passageiros foi enviada
    $horarioSaida = !empty($_POST['horario_saida']) ? $_POST['horario_saida'] : date('Y-m-d H:i:s'); // Se o horário de saída não for informado, utiliza a data e hora atuais
    $usuarioSaida = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido'; // Verificar se o nome do usuário logado está na sessão

    try {
       
        if (empty($placa)) {
            echo json_encode(["error" => "A placa é obrigatória."]);
            exit;
        }
        if (empty($motoristaSaida)) {
            echo json_encode(["error" => "Erro: Motorista de saída não pode ser vazio."]);
            exit;
        }

     
        $query = "SELECT COUNT(*) FROM registro_veiculos WHERE placa = :placa AND horario_saida IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(["error" => "Erro: Este veículo não está registrado como dentro do estacionamento."]);
            exit;
        }

        // Atualizar os dados na tabela registro_veiculos
        $query = "
            UPDATE registro_veiculos
            SET motorista_saida = :motorista_saida, 
                horario_saida = :horario_saida, 
                usuario_saida = :usuario_saida, 
                qtd_passageiros_saida = :qtd_passageiros_saida
            WHERE placa = :placa AND horario_saida IS NULL
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':motorista_saida', $motoristaSaida);
        $stmt->bindParam(':horario_saida', $horarioSaida);
        $stmt->bindParam(':usuario_saida', $usuarioSaida);
        $stmt->bindParam(':qtd_passageiros_saida', $qtdPassageirosSaida); 
        $stmt->execute();

        // Verificar se a atualização foi bem-sucedida
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => "Saída registrada com sucesso!",
                "horario_saida" => $horarioSaida,
                "usuario_saida" => $usuarioSaida
            ]);
        } else {
            echo json_encode(["error" => "Erro: Nenhuma linha foi atualizada. Verifique os dados enviados."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro ao registrar saída: " . $e->getMessage()]);
    }
}
?>