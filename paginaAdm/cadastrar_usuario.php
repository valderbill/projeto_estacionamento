<?php
session_start();

// Configurar o fuso horário para garantir o horário correto do sistema
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
include('../Conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografando a senha
    $perfil = $_POST['perfil'];
    $data_cadastro = date('Y-m-d H:i:s'); // Obtém a data e hora atual
    $cadastrado_por = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido'; // Usuário que cadastrou

    // Inserir no banco de dados
    $query = "INSERT INTO usuarios (nome, matricula, senha, perfil, data_cadastro, cadastrado_por) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$nome, $matricula, $senha, $perfil, $data_cadastro, $cadastrado_por]);

    // Redirecionar para a mesma página para atualizar a lista
    header('Location: cadastrar_usuario.php');
    exit;
}

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Processar exclusão
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Sanitizar o ID recebido
    try {
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirecionar após exclusão
        header("Location: cadastrar_usuario.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        echo "Erro ao excluir o usuário: " . $e->getMessage();
    }
}

// Recuperar usuários cadastrados com busca
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT id, nome, matricula, perfil, DATE_FORMAT(data_cadastro, '%d/%m/%Y %H:%i:%s') as data_cadastro, cadastrado_por 
          FROM usuarios 
          WHERE nome LIKE :search OR matricula LIKE :search";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina Adm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos/paginaAdm.css"> <!-- Referência ao arquivo CSS externo -->
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
    <!--
        <li class="nav-item">
            <a class="nav-link active" href="painel_admin.php">Estacionamentos</a>
        </li>
    -->
        <li class="nav-item">
            <a class="nav-link active" href="cadastrar_usuario.php">Cadastrar Usúario</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrar_motorista_oficial.php">Cadastrar Motoristas Oficiais</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="acessos_liberados.php">Cadastrar Acessos Liberados</a>
        </li> 
        <li class="nav-item">
            <a class="nav-link active" href="listaTodosVeiculos.php">Lista de Veículos</a>
        </li>   
        <li class="nav-item">
            <a class="nav-link active" href="gerarRelatorio.php">Relatório Geral</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="relatorioVeiculo.php">Relatório por Busca</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="gerarRelatorioOcorrencias.php">Relatório de Ocorrência</a>
        </li>
    </ul>
    <br>
    <br>

    <div class="container mt-5">
        <h1>Cadastrar Usuário</h1>

        <form method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>

            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required>
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>

            <div class="mb-3">
                <label for="perfil" class="form-label">Perfil</label>
                <select class="form-control" id="perfil" name="perfil" required>
                    <option value="administrador">Administrador</option>
                    <option value="vigilante">Vigilante</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>

        <h2 class="mt-5">Usuários Cadastrados</h2>

        <!-- Campo de busca -->
        <form action="cadastrar_usuario.php" method="GET" class="d-flex mb-4">
            <input type="text" name="search" class="form-control me-2" placeholder="Buscar por nome ou matrícula" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary me-2">Buscar</button>
            <a href="cadastrar_usuario.php" class="btn btn-secondary me-2">Limpar</a>
            <a href="imprimirRelatorioUsuarios.php?search=<?= urlencode($search) ?>" class="btn btn-success">Imprimir</a>
        </form>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Matrícula</th>
                    <th>Perfil</th>
                    <th>Data de Cadastro</th>
                    <th>Cadastrado Por</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr class="page-break">
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo $usuario['nome']; ?></td>
                        <td><?php echo $usuario['matricula']; ?></td>
                        <td><?php echo $usuario['perfil']; ?></td>
                        <td><?php echo $usuario['data_cadastro']; ?></td>
                        <td><?php echo $usuario['cadastrado_por']; ?></td>
                        <td>
                            <a href="../cadastros_edições/editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="cadastrar_usuario.php?delete=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
