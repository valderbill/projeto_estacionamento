<?php
require_once '../Conexao.php';  // Conexão com o banco de dados

// Verificar se o ID do motorista foi passado pela URL
if (!isset($_GET['id'])) {
    die("ID do motorista não fornecido.");
}

$idMotorista = $_GET['id'];

// Buscar dados do motorista no banco de dados
try {
    $stmt = $pdo->prepare("SELECT * FROM motoristas_oficiais WHERE id = :id");
    $stmt->bindParam(':id', $idMotorista);
    $stmt->execute();
    $motorista = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar se o motorista existe
    if (!$motorista) {
        die("Motorista não encontrado.");
    }
} catch (PDOException $e) {
    echo 'Erro ao buscar motorista: ' . $e->getMessage();
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];

    // Verificar se um novo arquivo de foto foi enviado
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto_nome = $_FILES['foto']['name'];
        $foto_temp = $_FILES['foto']['tmp_name'];
        $foto_destino = "uploads/" . uniqid() . "_" . $foto_nome;

        // Mover a foto para a pasta de uploads
        if (move_uploaded_file($foto_temp, $foto_destino)) {
            // Atualizar no banco de dados com nova foto
            $sql = "UPDATE motoristas_oficiais SET nome = :nome, matricula = :matricula, foto = :foto WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':foto', $foto_destino);
            $stmt->bindParam(':id', $idMotorista);

            if ($stmt->execute()) {
                echo "<script>alert('Motorista atualizado com sucesso!'); window.location='../paginaAdm/cadastrar_motorista_oficial.php';</script>";
            } else {
                echo "<script>alert('Erro ao atualizar motorista!');</script>";
            }
        } else {
            echo "<script>alert('Erro ao fazer upload da foto!');</script>";
        }
    } else {
        // Atualizar os dados sem mudar a foto
        $sql = "UPDATE motoristas_oficiais SET nome = :nome, matricula = :matricula WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->bindParam(':id', $idMotorista);

        if ($stmt->execute()) {
            echo "<script>alert('Motorista atualizado com sucesso!'); window.location='../paginaAdm/cadastrar_motorista_oficial.php';</script>";
        } else {
            echo "<script>alert('Erro ao atualizar motorista!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Motorista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Editar Motorista</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Nome:</label>
                <input type="text" class="form-control" name="nome" value="<?= $motorista['nome'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Matrícula:</label>
                <input type="text" class="form-control" name="matricula" value="<?= $motorista['matricula'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Foto do Motorista:</label>
                <input type="file" class="form-control" name="foto" accept="image/*">
                <br>
                <img src="<?= $motorista['foto'] ?>" alt="Foto Atual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="../paginaAdm/cadastrar_motorista_oficial.php" class="btn btn-secondary">Voltar</a>
        </form>
    </div>
</body>
</html>
