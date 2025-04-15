<?php
session_start();
require_once '../Conexao.php';  // Conexão com o banco de dados

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Adicionar novo motorista oficial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Coletar dados do formulário
    $nome = strtoupper(trim($_POST['nome']));
    $matricula = strtoupper(trim($_POST['matricula']));
    $usuario_cadastro = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido';

    // Diretório de upload
    $uploadDir = '../uploads/motoristas/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Cria o diretório se não existir
    }

    // Processar upload de foto
    $foto = 'sem_foto.png'; // Valor padrão para a foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nomeArquivo = uniqid() . '.' . $extensao;
        $destino = $uploadDir . $nomeArquivo;

        // Verificar se a extensão é válida
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($extensao, $extensoesPermitidas)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $foto = $nomeArquivo;
            } else {
                $msg = "Erro ao fazer upload da foto.";
            }
        } else {
            $msg = "Formato de arquivo inválido. Use JPG, JPEG, PNG ou GIF.";
        }
    }

    // Inserir no banco de dados
    try {
        $stmt = $pdo->prepare("INSERT INTO motoristas_oficiais (nome, matricula, usuario_cadastro, foto) VALUES (:nome, :matricula, :usuario_cadastro, :foto)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->bindParam(':usuario_cadastro', $usuario_cadastro);
        $stmt->bindParam(':foto', $foto);
        $stmt->execute();
        $msg = "Motorista oficial cadastrado com sucesso!";
    } catch (PDOException $e) {
        $msg = "Erro ao cadastrar: " . $e->getMessage();
    }
}

// Editar motorista oficial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $nome = strtoupper(trim($_POST['nome']));
    $matricula = strtoupper(trim($_POST['matricula']));
    $foto = null;

    // Diretório de upload
    $uploadDir = '../uploads/motoristas/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Processar upload de nova foto, se fornecida
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $nomeArquivo = uniqid() . '.' . $extensao;
        $destino = $uploadDir . $nomeArquivo;

        // Verificar se a extensão é válida
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($extensao, $extensoesPermitidas)) {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                $foto = $nomeArquivo;

                // Remover a foto antiga, se existir
                $stmt = $pdo->prepare("SELECT foto FROM motoristas_oficiais WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $motorista = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($motorista && $motorista['foto'] && $motorista['foto'] !== 'sem_foto.png') {
                    $fotoPath = $uploadDir . $motorista['foto'];
                    if (file_exists($fotoPath)) {
                        unlink($fotoPath); // Excluir a foto antiga
                    } else {
                        // Log ou mensagem para depuração
                        error_log("Arquivo não encontrado para exclusão: $fotoPath");
                    }
                }
            } else {
                $msg = "Erro ao fazer upload da nova foto.";
            }
        } else {
            $msg = "Formato de arquivo inválido. Use JPG, JPEG, PNG ou GIF.";
        }
    }

    // Atualizar no banco de dados
    try {
        $query = "UPDATE motoristas_oficiais SET nome = :nome, matricula = :matricula";
        if ($foto) {
            $query .= ", foto = :foto";
        }
        $query .= " WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':matricula', $matricula);
        if ($foto) {
            $stmt->bindParam(':foto', $foto);
        }
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $msg = "Motorista oficial atualizado com sucesso!";
    } catch (PDOException $e) {
        $msg = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Excluir motorista oficial
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Remover a foto do servidor, se existir
    $stmt = $pdo->prepare("SELECT foto FROM motoristas_oficiais WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $motorista = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($motorista && $motorista['foto'] && $motorista['foto'] !== 'sem_foto.png') {
        $fotoPath = '../uploads/motoristas/' . $motorista['foto'];
        if (file_exists($fotoPath)) {
            unlink($fotoPath); // Excluir a foto antiga
        } else {
            // Log ou mensagem para depuração
            error_log("Arquivo não encontrado para exclusão: $fotoPath");
        }
    }

    // Remover do banco de dados
    try {
        $stmt = $pdo->prepare("DELETE FROM motoristas_oficiais WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $msg = "Motorista oficial excluído com sucesso!";
    } catch (PDOException $e) {
        $msg = "Erro ao excluir: " . $e->getMessage();
    }
}

// Buscar motoristas oficiais com filtro
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT id, nome, matricula, data_cadastro, usuario_cadastro, foto FROM motoristas_oficiais";
if ($search) {
    $query .= " WHERE nome LIKE :search OR matricula LIKE :search";
}
$query .= " ORDER BY nome ASC";
$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$motoristas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina Adm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="container">
        <h1 class="mt-5 text-center">Cadastrar Motoristas Oficiais</h1>

        <!-- Exibir mensagem de sucesso ou erro -->
        <?php if (isset($msg)) { ?>
            <div class="alert alert-info"><?= $msg ?></div>
        <?php } ?>

        <!-- Campo de busca -->
        <form action="cadastrar_motorista_oficial.php" method="GET" class="d-flex mb-4">
            <input type="text" name="search" class="form-control me-2" placeholder="Buscar por nome ou matrícula" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary me-2">Buscar</button>
            <a href="cadastrar_motorista_oficial.php" class="btn btn-secondary me-2">Limpar</a>
            <a href="imprimirRelatorioMotoristas.php?search=<?= urlencode($search) ?>" class="btn btn-success">Imprimir</a>
        </form>

        <!-- Formulário de Cadastro de Motorista Oficial -->
        <form action="cadastrar_motorista_oficial.php" method="POST" enctype="multipart/form-data" class="mt-4">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula</label>
                <input type="text" class="form-control" id="matricula" name="matricula" required>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>

        <!-- Lista de Motoristas Oficiais -->
        <table class="table table-striped mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Foto</th>
                    <th>Nome</th>
                    <th>Matrícula</th>
                    <th>Data de Cadastro</th>
                    <th>Usuário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($motoristas as $motorista) { ?>
                    <tr>
                        <td>
                            <?php if (!empty($motorista['foto']) && $motorista['foto'] !== 'sem_foto.png' && file_exists("../uploads/motoristas/" . $motorista['foto'])) { ?>
                                <img src="../uploads/motoristas/<?= htmlspecialchars($motorista['foto']) ?>" alt="Foto" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                            <?php } else { ?>
                                <img src="../uploads/motoristas/sem_foto.png" alt="Sem Foto" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                            <?php } ?>
                        </td>
                        <td><?= strtoupper($motorista['nome']) ?></td>
                        <td><?= strtoupper($motorista['matricula']) ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($motorista['data_cadastro'])) ?></td>
                        <td><?= $motorista['usuario_cadastro'] ?></td>
                        <td>
                            <!-- Botões de Editar e Excluir -->
                            <a href="#" data-bs-toggle="modal" data-bs-target="#editModal" class="btn btn-warning btn-sm" 
                               data-id="<?= $motorista['id'] ?>" 
                               data-nome="<?= $motorista['nome'] ?>" 
                               data-matricula="<?= $motorista['matricula'] ?>">Editar</a>
                            <a href="cadastrar_motorista_oficial.php?delete=<?= $motorista['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="cadastrar_motorista_oficial.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Motorista Oficial</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="editNome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="editMatricula" class="form-label">Matrícula</label>
                            <input type="text" class="form-control" id="editMatricula" name="matricula" required>
                        </div>
                        <div class="mb-3">
                            <label for="editFoto" class="form-label">Foto</label>
                            <input type="file" class="form-control" id="editFoto" name="foto" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preencher os campos do modal com os dados para edição
        var editModal = document.getElementById('editModal')
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var id = button.getAttribute('data-id')
            var nome = button.getAttribute('data-nome')
            var matricula = button.getAttribute('data-matricula')

            var modalId = editModal.querySelector('#editId')
            var modalNome = editModal.querySelector('#editNome')
            var modalMatricula = editModal.querySelector('#editMatricula')

            modalId.value = id
            modalNome.value = nome
            modalMatricula.value = matricula
        })
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>