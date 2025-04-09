<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Relatório de Veículos</title>
    <style>
        /* Assegura que todos os cards tenham a mesma altura */
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Estilo para o ícone e nome ficarem próximos */
        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info i {
            font-size: 1.5rem;
            margin-right: 10px;

        }
                
        .motorista-card {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .motorista-card img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 15px;
        }
        .motorista-card .info {
            display: flex;
            flex-direction: column;
        }
        .motorista-card .info h5 {
            margin-bottom: 5px;
        }
        .motorista-card .info p {
            margin-bottom: 0;
            }
    </style>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery UI para Autocomplete -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
    <!-- jQuery e jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <!-- Máscara para Placa -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#placa').mask('AAA-0000', {
                translation: {
                    'A': { pattern: /[A-Za-z]/ },
                    '0': { pattern: /[0-9]/ }
                }
            });

            $("#motorista").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "buscarMotoristas.php",
                        dataType: "json",
                        data: { term: request.term.toUpperCase() },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 1
            });

            $("#placa").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "buscarPlacas.php",
                        dataType: "json",
                        data: { term: request.term },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 1
            });
        });
    </script>
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
    <div class="container mt-4">

    <h2 class="mb-4 text-center">Relatório de Veículos</h2>

    <form method="GET" action="relatorioVeiculo.php">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="dataInicio" class="form-label">Data Início:</label>
                <input type="date" id="dataInicio" name="dataInicio" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="dataFim" class="form-label">Data Final:</label>
                <input type="date" id="dataFim" name="dataFim" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="motorista" class="form-label">Motorista:</label>
                <input type="text" id="motorista" name="motorista" class="form-control" placeholder="Digite o nome">
            </div>
            <div class="col-md-3">
                <label for="placa" class="form-label">Placa do Veículo:</label>
                <input type="text" id="placa" name="placa" class="form-control" placeholder="AAA-0000">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <hr>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $dataInicio = $_GET['dataInicio'] ?? '';
        $dataFim = $_GET['dataFim'] ?? '';
        $motorista = $_GET['motorista'] ?? '';
        $placa = $_GET['placa'] ?? '';

        $query = "SELECT * FROM registro_veiculos WHERE 1=1";
        $params = [];

        if (!empty($dataInicio)) {
            $query .= " AND DATE(horario_entrada) >= :dataInicio";
            $params[':dataInicio'] = $dataInicio;
        }

        if (!empty($dataFim)) {
            $query .= " AND DATE(horario_entrada) <= :dataFim";
            $params[':dataFim'] = $dataFim;
        }

        if (!empty($motorista)) {
            $query .= " AND motorista_entrada LIKE :motorista";
            $params[':motorista'] = "%$motorista%";
        }

        if (!empty($placa)) {
            $query .= " AND placa LIKE :placa";
            $params[':placa'] = "%$placa%";
        }

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($resultados) > 0) {
                echo "<table class='table table-striped mt-4'>";
                echo "<thead><tr>
                        <th>ID</th><th>Placa</th><th>Marca</th><th>Modelo</th>
                        <th>Cor</th><th>Tipo</th><th>Motorista Entrada</th>
                        <th>Motorista Saída</th><th>Horário Entrada</th><th>Horário Saída</th>
                        <th>Localização</th><th>Usuário Logado</th><th>Usuário Saída</th>
                    </tr></thead>";
                echo "<tbody>";
                $idsFiltrados = [];
                foreach ($resultados as $row) {
                    $idsFiltrados[] = $row['id'];
                    // Formatar as datas
                    $horarioEntrada = date('d/m/Y H:i', strtotime($row['horario_entrada']));
                    $horarioSaida = date('d/m/Y H:i', strtotime($row['horario_saida']));

                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['placa']}</td>
                            <td>{$row['marca']}</td>
                            <td>{$row['modelo']}</td>
                            <td>{$row['cor']}</td>
                            <td>{$row['tipo']}</td>
                            <td>{$row['motorista_entrada']}</td>
                            <td>{$row['motorista_saida']}</td>
                            <td>$horarioEntrada</td>
                            <td>$horarioSaida</td>
                            <td>{$row['localizacao']}</td>
                            <td>{$row['usuario_logado']}</td>
                            <td>{$row['usuario_saida']}</td>
                        </tr>";
                }
                echo "</tbody></table>";

                // Formulário para gerar o relatório
                echo "<form action='imprimirRelatorio.php' method='POST'>
                        <input type='hidden' name='idsFiltrados' value='" . implode(',', $idsFiltrados) . "'>
                        <button type='submit' class='btn btn-success mt-3'>Gerar Relatório</button>
                    </form>";
            } else {
                echo "<p class='mt-3 text-danger'>Nenhum resultado encontrado.</p>";
            }
        } catch (Exception $e) {
            echo "<p class='mt-3 text-danger'>Erro na consulta: " . $e->getMessage() . "</p>";
        }
    }
    ?>

    </div> <!-- Fechando a classe container -->
</body>
</html>
