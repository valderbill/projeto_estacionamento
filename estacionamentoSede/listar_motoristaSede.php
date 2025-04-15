<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Buscar motoristas cadastrados no banco de dados
try {
    $stmt = $pdo->prepare("SELECT * FROM motoristas_oficiais");
    $stmt->execute();
    $motoristasOficiais = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Erro ao buscar motoristas: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Motoristas Oficiais - Página A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estacionamento.css"> 
    
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
        <h4 class="mt-5">Lista de Motoristas Oficiais</h4>  

        <div class="list-group">
            <?php if ($motoristasOficiais) { // Verifica se existem motoristas cadastrados ?>
                <?php foreach ($motoristasOficiais as $motorista) { ?>
                    <div class="motorista-card mb-3">
                        <?php 
                        $fotoPath = "../uploads/motoristas/" . $motorista['foto'];
                        if (!empty($motorista['foto']) && $motorista['foto'] !== 'sem_foto.png' && file_exists($fotoPath)) { ?>
                            <img src="<?= $fotoPath ?>" alt="<?= strtoupper($motorista['nome']) ?>" class="motorista-img">
                        <?php } else { ?>
                            <img src="../uploads/motoristas/sem_foto.png" alt="Foto padrão" class="motorista-img">
                        <?php } ?>
                        <div class="info">
                            <h5 class="card-title"><?= strtoupper($motorista['nome']) ?></h5>                            
                            <p class="card-text">Matrícula: <?= $motorista['matricula'] ?></p>
                            <button class="btn btn-primary btn-sm ver-foto" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="<?= $fotoPath ?>">Ver Foto</button>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>Nenhum motorista cadastrado.</p>
            <?php } ?>
        </div>

        <!-- Modal para exibir a foto ampliada -->
        <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fotoModalLabel">Foto do Motorista</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="fotoAmpliada" src="" alt="Foto Ampliada" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fotoModal = document.getElementById('fotoModal');
            const fotoAmpliada = document.getElementById('fotoAmpliada');

            fotoModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const fotoSrc = button.getAttribute('data-foto');
                fotoAmpliada.src = fotoSrc;
            });
        });
    </script>
</body>
</html>
