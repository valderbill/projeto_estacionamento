<?php
session_start();
require_once '../Conexao.php'; // Conexão com o banco de dados

// Definir localização inicial
$localizacao = "Estacionamento A";

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Adicionar novo acesso liberado ou motorista oficial
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nome = strtoupper(trim($_POST['nome']));
    $matricula = strtoupper(trim($_POST['matricula']));
    $tipo = $_POST['tipo'];
    $usuario_cadastro = isset($_SESSION['nome']) ? $_SESSION['nome'] : 'Desconhecido';

    // Definir localização automaticamente para "Particular" ou "Moto"
    $localizacao = ($tipo === 'Oficial') ? $_POST['localizacao'] : 'Estacionamento A';

    // Inserir no banco de dados
    try {
        $stmt = $pdo->prepare("INSERT INTO acessos_liberados (nome, matricula, tipo, localizacao, usuario_cadastro) VALUES (:nome, :matricula, :tipo, :localizacao, :usuario_cadastro)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':usuario_cadastro', $usuario_cadastro);
        $stmt->execute();
        $msg = "Cadastro realizado com sucesso!";
    } catch (PDOException $e) {
        $msg = "Erro ao cadastrar: " . $e->getMessage();
    }
}

// Editar acesso liberado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    // Coletar dados do formulário de edição
    $id = $_POST['id'];
    $nome = strtoupper(trim($_POST['nome']));
    $matricula = strtoupper(trim($_POST['matricula']));
    $localizacao = $_POST['localizacao'];

    // Atualizar no banco de dados
    try {
        $stmt = $pdo->prepare("UPDATE acessos_liberados SET nome = :nome, matricula = :matricula, localizacao = :localizacao WHERE id = :id");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':matricula', $matricula);
        $stmt->bindParam(':localizacao', $localizacao);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $msg = "Acesso liberado atualizado com sucesso!";
    } catch (PDOException $e) {
        $msg = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Excluir acesso liberado
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // Sanitizar o ID recebido
    try {
        $query = "DELETE FROM acessos_liberados WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirecionar após exclusão
        header("Location: gerenciar_acessos_motoristas.php?success=deleted");
        exit;
    } catch (PDOException $e) {
        echo "Erro ao excluir o acesso: " . $e->getMessage();
    }
}

// Buscar acessos liberados com filtro
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterLocalizacao = isset($_GET['localizacao']) ? $_GET['localizacao'] : '';

$query = "SELECT id, nome, matricula, tipo, localizacao, data_cadastro, usuario_cadastro FROM acessos_liberados";
$conditions = [];
if ($search) {
    $conditions[] = "(nome LIKE :search OR matricula LIKE :search)";
}
if ($filterLocalizacao) {
    $conditions[] = "localizacao = :localizacao";
}
if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY nome ASC";

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
if ($filterLocalizacao) {
    $stmt->bindValue(':localizacao', $filterLocalizacao);
}
$stmt->execute();
$acessos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Veículos</title>
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
            <a class="nav-link active" href="gerenciar_acessos_motoristas.php">Gerênciar Acessos e Motoristas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="gerarRelatorioOcorrencias.php">Relatório de Ocorrência</a>
        </li>
    </ul>
    <br>
    <br>




    <div class="container">
        <h1 class="mt-5 text-center">Gerenciar Acessos e Motoristas</h1>

        <!-- Exibir mensagem de sucesso ou erro -->
        <?php if (isset($msg)) { ?>
            <div class="alert alert-info"><?= $msg ?></div>
        <?php } ?>

        <!-- Campo de busca -->
        <form action="gerenciar_acessos_motoristas.php" method="GET" class="d-flex mb-4">
            <input type="text" name="search" class="form-control me-2" placeholder="Buscar por nome ou matrícula" value="<?= htmlspecialchars($search) ?>">
            <select name="localizacao" class="form-control me-2">
                <option value="">Todas as Localizações</option>
                <option value="Estacionamento A" <?= $filterLocalizacao === 'Estacionamento A' ? 'selected' : '' ?>>Estacionamento A</option>
                <option value="Estacionamento B" <?= $filterLocalizacao === 'Estacionamento B' ? 'selected' : '' ?>>Estacionamento B</option>
                <option value="Estacionamento C" <?= $filterLocalizacao === 'Estacionamento C' ? 'selected' : '' ?>>Estacionamento C</option>
                <option value="Garagem Sede I" <?= $filterLocalizacao === 'Garagem Sede I' ? 'selected' : '' ?>>Garagem Sede I</option>
                <option value="Garagem Sede III" <?= $filterLocalizacao === 'Garagem Sede III' ? 'selected' : '' ?>>Garagem Sede III</option>
                <option value="Garagem Anexo" <?= $filterLocalizacao === 'Garagem Anexo' ? 'selected' : '' ?>>Garagem Anexo</option>
            </select>
            <button type="submit" class="btn btn-primary me-2">Buscar</button>
            <a href="gerenciar_acessos_motoristas.php" class="btn btn-secondary me-2">Limpar</a>
            <a href="imprimirRelatorioAcessos.php?search=<?= urlencode($search) ?>&localizacao=<?= urlencode($filterLocalizacao) ?>" class="btn btn-success">Imprimir</a>
        </form>

        <!-- Formulário de Cadastro -->
        <form action="gerenciar_acessos_motoristas.php" method="POST" class="mt-4">
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
            <div class="mb-3" id="localizacao-group">
                <label for="localizacao" class="form-label">Localização</label>
                <select class="form-control" id="localizacao" name="localizacao">
                    <option value="Estacionamento A">Estacionamento A</option>
                    <option value="Estacionamento B">Estacionamento B</option>
                    <option value="Estacionamento C">Estacionamento C</option>
                    <option value="Garagem Sede I">Garagem Sede I</option>
                    <option value="Garagem Sede III">Garagem Sede III</option>
                    <option value="Garagem Anexo">Garagem Anexo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>

        <!-- Lista de Acessos Liberados -->
        <table class="table table-striped mt-4">
            <thead class="table-dark">
                <tr>
                    <th>Nome</th>
                    <th>Matrícula ou OAB</th>
                    <th>Tipo</th>
                    <th>Localização</th>
                    <th>Data de Cadastro</th>
                    <th>Usuário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($acessos as $acesso) { ?>
                    <tr>
                        <td><?= strtoupper($acesso['nome']) ?></td>
                        <td><?= strtoupper($acesso['matricula']) ?></td>
                        <td><?= $acesso['tipo'] ?></td>
                        <td><?= $acesso['localizacao'] ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($acesso['data_cadastro'])) ?></td>
                        <td><?= $acesso['usuario_cadastro'] ?></td>
                        <td>
                            <!-- Botões de Editar e Excluir -->
                            <a href="#" data-bs-toggle="modal" data-bs-target="#editModal" class="btn btn-warning btn-sm" 
                               data-id="<?= $acesso['id'] ?>" 
                               data-nome="<?= $acesso['nome'] ?>" 
                               data-matricula="<?= $acesso['matricula'] ?>"
                               data-tipo="<?= $acesso['tipo'] ?>"
                               data-localizacao="<?= $acesso['localizacao'] ?>">Editar</a>
                            <a href="gerenciar_acessos_motoristas.php?delete=<?= $acesso['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
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
                <form action="gerenciar_acessos_motoristas.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Acesso ou Motorista</h5>
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
                            <label for="editLocalizacao" class="form-label">Localização</label>
                            <select class="form-control" id="editLocalizacao" name="localizacao" required>
                                <option value="Estacionamento A">Estacionamento A</option>
                                <option value="Estacionamento B">Estacionamento B</option>
                                <option value="Estacionamento C">Estacionamento C</option>
                                <option value="Garagem Sede I">Garagem Sede I</option>
                                <option value="Garagem Sede III">Garagem Sede III</option>
                                <option value="Garagem Anexo">Garagem Anexo</option>
                            </select>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
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

        // Preencher os campos do modal com os dados para edição
        var editModal = document.getElementById('editModal')
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var id = button.getAttribute('data-id')
            var nome = button.getAttribute('data-nome')
            var matricula = button.getAttribute('data-matricula')
            var localizacao = button.getAttribute('data-localizacao')

            var modalId = editModal.querySelector('#editId')
            var modalNome = editModal.querySelector('#editNome')
            var modalMatricula = editModal.querySelector('#editMatricula')
            var modalLocalizacao = editModal.querySelector('#editLocalizacao')

            modalId.value = id
            modalNome.value = nome
            modalMatricula.value = matricula
            modalLocalizacao.value = localizacao
        })
    </script>
</body>
</html>