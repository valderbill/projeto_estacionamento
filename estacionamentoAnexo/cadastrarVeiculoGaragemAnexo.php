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
    $tipo = strtoupper(trim($_POST['tipo']));
    $nome = null; // Inicializa o nome do motorista 
    $localizacao = 'GARAGEM ANEXO'; // Definir a localização diretamente
    $acesso_id = null;  // Inicializa a variável de acesso

    // Se o tipo for PARTICULAR ou MOTO, capturar o nome do motorista selecionado
    if ($tipo == 'PARTICULAR' || $tipo == 'MOTO') {
        if (!empty($_POST['acesso_id'])) {
            $acesso_id = $_POST['acesso_id'];  // Recebe o ID do acesso selecionado
            $stmt = $pdo->prepare("SELECT nome FROM acessos_liberados WHERE id = :id");
            $stmt->bindParam(':id', $acesso_id, PDO::PARAM_INT);
            $stmt->execute();
            $nome = $stmt->fetchColumn(); // Buscar o nome do motorista pelo ID
        } else {
            $_SESSION['erro'] = "Por favor, selecione um NOME.";
            header("Location: cadastrarVeiculoGaragemAnexo.php");
            exit();
        }
    }

    // Validação dos dados
    if (empty($placa) || empty($modelo) || empty($cor) || empty($tipo) || empty($marca) || ($tipo != 'OFICIAL' && empty($nome))) {
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
                $stmt = $pdo->prepare("INSERT INTO veiculos (placa, modelo, cor, tipo, marca, localizacao, nome, acesso_id) 
                                        VALUES (:placa, :modelo, :cor, :tipo, :marca, :localizacao, :nome, :acesso_id)");
                $stmt->bindParam(':placa', $placa);
                $stmt->bindParam(':modelo', $modelo);
                $stmt->bindParam(':cor', $cor);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':marca', $marca);
                $stmt->bindParam(':localizacao', $localizacao);
                $stmt->bindParam(':nome', $nome); // Salvar o nome do motorista
                $stmt->bindParam(':acesso_id', $acesso_id);  // Inserir o acesso_id caso seja PARTICULAR ou MOTO

                $stmt->execute();
                $_SESSION['sucesso'] = "Veículo cadastrado com sucesso!";
            }
        } catch (PDOException $e) {
            $_SESSION['erro'] = "Erro ao cadastrar veículo: " . $e->getMessage();
        }
    }
    header("Location: cadastrarVeiculoGaragemAnexo.php"); 
    exit();
}

// Exibir veículos cadastrados
$veiculos = $pdo->query("SELECT * FROM veiculos WHERE localizacao = 'GARAGEM ANEXO'")->fetchAll();

// Buscar os acessos liberados para exibir no formulário
$acessos_query = $pdo->query("SELECT id, nome FROM acessos_liberados WHERE localizacao = 'GARAGEM ANEXO'");
$acessos = $acessos_query->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GARAGEM ANEXO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estacionamento.css"> 
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
            <a class="nav-link active" href="entradaSaidaVeiculoAnexo.php">Registro de Entrada/Saída</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_motoristaAnexo.php">Motoristas Oficiais</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_acessos_garagemAnexo.php">Acessos Liberados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="listarAnexo.php">Veicúlos Liberados</a>
        </li>
       
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarVeiculoGaragemAnexo.php">Cadastrar Veículos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarNovoMotoristaAnexo.php">Novo Cadastro</a>
        </li>
    </ul>
    <br><br>



<!-- Formulário de cadastro -->
<div class="container">
    <h4 class="text-center mt-5">Cadastrar Veículo - GARAGEM ANEXO</h4>

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
    <form action="cadastrarVeiculoGaragemAnexo.php" method="POST">
        <div class="row">
            <div class="col-md-3">
                <input type="text" class="form-control mb-2" name="placa" id="placa" placeholder="PLACA" maxlength="8" required pattern="[A-Z]{3}-[A-Z0-9]{4}" title="Formato esperado: AAA-AAAA">
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
    document.addEventListener('DOMContentLoaded', function () {
        // Preencher os campos do formulário com os valores salvos no localStorage
        const formFields = ['placa', 'modelo', 'cor', 'marca', 'tipo', 'acesso_id'];
        formFields.forEach(field => {
            const savedValue = localStorage.getItem(field);
            if (savedValue) {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.value = savedValue;
                    if (field === 'tipo') {
                        input.dispatchEvent(new Event('change')); 
                    }
                }
            }
        });

        // Adicionar evento para salvar os valores no localStorage
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function () {
                localStorage.setItem(this.name, this.value);
            });
        });

        // Formatar automaticamente a placa no formato AAA-AAAA
        const placaInput = document.getElementById('placa');
        placaInput.addEventListener('input', function () {
            let value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 3) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
            if (value.length > 8) {
                value = value.slice(0, 8);
            }
            this.value = value;
        });
    });

    function clearSaidaFields() {
        const fieldsToClear = ['placa', 'modelo', 'cor', 'marca', 'acesso_id'];
        fieldsToClear.forEach(field => {
            localStorage.removeItem(field);
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.value = '';
            }
        });
    }

    // Mostrar ou esconder o campo de acesso com base no tipo selecionado
    document.querySelector('select[name="tipo"]').addEventListener('change', function () {
        var acessoContainer = document.getElementById('acesso_id_container');
        if (this.value === 'PARTICULAR' || this.value === 'MOTO') {
            acessoContainer.style.display = 'block';
        } else {
            acessoContainer.style.display = 'none';
        }
    });

    document.querySelector('form').addEventListener('submit', function (event) {
        const placaInput = document.getElementById('placa');
        const placaPattern = /^[A-Z]{3}-[A-Z0-9]{4}$/;

        if (!placaPattern.test(placaInput.value)) {
            alert('A placa deve estar no formato AAA-AAAA.');
            event.preventDefault();
        }
    });
</script>
</body>
</html>