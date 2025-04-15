<?php
session_start();

// Verificar se o usuário está logado e se é um Vigilante
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 'Vigilante') {
    header('Location: login.php'); // Redirecionar se não for Vigilante
    exit();
}

// Verificar se o Vigilante já escolheu um local
if (isset($_SESSION['local_acesso']) && $_SESSION['local_acesso'] != '') {
    header('Location: ' . $_SESSION['local_acesso']); // Se já escolheu, redireciona
    exit();
}

// Processar a escolha do local
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['local'])) {
    $_SESSION['local_acesso'] = $_POST['local']; // Salva o local escolhido
    header('Location: ' . $_SESSION['local_acesso']); // Redireciona para o local
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolher Local</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Escolha o Local de Acesso</h2>
        <form method="POST" action="">
            <select class="form-select mb-3" name="local" required>
                <option value="./estacionamentoA/entradaSaidaVeiculo.php">Estacionamento A</option>
                <option value="./estacionamentoB/entradaSaidaVeiculoB.php">Estacionamento B</option>
                <option value="./estacionamentoC/entradaSaidaVeiculoC.php">Estacionamento C</option>
                <option value="./estacionamentoSede/entradaSaidaVeiculoSede.php">Garagem Sede I</option>
                <option value="./estacionamentoSede3/entradaSaidaVeiculoSede3.PHP">Garagem Sede III</option>
                <option value="./estacionamentoAnexo/entradaSaidaVeiculoAnexo.php">Garagem Anexo</option>
            </select>
            <button type="submit" class="btn btn-success w-100">Acessar Local</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
