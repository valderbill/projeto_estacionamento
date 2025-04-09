<?php
// Conexão com o banco de dados
include('../Conexao.php');
$pdo = new PDO("mysql:host=localhost;dbname=projeto_estacionamento", 'root', '');

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar dados do formulário
    $nomeEstacionamento = $_POST['nome'];
    $vagas_particulares = $_POST['vagas_particulares'];
    $vagas_oficiais = $_POST['vagas_oficiais'];
    $vagas_motos = isset($_POST['vagas_motos']) ? $_POST['vagas_motos'] : 0;

    // Atualizar os dados no banco de dados
    $updateQuery = "UPDATE vagas SET vagas_particulares = :vagas_particulares, vagas_oficiais = :vagas_oficiais, vagas_motos = :vagas_motos WHERE nome = :nome";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':vagas_particulares', $vagas_particulares);
    $updateStmt->bindParam(':vagas_oficiais', $vagas_oficiais);
    $updateStmt->bindParam(':vagas_motos', $vagas_motos);
    $updateStmt->bindParam(':nome', $nomeEstacionamento);

    if ($updateStmt->execute()) {
        echo "<div class='alert alert-success'>Estacionamento atualizado com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao atualizar estacionamento.</div>";
    }
}

// Obter o nome do estacionamento passado pela URL
$nomeEstacionamento = isset($_GET['nome']) ? $_GET['nome'] : '';

// Verificar se o nome do estacionamento foi passado
if ($nomeEstacionamento == '') {
    die('Estacionamento não especificado.');
}

// Consultar o estacionamento no banco de dados
$query = "SELECT * FROM vagas WHERE LOWER(nome) = LOWER(:nome)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':nome', $nomeEstacionamento);
$stmt->execute();
$estacionamento = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se o estacionamento existe
if (!$estacionamento) {
    die('Estacionamento não encontrado.');
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estacionamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Editar Estacionamento: <?= strtoupper($estacionamento['nome']) ?></h1>
        <form method="POST">
            <input type="hidden" name="nome" value="<?= $estacionamento['nome'] ?>">
            <div class="mb-3">
                <label for="vagas_particulares" class="form-label">Vagas Particulares</label>
                <input type="number" class="form-control" id="vagas_particulares" name="vagas_particulares" value="<?= $estacionamento['vagas_particulares'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="vagas_oficiais" class="form-label">Vagas Oficiais</label>
                <input type="number" class="form-control" id="vagas_oficiais" name="vagas_oficiais" value="<?= $estacionamento['vagas_oficiais'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="vagas_motos" class="form-label">Vagas para Motos</label>
                <input type="number" class="form-control" id="vagas_motos" name="vagas_motos" value="<?= $estacionamento['vagas_motos'] ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar Estacionamento</button>
            <a href="../paginaAdm/painel_admin.php" class="btn btn-secondary">Cancelar</a>
            <a href="../paginaAdm/painel_admin.php" class="btn btn-secondary">Voltar</a> <!-- Botão Voltar -->
        </form>

        <!-- Exibir informações do estacionamento no painel -->
        <div class="mt-5">
            <h2>Vagas Cadastradas</h2>
            <div class="row">
                <!-- Card para Vagas Particulares -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Vagas Particulares
                        </div>
                        <div class="card-body">
                            <p>Vagas Disponíveis: <?= $estacionamento['vagas_particulares'] ?></p>
                        </div>
                    </div>
                </div>

                <!-- Card para Vagas Oficiais -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Vagas Oficiais
                        </div>
                        <div class="card-body">
                            <p>Vagas Disponíveis: <?= $estacionamento['vagas_oficiais'] ?></p>
                        </div>
                    </div>
                </div>

                <!-- Card para Vagas de Motos -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Vagas para Motos
                        </div>
                        <div class="card-body">
                            <p>Vagas Disponíveis: <?= $estacionamento['vagas_motos'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
