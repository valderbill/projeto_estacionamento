<?php
// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=localhost;dbname=projeto_estacionamento", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    exit;
}

// Variável de mensagem de erro/sucesso
$message = "";

// ID do veículo a ser editado (exemplo de como buscar o ID na URL)
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "ID do veículo não fornecido!";
    exit;
}

// Consultar o veículo específico
try {
    $sqlVeiculo = "SELECT * FROM veiculos WHERE id = :id";
    $stmt = $pdo->prepare($sqlVeiculo);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$veiculo) {
        echo "Veículo não encontrado!";
        exit;
    }
} catch (PDOException $e) {
    echo "Erro ao consultar o veículo: " . $e->getMessage();
    exit;
}

// Consultar as localizações distintas
try {
    $sqlLocalizacao = "SELECT DISTINCT localizacao FROM veiculos";
    $stmt = $pdo->prepare($sqlLocalizacao);
    $stmt->execute();
    $localizacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar as localizações: " . $e->getMessage();
    exit;
}

// Processar o formulário de edição
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $placa = strtoupper($_POST['placa']); // Garantir que os dados estejam em maiúsculas
    $marca = strtoupper($_POST['marca']);
    $modelo = strtoupper($_POST['modelo']);
    $cor = strtoupper($_POST['cor']);
    $tipo = strtoupper($_POST['tipo']);
    $localizacao = strtoupper($_POST['localizacao']);

    // Atualizar no banco de dados
    try {
        $sql = "UPDATE veiculos SET placa = :placa, marca = :marca, modelo = :modelo, cor = :cor, tipo = :tipo, localizacao = :localizacao WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':cor', $cor);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redireciona de volta para a página de listagem com mensagem de sucesso
        header("Location: ../paginaAdm/listaTodosVeiculos.php?message=Veículo atualizado com sucesso!");
        exit;
    } catch (PDOException $e) {
        echo "Erro ao atualizar o veículo: " . $e->getMessage();
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-1">Editar Veículo</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-lg">
        <!-- Formulário para editar os dados do veículo -->
        <form method="POST">
            <div class="mb-3">
                <label for="placa" class="form-label">Placa</label>
                <input type="text" class="form-control" id="placa" name="placa" value="<?= htmlspecialchars($veiculo['placa']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="marca" class="form-label">Marca</label>
                <input type="text" class="form-control" id="marca" name="marca" value="<?= htmlspecialchars($veiculo['marca']) ?>">
            </div>
            <div class="mb-3">
                <label for="modelo" class="form-label">Modelo</label>
                <input type="text" class="form-control" id="modelo" name="modelo" value="<?= htmlspecialchars($veiculo['modelo']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="cor" class="form-label">Cor</label>
                <input type="text" class="form-control" id="cor" name="cor" value="<?= htmlspecialchars($veiculo['cor']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-control" id="tipo" name="tipo" required>
                    <option value="oficial" <?= ($veiculo['tipo'] == 'oficial') ? 'selected' : '' ?>>Oficial</option>
                    <option value="particular" <?= ($veiculo['tipo'] == 'particular') ? 'selected' : '' ?>>Particular</option>
                    <option value="moto" <?= ($veiculo['tipo'] == 'moto') ? 'selected' : '' ?>>Moto</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="localizacao" class="form-label">Localização</label>
                <select class="form-control" id="localizacao" name="localizacao" required>
                    <option value="">Selecione a Localização</option>
                    <?php foreach ($localizacoes as $localizacao): ?>
                        <option value="<?= htmlspecialchars($localizacao['localizacao']) ?>" <?= ($localizacao['localizacao'] == $veiculo['localizacao']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($localizacao['localizacao']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
