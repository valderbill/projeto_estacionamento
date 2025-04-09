<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Obter os veículos que estão no estacionamento C
$veiculos = $pdo->prepare("SELECT * FROM veiculos WHERE localizacao = :localizacao");
$veiculos->bindParam(':localizacao', $localizacao);
$localizacao = 'ESTACIONAMENTO C';
$veiculos->execute();
$veiculos = $veiculos->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTACIONAMENTO C</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estacionamento.css"> 
</head>

<body>
    <!-- Barra superior com logo e login/logout -->
    <div class="d-flex justify-content-between align-items-center px-4 py-2 bg-white">
        <!-- Logo e título -->
        <div class="d-flex align-items-center gap-2">
            <img src="../uploads/placa.png" alt="Logo" width="40"> <!-- Substitua pelo caminho correto da sua logo -->
            <h4 class="m-0">Controle de Estacionamento</h4>
        </div>

        <!-- Login e Logout -->
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
    <li class="nav-item">
            <a class="nav-link active" href="entradaSaidaVeiculoC.php">Registro de Entrada/Saída</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_motoristaC.php">Motoristas Oficiais</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_acessos_estacionamentoC.php">Acessos Liberados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="listarEstacionamentoC.php">Veicúlos Liberados</a>
        </li>
       
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarVeiculoEstacionamentoC.php">Cadastrar Veículos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarNovoMotoristaC.php">Novo Cadastro</a>
        </li>
    </ul>
    <br><br>
    <h4 class="text-center mt-5">Veículos ESTACIONAMENTO C</h4>

    <table class="table table-bordered mt-3" style="width: 90%; margin: 0 auto; font-size: 1rem;">
        <thead>
            <tr>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Cor</th>
                <th>Tipo</th>
                <th>Motorista</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($veiculos as $veiculo) { ?>
                <tr>
                    <td><?= strtoupper($veiculo['placa']) ?></td>
                    <td><?= strtoupper($veiculo['marca']) ?></td>
                    <td><?= strtoupper($veiculo['modelo']) ?></td>
                    <td><?= strtoupper($veiculo['cor']) ?></td>
                    <td><?= strtoupper($veiculo['tipo']) ?></td>
                    <td>
                        <?php 
                        if ($veiculo['acesso_id']) {
                            $acesso = $pdo->prepare("SELECT nome FROM acessos_liberados WHERE id = :id");
                            $acesso->bindParam(':id', $veiculo['acesso_id']);
                            $acesso->execute();
                            $acesso_data = $acesso->fetch();
                            echo $acesso_data ? strtoupper($acesso_data['nome']) : 'N/A';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>