<?php
include('../Conexao.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Hash the password
        $perfil = $_POST['perfil'];

        $query = "UPDATE usuarios SET nome = ?, senha = ?, perfil = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$nome, $senha, $perfil, $id]);

        header('Location: ../paginaAdm/cadastrar_usuario.php');
        exit;
    }
} else {
    header('Location: ../paginaAdm/cadastrar_usuario.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Usuário</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo $usuario['nome']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula</label>
                <input type="text" class="form-control" id="matricula" name="matricula" value="<?php echo $usuario['matricula']; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="mb-3">
                <label for="perfil" class="form-label">Perfil</label>
                <select class="form-control" id="perfil" name="perfil" required>
                    <option value="administrador" <?php echo ($usuario['perfil'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="vigilante" <?php echo ($usuario['perfil'] == 'vigilante') ? 'selected' : ''; ?>>Vigilante</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>
