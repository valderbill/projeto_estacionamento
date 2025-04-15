<?php
session_start(); // Iniciar a sessão
include('../Conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placa = strtoupper(trim($_POST['placa']));
    $motoristaSaida = trim($_POST['motorista_saida']);
    $qtdPassageirosSaida = intval($_POST['qtd_passageiros_saida']); // Capturar a quantidade de passageiros na saída
    $horarioSaida = !empty($_POST['horario_saida']) ? $_POST['horario_saida'] : date('Y-m-d H:i:s'); // Usa o horário enviado ou o atual
    $usuarioSaida = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido'; // Verificar se o nome do usuário logado está na sessão

    try {
        // Validação dos dados
        if (empty($placa)) {
            echo json_encode(["error" => "A placa é obrigatória."]);
            exit;
        }
        if (empty($motoristaSaida)) {
            echo json_encode(["error" => "O motorista de saída é obrigatório."]);
            exit;
        }

        // Verificar se o veículo está registrado como "dentro" do estacionamento
        $query = "SELECT * FROM registro_veiculos WHERE placa = :placa AND horario_saida IS NULL LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa, PDO::PARAM_STR);
        $stmt->execute();
        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$veiculo) {
            echo json_encode(["error" => "Veículo não está registrado como dentro do estacionamento."]);
            exit;
        }

        // Atualizar o registro do veículo com os dados de saída
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
        $stmt->bindParam(':qtd_passageiros_saida', $qtdPassageirosSaida); // Atualizar a quantidade de passageiros na saída
        $stmt->execute();

        // Retornar o horário de saída para o frontend
        echo json_encode([
            "success" => "Saída registrada com sucesso!",
            "horario_saida" => $horarioSaida,
            "usuario_saida" => $usuarioSaida
        ]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro ao registrar saída: " . $e->getMessage()]);
    }
}
?>