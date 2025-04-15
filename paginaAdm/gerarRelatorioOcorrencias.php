<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Processar o formulário de filtro
$filters = [];
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['localizacao'])) {
        $filters[] = "localizacao = :localizacao";
        $params[':localizacao'] = $_POST['localizacao'];
    }
    if (!empty($_POST['data_inicio']) && !empty($_POST['hora_inicio'])) {
        $filters[] = "horario >= :data_hora_inicio";
        $params[':data_hora_inicio'] = $_POST['data_inicio'] . ' ' . $_POST['hora_inicio'];
    }
    if (!empty($_POST['data_fim']) && !empty($_POST['hora_fim'])) {
        $filters[] = "horario <= :data_hora_fim";
        $params[':data_hora_fim'] = $_POST['data_fim'] . ' ' . $_POST['hora_fim'];
    }
}

// Consultar os dados da tabela ocorrencias com filtros
try {
    $sqlOcorrencias = "
        SELECT 
            id,
            placa,
            ocorrencia,
            horario,
            usuario,
            localizacao
        FROM ocorrencias
    ";
    if (!empty($filters)) {
        $sqlOcorrencias .= " WHERE " . implode(' AND ', $filters);
    }
    $sqlOcorrencias .= " ORDER BY horario ASC"; // Changed to ascending order

    $stmtOcorrencias = $pdo->prepare($sqlOcorrencias);
    $stmtOcorrencias->execute($params);
    $ocorrencias = $stmtOcorrencias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar as ocorrências: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Ocorrências</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos/paginaAdm.css">
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
        <h5 class="my-4">Relatório de Ocorrências</h5>

        <!-- Formulário para buscar ocorrências -->
        <form method="POST" action="gerarRelatorioOcorrencias.php">
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
                    <select class="form-select" id="hora_inicio" name="hora_inicio">
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
                    <select class="form-select" id="hora_fim" name="hora_fim">
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

            <!-- Hidden inputs to pass filtered data -->
            <input type="hidden" name="filtered_localizacao" value="<?= htmlspecialchars($_POST['localizacao'] ?? '') ?>">
            <input type="hidden" name="filtered_data_inicio" value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>">
            <input type="hidden" name="filtered_hora_inicio" value="<?= htmlspecialchars($_POST['hora_inicio'] ?? '') ?>">
            <input type="hidden" name="filtered_data_fim" value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>">
            <input type="hidden" name="filtered_hora_fim" value="<?= htmlspecialchars($_POST['hora_fim'] ?? '') ?>">

            <button type="submit" class="btn btn-primary">Filtrar</button>
            <button type="submit" formaction="imprimirRelatorioOcorrencias.php" class="btn btn-secondary">Imprimir</button>
        </form>

        <!-- Tabela de ocorrências -->
        <h3>Ocorrências</h3>
        <?php if (count($ocorrencias) > 0) { ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Ocorrência</th>
                        <th>Horário</th>
                        <th>Usuário</th>
                        <th>Localização</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ocorrencias as $ocorrencia) { ?>
                        <tr>
                            <td><?= htmlspecialchars($ocorrencia['placa']) ?></td>
                            <td><?= htmlspecialchars($ocorrencia['ocorrencia']) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($ocorrencia['horario']))) ?></td>
                            <td><?= htmlspecialchars($ocorrencia['usuario']) ?></td>
                            <td><?= htmlspecialchars($ocorrencia['localizacao']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="text-center">Nenhuma ocorrência encontrada.</p>
        <?php } ?>
    </div>
</body>
</html>
