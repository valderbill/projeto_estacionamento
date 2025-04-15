<?php
session_start(); // Iniciar a sessão
include('../Conexao.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placa = strtoupper(trim($_POST['placa']));
    $modelo = strtoupper(trim($_POST['modelo']));
    $cor = strtoupper(trim($_POST['cor']));
    $tipo = strtoupper(trim($_POST['tipo']));
    $marca = strtoupper(trim($_POST['marca']));
    $motoristaEntrada = trim($_POST['motorista_entrada']);
    $qtdPassageiros = intval($_POST['qtd_passageiros']);
    $ocorrencia = isset($_POST['ocorrencia']) ? trim($_POST['ocorrencia']) : ''; // Verificar se "ocorrencia" foi enviado
    
    // Se o horário de entrada não for informado, utiliza a data e hora atuais
    $horarioEntrada = !empty($_POST['horario_entrada']) ? $_POST['horario_entrada'] : date('Y-m-d H:i:s'); 
    $usuario_logado = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido'; // Verificar se o nome do usuário logado está na sessão
    $localizacao = 'GARAGEM SEDE III'; // Defina a localização conforme necessário

    // Verificar se o motoristaEntrada está vazio
    if (empty($motoristaEntrada)) {
        echo "Erro: Motorista de entrada não pode ser vazio.";
        exit;
    }

    // Verificar se o veículo já está registrado como "dentro" do estacionamento
    $query = "SELECT COUNT(*) FROM registro_veiculos WHERE placa = :placa AND horario_saida IS NULL";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':placa', $placa, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo "Erro: Este veículo já está registrado como dentro do estacionamento.";
        exit;
    }

    // Buscar o veículo na tabela veiculos
    $query = "SELECT nome, tipo FROM veiculos WHERE placa = :placa LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':placa', $placa);
    $stmt->execute();
    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$veiculo) {
        echo "Erro: Veículo não encontrado.";
        exit;
    }

    // Verificar o tipo do veículo e definir o motorista
    if ($veiculo['tipo'] === 'PARTICULAR' || $veiculo['tipo'] === 'MOTO') {
        $motoristaEntrada = $veiculo['nome']; // Buscar o nome do motorista da tabela veiculos
    }

    if (empty($motoristaEntrada)) {
        echo "Erro: Motorista de entrada inválido.";
        exit;
    }

    // Inserir os dados na tabela registro_veiculos
    try {
        $query = "
            INSERT INTO registro_veiculos (placa, marca, modelo, cor, tipo, motorista_entrada, horario_entrada, usuario_logado, localizacao, qtd_passageiros)
            VALUES (:placa, :marca, :modelo, :cor, :tipo, :motorista_entrada, :horario_entrada, :usuario_logado, :localizacao, :qtd_passageiros)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':cor', $cor);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':motorista_entrada', $motoristaEntrada);
        $stmt->bindParam(':horario_entrada', $horarioEntrada); // Agora usa o horário do POST ou o horário atual
        $stmt->bindParam(':usuario_logado', $usuario_logado);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':qtd_passageiros', $qtdPassageiros);
        $stmt->execute();

        // Registrar ocorrência, se houver
        if (!empty($ocorrencia)) {
            $query = "
                INSERT INTO ocorrencias (placa, ocorrencia, horario, usuario)
                VALUES (:placa, :ocorrencia, :horario, :usuario)
            ";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':placa', $placa);
            $stmt->bindParam(':ocorrencia', $ocorrencia);
            $stmt->bindParam(':horario', $horarioEntrada);
            $stmt->bindParam(':usuario', $usuario_logado);
            $stmt->execute();
        }

        echo "Entrada registrada com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao registrar entrada: " . $e->getMessage();
    }
}
?>
