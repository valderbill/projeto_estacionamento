<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Definir localização inicial
$localizacao = "GARAGEM ANEXO";

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Adicionar novo acesso liberado ou motorista oficial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nome = strtoupper(trim($_POST['nome']));
    $matricula = strtoupper(trim($_POST['matricula']));
    $tipo = $_POST['tipo'];
    $usuario_cadastro = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido';

    try {
        if ($tipo === 'Oficial') {
            // Inserir na tabela motoristas_oficiais
            $stmt = $pdo->prepare("INSERT INTO motoristas_oficiais (nome, matricula, foto, usuario_cadastro) VALUES (:nome, :matricula, NULL, :usuario_cadastro)");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':usuario_cadastro', $usuario_cadastro);
        } else {
            // Inserir na tabela acessos_liberados
            $stmt = $pdo->prepare("INSERT INTO acessos_liberados (nome, matricula, tipo, localizacao, usuario_cadastro) VALUES (:nome, :matricula, :tipo, :localizacao, :usuario_cadastro)");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':localizacao', $localizacao);
            $stmt->bindParam(':usuario_cadastro', $usuario_cadastro);
        }
        $stmt->execute();
        $msg = "Cadastro realizado com sucesso!";
    } catch (PDOException $e) {
        $msg = "Erro ao cadastrar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GARAGEM ANEXO</title>
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
            <img src="../uploads/placa.png" alt="Logo" width="40"> 
            <h4 class="m-0">Controle de Estacionamento</h4>
        </div>

        <!-- Login e Logout -->
        <div class="d-flex align-items-center gap-3">
            <div class="user-info d-flex align-items-center gap-2">
                <i class="bi bi-file-person fs-5"></i>
                <?= isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : 'Usuário' ?>
            </div>
            <a href="?logout=true" class="btn btn-danger d-flex align-items-center p-1">
                <i class="bi bi-power fs-5"></i>
            </a>
        </div>
    </div>

    
  
     <!-- Barra de navegação -->
     <ul class="nav nav-tabs justify-content-center mt-3">
    <li class="nav-item">
            <a class="nav-link active" href="entradaSaidaVeiculoAnexo.php">Registro de Entrada/Saída</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_motoristaAnexo.php">Motoristas Oficiais</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_acessos_garagemAnexo.php">Acessos Liberados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="listarAnexo.php">Veicúlos Liberados</a>
        </li>
       
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarVeiculoGaragemAnexo.php">Cadastrar Veículos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarNovoMotoristaAnexo.php">Novo Cadastro</a>
        </li>
    </ul>
    <br><br>


    <div class="container">
        <h4 class="mt-5 text-center">Cadastrar Novo Motorista - Garagem Anexo</h4>

        <!-- Exibir mensagem de sucesso ou erro -->
        <?php if (isset($msg)) { ?>
            <div class="alert alert-info"><?= $msg ?></div>
        <?php } ?>

        <!-- Formulário de Cadastro -->
        <form action="cadastrarNovoMotoristaAnexo.php" method="POST" class="mt-4">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula ou OAB</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-control" id="tipo" name="tipo" required>
                    <option value="Particular">Particular</option>
                    <option value="Moto">Moto</option>
                    <option value="Oficial">Oficial</option>
                </select>
            </div>
            <div class="mb-3" id="localizacao-group" style="display: none;">
                <label for="localizacao" class="form-label">Localização</label>
                <select class="form-control" id="localizacao" name="localizacao">
                    <option value="Garagem Anexo">Garagem Anexo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>

    <script>
        // Mostrar/ocultar campos com base no tipo selecionado
        document.getElementById('tipo').addEventListener('change', function () {
            var tipo = this.value;
            var localizacaoGroup = document.getElementById('localizacao-group');

            if (tipo === 'Oficial') {
                localizacaoGroup.style.display = 'block';
            } else {
                localizacaoGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>