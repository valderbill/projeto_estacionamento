<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Processar o formulário de filtro
$filters = [];
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['localizacao'])) {
        if ($_POST['localizacao'] === 'Garagem Sede') {
            $filters[] = "(localizacao LIKE 'Garagem Sede%')";
        } else {
            $filters[] = "localizacao = :localizacao";
            $params[':localizacao'] = $_POST['localizacao'];
        }
    }
    if (!empty($_POST['data_inicio']) && !empty($_POST['hora_inicio'])) {
        $filters[] = "horario_entrada >= :data_hora_inicio"; // Certifique-se de que o nome da coluna está correto
        $params[':data_hora_inicio'] = $_POST['data_inicio'] . ' ' . $_POST['hora_inicio'];
    }
    if (!empty($_POST['data_fim']) && !empty($_POST['hora_fim'])) {
        $filters[] = "horario_saida <= :data_hora_fim"; // Certifique-se de que o nome da coluna está correto
        $params[':data_hora_fim'] = $_POST['data_fim'] . ' ' . $_POST['hora_fim'];
    }
}

// Handle alert for registered occurrences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'alert_ocorrencia') {
    $placaMotorista = $_POST['placa_motorista'] ?? '';
    $ocorrencia = $_POST['ocorrencia'] ?? '';
    $localizacao = $_POST['localizacao'] ?? '';

    // Validate the required fields
    if (empty($placaMotorista) || empty($ocorrencia) || empty($localizacao)) {
        echo json_encode(['error' => 'Dados incompletos para o alerta.']);
        exit;
    }

    // Log or process the alert (e.g., save to database, send notification, etc.)
    // Example: Log the alert to a file
    $logMessage = "Alerta de Ocorrência:\nPlaca/Motorista: $placaMotorista\nOcorrência: $ocorrencia\nLocalização: $localizacao\n";
    file_put_contents('alerta_ocorrencias.log', $logMessage, FILE_APPEND);

    // Respond with success
    echo json_encode(['success' => 'Alerta processado com sucesso.']);
    exit;
}

// Consultar os dados da tabela veiculos
try {
    $sql = "SELECT * FROM veiculos";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $veiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar os veículos: " . $e->getMessage();
    exit;
}

// Consultar os dados da tabela registro_veiculos com filtros
try {
    $sqlRegistroVeiculos = "
        SELECT 
            placa,
            marca,
            modelo,
            cor,
            tipo,
            motorista_entrada,
            motorista_saida,
            horario_entrada, -- Certifique-se de que o nome da coluna está correto
            horario_saida,   -- Certifique-se de que o nome da coluna está correto
            usuario_logado,
            usuario_saida,
            localizacao,
            qtd_passageiros,
            qtd_passageiros_saida
        FROM registro_veiculos
    ";
    if (!empty($filters)) {
        $sqlRegistroVeiculos .= " WHERE " . implode(' AND ', $filters);
    }
    $sqlRegistroVeiculos .= " ORDER BY horario_entrada DESC"; // Certifique-se de que o nome da coluna está correto

    $stmtRegistroVeiculos = $pdo->prepare($sqlRegistroVeiculos);
    $stmtRegistroVeiculos->execute($params);
    $registroVeiculos = $stmtRegistroVeiculos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar os registros de veículos: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Veículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos/paginaAdm.css"> <!-- Referência ao arquivo CSS externo -->
</head>
<body>
    <!-- Barra superior com logo e login/logout -->
    <div class="d-flex justify-content-between align-items-center px-4 py-2 bg-white">
        <div class="d-flex align-items-center gap-2">
            <img src="../uploads/placa.png" alt="Logo" width="40">
            <h4 class="m-0">Controle de Estacionamento</h4>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="user-info d-flex align-items-center gap-2">
                <i class="bi bi-file-person fs-5"></i>
                <?= isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Usuário' ?>
            </div>
            <a href="?logout=true" class="btn btn-danger d-flex align-items-center p-1">
                <i class="bi bi-power fs-5"></i>
            </a>
        </div>
    </div>

    
     <!-- Barra de navegação -->
     <ul class="nav nav-tabs justify-content-center mt-3">
    <!--
        <li class="nav-item">
            <a class="nav-link active" href="painel_admin.php">Estacionamentos</a>
        </li>
    -->
        <li class="nav-item">
            <a class="nav-link active" href="cadastrar_usuario.php">Cadastrar Usúario</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrar_motorista_oficial.php">Cadastrar Motoristas Oficiais</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="acessos_liberados.php">Cadastrar Acessos Liberados</a>
        </li> 
        <li class="nav-item">
            <a class="nav-link active" href="listaTodosVeiculos.php">Lista de Veículos</a>
        </li>   
        <li class="nav-item">
            <a class="nav-link active" href="gerarRelatorio.php">Relatório Geral</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="relatorioVeiculo.php">Relatório por Busca</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="gerarRelatorioOcorrencias.php">Relatório de Ocorrência</a>
        </li>
    </ul>
    <br>
    <br>

    <div class="container">
       <h5 class="my-4">Gerar Relatório de Entrada e Saída de Veículos</h5>
       <br>
       <br>

        <?php if (isset($_SESSION['mensagem'])): ?>
            <script>
                alert("<?= htmlspecialchars($_SESSION['mensagem']) ?>");
            </script>
            <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <!-- Formulário para buscar relatórios -->
        <form method="POST" action="gerarRelatorio.php"> <!-- Corrigir o action para o arquivo correto -->
            <div class="mb-3 row">
                <!-- Localização -->
                <div class="col-md-3">
                    <label for="localizacao" class="form-label">Localização:</label>
                    <select class="form-select" id="localizacao" name="localizacao">
                        <option value="">Selecione</option>
                        <option value="Estacionamento A">Estacionamento A</option>
                        <option value="Estacionamento B">Estacionamento B</option>
                        <option value="Estacionamento C">Estacionamento C</option>
                        <option value="Garagem Sede I">Garagem Sede I</option>
                        <option value="Garagem Sede III">Garagem Sede III</option>
                        <option value="Garagem Anexo">Garagem Anexo</option>
                    </select>
                </div>

                <!-- Data e Hora de Início -->
                <div class="col-md-2">
                    <label for="data_inicio" class="form-label">Data Início:</label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                </div>

                <div class="col-md-2">
                    <label for="hora_inicio" class="form-label">Hora Início:</label>
                    <select class="form-select select-time" id="hora_inicio" name="hora_inicio">
                        <?php
                            for ($i = 0; $i < 24; $i++) {
                                for ($j = 0; $j < 60; $j += 15) {
                                    $hour = str_pad($i, 2, "0", STR_PAD_LEFT);
                                    $minute = str_pad($j, 2, "0", STR_PAD_LEFT);
                                    echo "<option value='{$hour}:{$minute}'>{$hour}:{$minute}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>

                <!-- Data e Hora de Fim -->
                <div class="col-md-2">
                    <label for="data_fim" class="form-label">Data Fim:</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim">
                </div>

                <div class="col-md-2">
                    <label for="hora_fim" class="form-label">Hora Fim:</label>
                    <select class="form-select select-time" id="hora_fim" name="hora_fim">
                        <?php
                            for ($i = 0; $i < 24; $i++) {
                                for ($j = 0; $j < 60; $j += 15) {
                                    $hour = str_pad($i, 2, "0", STR_PAD_LEFT);
                                    $minute = str_pad($j, 2, "0", STR_PAD_LEFT);
                                    echo "<option value='{$hour}:{$minute}'>{$hour}:{$minute}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Gerar Relatório</button>
        </form>

        <!-- Botão para imprimir o relatório -->
        <form method="POST" action="imprimirRelatorio.php" target="_blank">
            <input type="hidden" name="filters" value="<?= htmlspecialchars(json_encode($filters)) ?>">
            <input type="hidden" name="params" value="<?= htmlspecialchars(json_encode($params)) ?>">
            <button type="submit" class="btn btn-secondary mt-3">Imprimir Relatório</button>
        </form>

        <!-- Tabela de registros de veículos -->
        <div class="container" style="padding: 10 1px;">
            <h3>Registros de Veículos</h3>
            <?php if (count($registroVeiculos) > 0) { ?>
                <div style="padding: 0 1px; overflow-x: auto;">
                    <table class="table table-bordered table-striped" style="font-size: 0.7rem; table-layout: fixed; word-wrap: break-word;">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Placa</th>
                                <th style="width: 10%;">Marca</th>
                                <th style="width: 10%;">Modelo</th>
                                <th style="width: 8%;">Cor</th>
                                <th style="width: 8%;">Tipo</th>
                                <th style="width: 12%;">Motorista Entrada</th>
                                <th style="width: 12%;">Motorista Saída</th>
                                <th style="width: 12%;">Horário Entrada</th>
                                <th style="width: 12%;">Horário Saída</th>
                                <th style="width: 10%;">Usuário Logado</th>
                                <th style="width: 10%;">Usuário Saída</th>
                                <th style="width: 12%;">Qtd. Passageiros Entrada</th>
                                <th style="width: 12%;">Qtd. Passageiros Saída</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registroVeiculos as $registro) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($registro['placa']) ?></td>
                                    <td><?= htmlspecialchars($registro['marca']) ?></td>
                                    <td><?= htmlspecialchars($registro['modelo']) ?></td>
                                    <td><?= htmlspecialchars($registro['cor']) ?></td>
                                    <td><?= htmlspecialchars($registro['tipo']) ?></td>
                                    <td><?= htmlspecialchars($registro['motorista_entrada']) ?></td>
                                    <td><?= htmlspecialchars($registro['motorista_saida']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($registro['horario_entrada']))) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($registro['horario_saida']))) ?></td>
                                    <td><?= htmlspecialchars($registro['usuario_logado']) ?></td>
                                    <td><?= htmlspecialchars($registro['usuario_saida']) ?></td>
                                    <td><?= htmlspecialchars($registro['qtd_passageiros']) ?></td>
                                    <td><?= htmlspecialchars($registro['qtd_passageiros_saida']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <p class="text-center">Nenhum registro de veículo encontrado.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
