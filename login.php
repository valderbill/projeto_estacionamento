<?php
session_start();

// Verificar se já está logado
if (isset($_SESSION['usuario_id']) && isset($_SESSION['perfil'])) {
    if ($_SESSION['perfil'] == 'Administrador') {
        header('Location: ./paginaAdm/gerarRelatorio.php');
        exit();
    } else if ($_SESSION['perfil'] == 'Vigilante' && isset($_SESSION['local_acesso'])) {
        header('Location: ' . $_SESSION['local_acesso']);
        exit();
    }
}

// Processamento do login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('Conexao.php');
    $pdo = new PDO("mysql:host=localhost;dbname=projeto_estacionamento", 'root', '');

    $matricula = $_POST['matricula'];
    $senha = $_POST['senha'];
    $perfil = $_POST['perfil']; // Define qual login está sendo usado

    // Consultar a tabela de usuários com base na matrícula e perfil
    $query = "SELECT * FROM usuarios WHERE matricula = :matricula AND perfil = :perfil";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':matricula', $matricula);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['perfil'] = ucfirst(strtolower($usuario['perfil']));

        if ($_SESSION['perfil'] == 'Administrador') {
            header('Location: ./paginaAdm/gerarRelatorio.php');
            exit();
        } else if ($_SESSION['perfil'] == 'Vigilante') {
            // Se o usuário for Vigilante, ele será redirecionado para escolher um local
            $_SESSION['local_acesso'] = ''; // Garantir que a escolha do local não esteja setada
            header('Location: escolher_local.php'); // Redireciona para a escolha de local
            exit();
        }
    } else {
        $erro = "Matrícula ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilo para centralizar os cards */
        .login-card {
            max-width: 400px; /* Largura máxima do card */
            margin: 20px auto;   /* Centraliza o card */
        }
        .centralizado {
            text-align: center;
            margin-top: 50px;
        }
        .imagem {
            width: 50px;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container">
        <div class="centralizado">
            <h1>Controle de Estacionamento</h1>
            <img src="./uploads/placa.png" alt="Estacionamento" class="imagem">
        </div>
    </div>


    <div class="container mt-5">
        <div class="row">
            <!-- Card Administrador -->
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-header text-center">
                        <h5>Login Administrador</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="perfil" value="Administrador">
                            <div class="mb-3">
                                <label for="matriculaAdmin" class="form-label">Matrícula</label>
                                <input type="text" class="form-control" id="matriculaAdmin" name="matricula" required>
                            </div>
                            <div class="mb-3">
                                <label for="senhaAdmin" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senhaAdmin" name="senha" required>
                            </div>
                            <?php if (isset($erro) && $_POST['perfil'] == 'Administrador'): ?>
                                <div class="alert alert-danger"><?= $erro ?></div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Card Vigilante -->
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-header text-center">
                        <h5>Login Vigilante</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="perfil" value="Vigilante">
                            <div class="mb-3">
                                <label for="matriculaVigilante" class="form-label">Matrícula</label>
                                <input type="text" class="form-control" id="matriculaVigilante" name="matricula" required>
                            </div>
                            <div class="mb-3">
                                <label for="senhaVigilante" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senhaVigilante" name="senha" required>
                            </div>
                            <?php if (isset($erro) && $_POST['perfil'] == 'Vigilante'): ?>
                                <div class="alert alert-danger"><?= $erro ?></div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
