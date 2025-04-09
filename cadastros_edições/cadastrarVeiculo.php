<?php
// Incluir a conexão com o banco de dados
include '../Conexao.php';

session_start();

// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Inicializar a variável tipo, caso ela não tenha sido definida
$tipo = isset($_POST['tipo']) ? strtoupper(trim($_POST['tipo'])) : '';

// Verificar se o formulário foi submetido para cadastro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capturar os dados do formulário
    $placa = strtoupper(trim($_POST['placa']));
    $modelo = strtoupper(trim($_POST['modelo']));
    $cor = strtoupper(trim($_POST['cor']));
    $marca = strtoupper(trim($_POST['marca']));
    $localizacao = strtoupper(trim($_POST['localizacao']));
    $acesso_id = null;  // Inicializa a variável de acesso

    // Se o tipo for PARTICULAR, definir o acesso liberado
    if ($tipo == 'PARTICULAR' || $tipo == 'MOTO') {
        $acesso_id = $_POST['acesso_id'];  // Recebe o ID do acesso selecionado
    }

    // Validação dos dados
    if (empty($placa) || empty($modelo) || empty($cor) || empty($tipo) || empty($marca) || empty($localizacao)) {
        $_SESSION['erro'] = "Todos os campos são obrigatórios!";
    } else {
        try {
            // Verificar se a placa já está cadastrada
            $verifica = $pdo->prepare("SELECT COUNT(*) FROM veiculos WHERE placa = :placa");
            $verifica->bindParam(':placa', $placa);
            $verifica->execute();

            if ($verifica->fetchColumn() > 0) {
                $_SESSION['erro'] = "Já existe um veículo cadastrado com esta placa!";
            } else {
                // Inserir os dados na tabela veiculos
                $stmt = $pdo->prepare("INSERT INTO veiculos (placa, modelo, cor, tipo, marca, localizacao, acesso_id) 
                                        VALUES (:placa, :modelo, :cor, :tipo, :marca, :localizacao, :acesso_id)");
                $stmt->bindParam(':placa', $placa);
                $stmt->bindParam(':modelo', $modelo);
                $stmt->bindParam(':cor', $cor);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':marca', $marca);
                $stmt->bindParam(':localizacao', $localizacao);
                $stmt->bindParam(':acesso_id', $acesso_id);  // Inserir o acesso_id caso seja PARTICULAR

                $stmt->execute();
                $_SESSION['sucesso'] = "Veículo cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar veículo: " . $e->getMessage();
        }
    }
    header("Location: cadastrarVeiculo.php");
    exit();
}

// Exibir veículos cadastrados
$veiculos = $pdo->query("SELECT * FROM veiculos")->fetchAll();

// Buscar os acessos liberados para exibir no formulário
$acessos_query = $pdo->query("SELECT id, nome FROM acessos_liberados");
$acessos = $acessos_query->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estacionamento A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
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
<li class="nav-item">
            <a class="nav-link active" href="../estacionamentoA/entradaSaidaVeiculo.php">Registro de Entrada/Saída</a>
        </li>
    <li class="nav-item">
        <a class="nav-link active" href="../listar_motoristas_oficiais.php">Motoristas Oficiais</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="../listar_acessos_liberados.php">Acessos Liberados</a>
    </li>
    <li class="nav-item">
            <a class="nav-link active" href="../lista_estacinamento/listarEstacionamentoA.php">Veicúlos Liberados</a>
        </li>
    <li class="nav-item">
        <a class="nav-link active" href="../cadastros_edições/cadastrarVeiculo.php">Cadastrar Veículos</a>
    </li>
</ul>

<!-- Formulário de cadastro -->
<div class="container">
    <h1 class="text-center mt-5">Cadastrar Veículo</h1>

    <!-- Exibir mensagens de erro ou sucesso -->
    <?php if (isset($_SESSION['erro'])) { ?>
        <div class="alert alert-danger" role="alert"><?= $_SESSION['erro'] ?></div>
        <?php unset($_SESSION['erro']); ?>
    <?php } ?>
    <?php if (isset($_SESSION['sucesso'])) { ?>
        <div class="alert alert-success" role="alert"><?= $_SESSION['sucesso'] ?></div>
        <?php unset($_SESSION['sucesso']); ?>
    <?php } ?>

    <!-- Formulário de cadastro -->
    <form action="cadastrarVeiculo.php" method="POST">
        <div class="row">
            <div class="col-md-3">
                <input type="text" class="form-control mb-2" name="placa" id="placa" placeholder="PLACA" maxlength="8" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control mb-2" name="modelo" placeholder="MODELO" required>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control mb-2" name="cor" placeholder="COR" required>
            </div>
            <div class="col-md-3">
                <select class="form-select mb-2" name="tipo" required>
                    <option value="OFICIAL" selected>Oficial</option>
                    <option value="PARTICULAR">Particular</option>
                    <option value="MOTO">Moto</option> <!-- Novo tipo adicionado -->
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <input type="text" class="form-control mb-2" name="marca" placeholder="MARCA" required>
            </div>
            <div class="col-md-3">
                <select class="form-select mb-2" name="localizacao" required>
                    <option value="" disabled selected>Selecione o Local</option>
                    <option value="ESTACIONAMENTO A">Estacionamento A</option>
                    <option value="ESTACIONAMENTO B">Estacionamento B</option>
                    <option value="ESTACIONAMENTO C">Estacionamento C</option>
                    <option value="GARAGEM SEDE I">Garagem Sede I</option>
                    <option value="GARAGEM SEDE III">Garagem Sede III</option>
                    <option value="GARAGEM ANEXO">Garagem Anexo</option>
                </select>
            </div>

            <!-- Se o tipo for PARTICULAR, mostrar o campo de acesso -->
            <div class="col-md-3" id="acesso_id_container" style="display: none;">
                <select class="form-select mb-2" name="acesso_id">
                    <option value="" disabled selected>Selecione o Acesso</option>
                    <?php foreach ($acessos as $acesso) { ?>
                        <option value="<?= $acesso['id'] ?>"><?= $acesso['nome'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
            </div>
        </div>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('select[name="tipo"]').change(function () {
            if ($(this).val() === "PARTICULAR" || $(this).val() === "MOTO") {
                $('#acesso_id_container').show();
            } else {
                $('#acesso_id_container').hide();
            }
        });

        // Formatar a placa no padrão AAA-AAAA e converter para maiúsculas
        $('#placa').on('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').replace(/(.{3})(.{4})/, '$1-$2');
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>