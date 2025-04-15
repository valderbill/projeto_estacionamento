<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Buscar acessos liberados para Garagem Sede
$stmt = $pdo->prepare("SELECT id, nome, matricula FROM acessos_liberados WHERE localizacao = 'Garagem Sede' ORDER BY nome ASC");
$stmt->execute();
$acessos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garagem Sede</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estacionamento.css"> <!-- Referência ao arquivo CSS externo -->
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
            <a class="nav-link active" href="entradaSaidaVeiculoSede.php">Registro de Entrada/Saída</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_motoristaSede.php">Motoristas Oficiais</a>
        </li>
    
        <li class="nav-item">
            <a class="nav-link active" href="listar_acessos_garagemSede.php">Acessos Liberados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="listarSede.php">Veicúlos Liberados</a>
        </li>
       
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarVeiculoGaragemSede.php">Cadastrar Veículos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarNovoMotoristaSede.php">Novo Cadastro</a>
        </li>
    </ul>
    <br><br>

    <div class="container">
        <h5 class="mt-5 text-center">Acessos Liberados - Garagem Sede</h5>

        <!-- Lista de Acessos Liberados -->
        <table class="table table-striped mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Matrícula ou OAB</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($acessos as $acesso) { ?>
                    <tr>
                        <td><?= strtoupper($acesso['nome']) ?></td>
                        <td><?= strtoupper($acesso['matricula']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>