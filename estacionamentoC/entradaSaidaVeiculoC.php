<?php
session_start(); // Iniciar a sessão
include('../Conexao.php');  // Inclui a conexão com o banco de dados atualizado
date_default_timezone_set('America/Sao_Paulo'); // Definir o fuso horário para São Paulo


// Verificar se o botão de logout foi clicado
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../login.php"); // Redirecionar para a página de login após o logout
    exit();
}

// Verificar se o usuário solicitou logout
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Limpar apenas os veículos que registraram saída
    $veiculos = json_decode($_SESSION['veiculos'], true) ?? [];
    $veiculos = array_filter($veiculos, function($veiculo) {
        return empty($veiculo['horario_saida']);
    });
    $_SESSION['veiculos'] = json_encode($veiculos);

    session_destroy(); // Destruir a sessão
    header("Location: entradaSaidaVeiculoC.php?login=true"); // Redirecionar para a página de login
    exit;
}

if (isset($_GET['term'])) {
    $term = strtoupper(trim($_GET['term'])); // Captura o termo digitado e converte para maiúsculas

    try {
        // Consulta para buscar placas que correspondem ao termo digitado
        $query = "SELECT placa FROM veiculos WHERE placa LIKE :term LIMIT 10";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':term', "%$term%");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retorna as placas no formato esperado pelo jQuery UI Autocomplete
        $placas = array_map(function ($row) {
            return $row['placa'];
        }, $result);

        echo json_encode($placas);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
}
// Consultar placas da Estacionamento C
if (isset($_GET['action']) && $_GET['action'] == 'get_placas_estacionamento_c') {
    try {
        $query = "SELECT placa FROM veiculos WHERE localizacao = 'ESTACIONAMENTO C'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}
// Consultar detalhes do veículo e motorista
if (isset($_GET['action']) && $_GET['action'] == 'get_veiculo_motorista' && isset($_GET['placa'])) {
    try {
        $placa = strtoupper(trim($_GET['placa']));

        // Verificando se a placa foi recebida corretamente
        if (empty($placa)) {
            echo json_encode(["error" => "Placa não fornecida."]);
            exit;
        }

        // Consulta para buscar as informações do veículo
        $query = "SELECT id, modelo, cor, tipo, marca, nome FROM veiculos WHERE placa = :placa LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Retornar o nome do motorista diretamente da tabela veiculos
            $result['motorista'] = $result['nome'] ?? 'Motorista não encontrado';

            // Se o tipo for OFICIAL, buscar motoristas oficiais
            if ($result['tipo'] === 'OFICIAL') {
                $motoristasOficiaisQuery = "SELECT nome FROM motoristas_oficiais";
                $motoristasStmt = $pdo->query($motoristasOficiaisQuery);
                $motoristasOficiais = $motoristasStmt->fetchAll(PDO::FETCH_ASSOC);

                $result['motoristas_oficiais'] = $motoristasOficiais;
            }

            echo json_encode($result);
        } else {
            echo json_encode(["error" => "Veículo não encontrado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}

// Consultar motoristas oficiais
if (isset($_GET['action']) && $_GET['action'] == 'get_motoristas_oficiais') {
    try {
        $query = "SELECT nome FROM motoristas_oficiais";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}

// Consultar total de vagas e vagas disponíveis por tipo no Estacionamento C
if (isset($_GET['action']) && $_GET['action'] == 'get_total_vagas_c') {
    try {
        $query = "
            SELECT tipo, 
                   (SELECT COUNT(*) FROM veiculos WHERE localizacao = 'ESTACIONAMENTO C' AND tipo = v.tipo) as total,
                   (SELECT COUNT(*) FROM veiculos WHERE localizacao = 'ESTACIONAMENTO C' AND tipo = v.tipo AND saida IS NULL) as disponivel
            FROM veiculos v
            WHERE localizacao = 'ESTACIONAMENTO C'
            GROUP BY tipo";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}

// Registrar entrada do veículo no Estacionamento C
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_entrada_c') {
    try {
        $placa = strtoupper(trim($_POST['placa']));
        $motoristaEntrada = trim($_POST['motorista_entrada']);
        $horarioEntrada = date('Y-m-d H:i:s');
        $usuarioLogado = $_SESSION['nome'] ?? 'Usuário';
        $qtdPassageiros = intval($_POST['qtd_passageiros']);

        // Validação dos dados
        if (empty($placa)) {
            echo json_encode(["error" => "Placa não fornecida."]);
            exit;
        }

        // Buscar o veículo na tabela veiculos
        $query = "SELECT nome, tipo FROM veiculos WHERE placa = :placa LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa);
        $stmt->execute();
        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$veiculo) {
            echo json_encode(["error" => "Veículo não encontrado."]);
            exit;
        }

        // Verificar o tipo do veículo e definir o motorista
        if ($veiculo['tipo'] === 'PARTICULAR' || $veiculo['tipo'] === 'MOTO') {
            $motoristaEntrada = $veiculo['nome']; // Buscar o nome do motorista da tabela veiculos
        }

        if (empty($motoristaEntrada)) {
            echo json_encode(["error" => "Motorista de entrada inválido."]);
            exit;
        }

        // Registrar a entrada do veículo na tabela registro_veiculos
        $query = "INSERT INTO registro_veiculos (placa, motorista_entrada, horario_entrada, usuario_logado, localizacao, qtd_passageiros) 
                  VALUES (:placa, :motorista_entrada, :horario_entrada, :usuario_logado, 'ESTACIONAMENTO C', :qtd_passageiros)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':placa', $placa);
        $stmt->bindParam(':motorista_entrada', $motoristaEntrada);
        $stmt->bindParam(':horario_entrada', $horarioEntrada);
        $stmt->bindParam(':usuario_logado', $usuarioLogado);
        $stmt->bindParam(':qtd_passageiros', $qtdPassageiros);
        $stmt->execute();

        echo json_encode(["success" => "Entrada registrada com sucesso!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erro ao registrar entrada: " . $e->getMessage()]);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTACIONAMENTO C</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos/estacionamento.css"> 
</head>


<body>
    <!-- Barra superior com logo e login/logout -->
    <div class="d-flex justify-content-between align-items-center px-4 py-2 bg-white">
        <!-- Logo e título -->
        <div class="d-flex align-items-center gap-2">
            <img src="../uploads/placa.png" alt="Logo" width="40"> 
            <h4 class="m-0">Controle de Estacionamento</h4>
        </div>

        <!-- Login e Logout -->
        <div class="d-flex align-items-center gap-3">
            <div class="user-info d-flex align-items-center gap-2">
                <i class="bi bi-file-person fs-5"></i>
                <?= isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : 'Usuário' ?>
            </div>
            <a href="?logout=true" class="btn btn-danger d-flex align-items-center p-1">
                <i class="bi bi-power fs-5"></i>
            </a>
        </div>
    </div>

    
     <!-- Barra de navegação -->
     <ul class="nav nav-tabs justify-content-center mt-3">
    <li class="nav-item">
            <a class="nav-link active" href="entradaSaidaVeiculoC.php">Registro de Entrada/Saída</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_motoristaC.php">Motoristas Oficiais</a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link active" href="listar_acessos_estacionamentoC.php">Acessos Liberados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="listarEstacionamentoC.php">Veicúlos Liberados</a>
        </li>
       
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarVeiculoEstacionamentoC.php">Cadastrar Veículos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="cadastrarNovoMotoristaC.php">Novo Cadastro Motorista</a>
        </li>
    </ul>
    <br><br>
    <div class="container">
        <h4>ESTACIONAMENTO C</h4>

        <div class="row">
            <div class="col-md-6">
             <div class="mt-4">
    <label for="placa">Digite a placa:</label>
    <input id="placa" class="form-control select-pequeno" placeholder="Digite a placa do veículo" required>
</div>

                <div class="mt-4">           
                    <form id="entrada-veiculo" method="POST">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="marca">Marca:</label>
                                <input type="text" id="marca" name="marca" class="form-control" readonly>
                            </div>
                            
                            <div class="form-group col-md-3">
                                <label for="modelo">Modelo:</label>
                                <input type="text" id="modelo" name="modelo" class="form-control" readonly>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="cor">Cor:</label>
                                <input type="text" id="cor" name="cor" class="form-control" readonly>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="tipo">Tipo:</label>
                                <input type="text" id="tipo" name="tipo" class="form-control" readonly>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="motorista">Motorista:</label>
                                <input type="text" id="motorista" name="motorista" class="form-control" readonly>
                            </div>

                            <!-- Campo de seleção de motoristas oficiais (apenas para veículos oficiais) -->
                            <div class="form-group col-md-6" id="motorista-select-container" style="display: none;">
                                <label for="motorista-select">Selecione o Motorista:</label>
                                <select id="motorista-select" name="motorista-select" class="form-control">
                                    <option value="">Selecione um motorista</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="usuario_logado">Usuário Logado:</label>
                                <input type="text" id="usuario_logado" name="usuario_logado" class="form-control" value="<?= isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : 'Usuário' ?>" readonly>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="qtd_passageiros">Quantidade de Passageiros:</label>
                                <input type="number" id="qtd_passageiros" name="qtd_passageiros" class="form-control" min="0" required>
                            </div>
                        </div>
                        <br>

                        <!-- Botão de Registrar Entrada -->
                        <div style="display: flex; justify-content: left;">
                            <button type="button" id="registrarEntrada" class="btn btn-success btn-sm" style="padding: 5px 10px; font-size: 14px;">Registrar Entrada</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Card de Vagas Disponíveis -->
                <div class="row text-center justify-content-center">
                    <div class="col-md-12 mb-3">
                        <div class="card shadow mx-auto" style="max-width: 50rem;">
                            <div class="card-body">
                                <h2 class="card-title">Vagas Disponíveis</h2>
                                <p class="card-text fw-bold text-success">🚗 Particulares: <span id="total-particular">50</span> | Disponível: <span id="disponivel-particular">50</span></p>
                                <p class="card-text fw-bold text-primary">🏍️ Motos: <span id="total-moto">15</span> | Disponível: <span id="disponivel-moto">15</span></p>
                                <p class="card-text fw-bold text-danger">🚓 Oficiais: <span id="total-oficial">20</span> | Disponível: <span id="disponivel-oficial">20</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ocorrência (opcional) -->
                <div class="form-group mt-4">
                    <label for="ocorrencia">Ocorrência (opcional):</label>
                    <textarea id="ocorrencia" name="ocorrencia" class="form-control" rows="3" placeholder="Digite as ocorrências, se houver"></textarea>
                    <button type="button" id="registrarOcorrencia" class="btn btn-primary mt-2">Registrar Ocorrência</button>
                </div>
            </div>
        </div>

        <!-- Formulário de Saída do Veículo -->
        <div class="mt-4 d-flex justify-content-center">
            <div style="width: 120%;">        
                <form id="saida-veiculo" method="POST">
                    <table class="table table-bordered text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th style="min-width: 120px;">Placa</th>
                                <th style="min-width: 120px;">Marca</th>
                                <th style="min-width: 120px;">Modelo</th>
                                <th style="min-width: 100px;">Cor</th>
                                <th style="min-width: 100px;">Tipo</th>                   
                                <th style="min-width: 120px;">Motorista Entrada</th>
                                <th style="min-width: 80px;">Horário de Entrada</th>
                                <th style="min-width: 120px;">Motorista Saída</th>
                                <th style="min-width: 120px;">Qtd. Passageiros Saída</th>
                                <th style="min-width: 80px;">Horário de Saída</th>
                                <th style="min-width: 120px;">Usuário Saída</th>
                                <th style="min-width: 70px;">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="saida-veiculo-body">
                            <!-- Linhas de veículos serão adicionadas aqui -->
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Limpar localStorage ao fazer login
            if (window.location.search.includes('login=true')) {
                var veiculos = JSON.parse(localStorage.getItem('veiculos')) || [];
                veiculos = veiculos.filter(function(veiculo) {
                    return !veiculo.horario_saida;
                });
                localStorage.setItem('veiculos', JSON.stringify(veiculos));
            }

            // Carregar placas da ESTACIONAMENTO C 
            $.ajax({
                url: 'entradaSaidaVeiculoC.php',
                method: 'GET',
                data: { action: 'get_placas_estacionamento_c' },
                success: function (data) {
                    var veiculos = JSON.parse(data);
                    var selectPlaca = $('#placa');

                    if (veiculos.length > 0) {
                        veiculos.forEach(function (veiculo) {
                            selectPlaca.append('<option value="' + veiculo.placa + '">' + veiculo.placa + '</option>');
                        });
                    } else {
                        selectPlaca.append('<option value="">Nenhum veículo encontrado na estacionamento c</option>');
                    }

                    // Buscar detalhes do veículo e motorista ao selecionar uma placa
                    $('#placa').on('change', function () {
                        var placaSelecionada = $(this).val();

                        $.ajax({
                            url: 'entradaSaidaVeiculoC.php',
                            method: 'GET',
                            data: { action: 'get_veiculo_motorista', placa: placaSelecionada },
                            success: function (data) {
                                var veiculo = JSON.parse(data);
                                console.log(veiculo); // Depuração: ver dados retornados

                                if (veiculo.error) {
                                    alert(veiculo.error); // Mostrar erro caso haja
                                    return;
                                }

                                if (veiculo.modelo) {
                                    $('#marca').val(veiculo.marca);
                                    $('#modelo').val(veiculo.modelo);
                                    $('#cor').val(veiculo.cor);
                                    $('#tipo').val(veiculo.tipo);
                                    
                                    if (veiculo.tipo === 'OFICIAL') {
                                        $('#motorista').val('');
                                        $('#motorista-select-container').show();
                                        
                                        var motoristaSelect = $('#motorista-select');
                                        motoristaSelect.empty();
                                        motoristaSelect.append('<option value="">Selecione um motorista</option>');
                                        
                                        // Verifica se 'motorista' é um array antes de iterar
                                        if (Array.isArray(veiculo.motoristas_oficiais)) {
                                            veiculo.motoristas_oficiais.forEach(function (motorista) {
                                                motoristaSelect.append('<option value="' + motorista.nome + '">' + motorista.nome + '</option>');
                                            });
                                        } else {
                                            alert('Nenhum motorista disponível para este veículo oficial.');
                                        }
                                    } else {
                                        $('#motorista').val(veiculo.motorista ? veiculo.motorista : 'Digite o Nome do Motorista'); // Garante que preencha corretamente
                                        $('#motorista-select-container').hide();
                                    }
                                } else {
                                    alert('Veículo não encontrado.');
                                }
                            }
                        });
                    });
                }
            });

            // Função para atualizar o total de vagas e vagas disponíveis por tipo
            function atualizarTotalVagas() {
                var totalParticular = localStorage.getItem('total-particular') || 50;
                var disponivelParticular = localStorage.getItem('disponivel-particular') || 50;
                var totalMoto = localStorage.getItem('total-moto') || 15;
                var disponivelMoto = localStorage.getItem('disponivel-moto') || 15;
                var totalOficial = localStorage.getItem('total-oficial') || 20;
                var disponivelOficial = localStorage.getItem('disponivel-oficial') || 20;

                $('#total-particular').text(totalParticular);
                $('#disponivel-particular').text(disponivelParticular);
                $('#total-moto').text(totalMoto);
                $('#disponivel-moto').text(disponivelMoto);
                $('#total-oficial').text(totalOficial);
                $('#disponivel-oficial').text(disponivelOficial);
            }

            // Atualizar total de vagas e vagas disponíveis ao carregar a página
            atualizarTotalVagas();

            // Carregar dados do localStorage ao carregar a página
            carregarDadosLocalStorage();

            // Função para registrar a entrada do veículo
            $('#registrarEntrada').click(function () {
                var placa = $('#placa').val();
                var modelo = $('#modelo').val();
                var cor = $('#cor').val();
                var tipo = $('#tipo').val();
                var marca = $('#marca').val();
                var motoristaEntrada = $('#motorista-select-container').is(':visible') ? $('#motorista-select').val() : $('#motorista').val();
                var qtdPassageiros = $('#qtd_passageiros').val();
                var horario_entrada = formatarDataHora(new Date());

                // Verificar se o motoristaEntrada é válido
                if ($('#motorista-select-container').is(':visible') && motoristaEntrada === '') {
                    alert('Por favor, selecione um motorista.');
                    return;
                } else if (!$('#motorista-select-container').is(':visible') && motoristaEntrada === '') {
                    alert('Por favor, digite o nome do motorista.');
                    return;
                }

                $.ajax({
                    url: 'registrarEntradaC.php', // URL do arquivo PHP para registrar a entrada
                    type: 'POST',
                    data: {
                        placa: placa,
                        modelo: modelo,
                        cor: cor,
                        tipo: tipo,
                        marca: marca,
                        motorista_entrada: motoristaEntrada,
                        qtd_passageiros: qtdPassageiros,
                        horario_entrada: horario_entrada
                    },
                    success: function (response) {
                        alert(response);

                        // Adicionar o veículo ao formulário de saída
                        adicionarVeiculoSaidaCorrigido(placa, marca, modelo, cor, tipo, motoristaEntrada, formatarHora(new Date()));

                        // Atualizar o contador de vagas disponíveis
                        atualizarContadorDisponivel(tipo, -1);

                        // Atualizar o card de contagem de vagas
                        atualizarTotalVagas();
                    },
                    error: function (xhr, status, error) {
                        alert('Erro ao registrar entrada no Estacionamento C: ' + error);
                    }
                });
            });

            // Função para adicionar veículo ao formulário de saída
            function adicionarVeiculoSaidaCorrigido(placa, marca, modelo, cor, tipo, motorista, horario_entrada) {
                var rowCount = $('#saida-veiculo-body tr').length + 1;

                var motoristaSaidaField = tipo === 'OFICIAL' ? 
                    `<select name="motorista_saida[]" class="form-control">
                        <option value="">Selecione um motorista</option>
                    </select>` : 
                    `<input type="text" name="motorista_saida[]" class="form-control" value="${motorista}" readonly>`;

                var newRow = `
                    <tr>
                        <td>${rowCount}</td>
                        <td><input type="text" name="placa_saida[]" class="form-control" value="${placa}" readonly></td>
                        <td><input type="text" name="marca_saida[]" class="form-control" value="${marca}" readonly></td>
                        <td><input type="text" name="modelo_saida[]" class="form-control" value="${modelo}" readonly></td>
                        <td><input type="text" name="cor_saida[]" class="form-control" value="${cor}" readonly></td>
                        <td><input type="text" name="tipo_saida[]" class="form-control" value="${tipo}" readonly></td>
                        <td><input type="text" name="motorista_entrada[]" class="form-control" value="${motorista}" readonly></td>
                        <td><input type="text" name="horario_entrada[]" class="form-control" value="${horario_entrada}" readonly></td>
                        <td>${motoristaSaidaField}</td>
                        <td><input type="number" name="qtd_passageiros_saida[]" class="form-control" min="0"></td>
                        <td><input type="text" name="horario_saida[]" class="form-control" readonly></td>
                        <td><input type="text" name="usuario_saida[]" class="form-control" value="<?= isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : 'Usuário' ?>" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="registrarSaida(this)">Registrar Saída</button></td>
                    </tr>
                `;

                $('#saida-veiculo-body').append(newRow);

                // Preencher a lista de motoristas oficiais para o novo campo de seleção
                if (tipo === 'OFICIAL') {
                    $.ajax({
                        url: 'entradaSaidaVeiculoC.php',
                        method: 'GET',
                        data: { action: 'get_motoristas_oficiais' },
                        success: function (data) {
                            var motoristas = JSON.parse(data);
                            var motoristaSelect = $('select[name="motorista_saida[]"]').last();
                            motoristas.forEach(function (motorista) {
                                motoristaSelect.append('<option value="' + motorista.nome + '">' + motorista.nome + '</option>');
                            });
                        }
                    });
                }
            }

            // Função para formatar a data e hora
            function formatarDataHora(data) {
                const ano = data.getFullYear();
                const mes = String(data.getMonth() + 1).padStart(2, '0');
                const dia = String(data.getDate()).padStart(2, '0');
                const horas = String(data.getHours()).padStart(2, '0');
                const minutos = String(data.getMinutes()).padStart(2, '0');
                const segundos = String(data.getSeconds()).padStart(2, '0');
                return `${ano}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;
            }

            // Função para formatar o horário
            function formatarHora(data) {
                const horas = String(data.getHours()).padStart(2, '0');
                const minutos = String(data.getMinutes()).padStart(2, '0');
                return `${horas}:${minutos}`;
            }

            // Função para salvar dados no localStorage
            function salvarDadosLocalStorage() {
                var veiculos = [];
                $('#saida-veiculo-body tr').each(function () {
                    var veiculo = {
                        placa: $(this).find('input[name="placa_saida[]"]').val(),
                        marca: $(this).find('input[name="marca_saida[]"]').val(),
                        modelo: $(this).find('input[name="modelo_saida[]"]').val(),
                        cor: $(this).find('input[name="cor_saida[]"]').val(),
                        tipo: $(this).find('input[name="tipo_saida[]"]').val(),
                        motorista_entrada: $(this).find('input[name="motorista_entrada[]"]').val(),
                        horario_entrada: $(this).find('input[name="horario_entrada[]"]').val(),
                        motorista_saida: $(this).find('select[name="motorista_saida[]"]').val() || $(this).find('input[name="motorista_saida[]"]').val(),
                        qtd_passageiros_saida: $(this).find('input[name="qtd_passageiros_saida[]"]').val(),
                        ocorrencia_saida: $(this).find('textarea[name="ocorrencia_saida[]"]').val(),
                        horario_saida: $(this).find('input[name="horario_saida[]"]').val()
                    };
                    veiculos.push(veiculo);
                });
                localStorage.setItem('veiculos', JSON.stringify(veiculos));
                salvarCardLocalStorage();
            }

            // Função para carregar dados do localStorage
            function carregarDadosLocalStorage() {
                var veiculos = JSON.parse(localStorage.getItem('veiculos')) || [];
                veiculos.forEach(function (veiculo) {
                    adicionarVeiculoSaidaCorrigido(
                        veiculo.placa,
                        veiculo.marca,
                        veiculo.modelo,
                        veiculo.cor,
                        veiculo.tipo,
                        veiculo.motorista_entrada,
                        veiculo.horario_entrada
                    );
                    var row = $('#saida-veiculo-body').last();
                    row.find('input[name="horario_saida[]"]').val(veiculo.horario_saida);
                    row.find('input[name="qtd_passageiros_saida[]"]').val(veiculo.qtd_passageiros_saida);
                    row.find('textarea[name="ocorrencia_saida[]"]').val(veiculo.ocorrencia_saida);
                    if (veiculo.horario_saida) {
                        row.find('select[name="motorista_saida[]"], input[name="motorista_saida[]"]').prop('disabled', true);
                        row.find('button').prop('disabled', true);
                    }
                });
            }

            // Função para atualizar o contador de vagas disponíveis
            function atualizarContadorDisponivel(tipo, delta) {
                var disponivelElement;
                if (tipo === 'OFICIAL') {
                    disponivelElement = $('#disponivel-oficial');
                } else if (tipo === 'PARTICULAR') {
                    disponivelElement = $('#disponivel-particular');
                } else if (tipo === 'MOTO') {
                    disponivelElement = $('#disponivel-moto');
                }

                var disponivel = parseInt(disponivelElement.text());
                disponivelElement.text(disponivel + delta);

                // Salvar os valores atualizados no localStorage
                localStorage.setItem('disponivel-oficial', $('#disponivel-oficial').text());
                localStorage.setItem('disponivel-particular', $('#disponivel-particular').text());
                localStorage.setItem('disponivel-moto', $('#disponivel-moto').text());
            }

            // Registrar ocorrência ao clicar no botão
            $('#registrarOcorrencia').click(function () {
                var placaOuMotorista = $('#placa').val(); // Usar o campo de placa como referência
                var ocorrenciaTexto = $('#ocorrencia').val();
                var localizacao = 'ESTACIONAMENTO C'; // Definir a localização fixa para este formulário

                if (!placaOuMotorista || !ocorrenciaTexto) {
                    alert('Por favor, preencha a placa e a ocorrência.');
                    return;
                }

                $.ajax({
                    url: 'registrarOcorrenciaC.php', // URL do arquivo PHP para registrar a ocorrência
                    method: 'POST',
                    data: {
                        placa_motorista: placaOuMotorista,
                        ocorrencia: ocorrenciaTexto,
                        localizacao: localizacao // Enviar a localização
                    },
                    success: function (response) {
                        alert(response);
                        $('#ocorrencia').val(''); // Limpar o campo de ocorrência após o registro

                        // Set the session message via an AJAX request
                        $.ajax({
                            url: '../paginaAdm/setSessionMessage.php',
                            method: 'POST',
                            data: { mensagem: 'Ocorrência Gerada' },
                            success: function () {
                                alert('Mensagem configurada com sucesso.');
                            },
                            error: function () {
                                alert('Erro ao configurar a mensagem.');
                            }
                        });
                    },
                    error: function (xhr, status, error) {
                        alert('Erro ao registrar ocorrência no Estacionamento C: ' + error);
                    }
                });
            });
        });

        // Certifique-se de que a função está no escopo global
        window.registrarSaida = function(button) {
            var row = $(button).closest("tr"); // Encontra a linha correspondente ao botão clicado

            var dados = {
                placa: row.find('input[name="placa_saida[]"]').val(),
                motorista_saida: row.find('select[name="motorista_saida[]"]').val() || row.find('input[name="motorista_saida[]"]').val(),
                qtd_passageiros_saida: row.find('input[name="qtd_passageiros_saida[]"]').val(),
                ocorrencia_saida: row.find('textarea[name="ocorrencia_saida[]"]').val(),
                horario_saida: new Date().toISOString().slice(0, 19).replace('T', ' ') // Formata a data e hora completa para envio ao backend
            };

            console.log("Dados enviados para registrar saída:", dados); // Log para depuração

            // Verificar se o motorista de saída foi preenchido
            if (!dados.motorista_saida || dados.motorista_saida.trim() === '') {
                alert('Por favor, preencha o motorista de saída.');
                return;
            }

            $.ajax({
                url: 'registrarSaidaC.php', // URL do arquivo PHP para registrar a saída
                type: 'POST',
                data: dados,
                success: function (response) {
                    console.log("Resposta do servidor:", response); // Log para depuração
                    var result = JSON.parse(response);

                    if (result.success) {
                        alert(result.success);

                        // Atualizar o horário de saída e o usuário de saída na tabela
                        row.find('input[name="horario_saida[]"]').val(formatarHora(new Date())); // Formata o horário para exibição no padrão HH:MM
                        row.find('input[name="usuario_saida[]"]').val(result.usuario_saida);

                        // Atualizar o contador de vagas disponíveis
                        var tipo = row.find('input[name="tipo_saida[]"]').val();
                        atualizarContadorDisponivel(tipo, 1); // Incrementa +1 no card

                        // Desabilitar os campos para indicar que a saída foi registrada
                        row.find('select[name="motorista_saida[]"], input[name="motorista_saida[]"]').prop('disabled', true);
                        row.find('button').prop('disabled', true);
                    } else if (result.error) {
                        alert(result.error); // Exibe mensagem de erro
                    }
                },
                error: function () {
                    alert('Erro ao registrar saída.');
                }
            });
        };

        // Função para formatar o horário no padrão HH:MM
        function formatarHora(data) {
            const horas = String(data.getHours()).padStart(2, '0');
            const minutos = String(data.getMinutes()).padStart(2, '0');
            return `${horas}:${minutos}`;
        }

        // Função para atualizar o total de vagas e vagas disponíveis
        function atualizarTotalVagas() {
            // Simulação de atualização dinâmica (pode ser ajustado para buscar do backend, se necessário)
            var totalParticular = parseInt($('#total-particular').text());
            var disponivelParticular = parseInt($('#disponivel-particular').text());
            var totalMoto = parseInt($('#total-moto').text());
            var disponivelMoto = parseInt($('#disponivel-moto').text());
            var totalOficial = parseInt($('#total-oficial').text());
            var disponivelOficial = parseInt($('#disponivel-oficial').text());

            // Atualizar os valores no card
            $('#total-particular').text(totalParticular);
            $('#disponivel-particular').text(disponivelParticular);
            $('#total-moto').text(totalMoto);
            $('#disponivel-moto').text(disponivelMoto);
            $('#total-oficial').text(totalOficial);
            $('#disponivel-oficial').text(disponivelOficial);
        }

        // Função para atualizar o contador de vagas disponíveis
        function atualizarContadorDisponivel(tipo, delta) {
            var disponivelElement;
            if (tipo === 'OFICIAL') {
                disponivelElement = $('#disponivel-oficial');
            } else if (tipo === 'PARTICULAR') {
                disponivelElement = $('#disponivel-particular');
            } else if (tipo === 'MOTO') {
                disponivelElement = $('#disponivel-moto');
            }

            var disponivel = parseInt(disponivelElement.text());
            disponivelElement.text(disponivel + delta); // Incrementa ou decrementa o valor

            // Salvar os valores atualizados no localStorage
            localStorage.setItem('disponivel-oficial', $('#disponivel-oficial').text());
            localStorage.setItem('disponivel-particular', $('#disponivel-particular').text());
            localStorage.setItem('disponivel-moto', $('#disponivel-moto').text());
        }


        $(document).ready(function () {
        // Configurar o autocomplete para o campo de placa
        $("#placa").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: 'entradaSaidaVeiculoC.php', // Endpoint correto
                    method: 'GET',
                    data: {
                        term: request.term // Termo digitado pelo usuário
                    },
                    success: function (data) {
                        try {
                            var placas = JSON.parse(data);
                            response(placas); // Retorna as placas para o autocomplete
                        } catch (e) {
                            console.error("Erro ao processar os dados do autocomplete:", e);
                            response([]);
                        }
                    },
                    error: function () {
                        response([]); // Retorna vazio em caso de erro
                    }
                });
            },
            minLength: 1, // Inicia a busca após digitar 1 caractere
            select: function (event, ui) {
                // Quando uma placa é selecionada, buscar os detalhes do veículo
                var placaSelecionada = ui.item.value;
                $.ajax({
                    url: 'entradaSaidaVeiculoC.php',
                    method: 'GET',
                    data: { action: 'get_veiculo_motorista', placa: placaSelecionada },
                    success: function (data) {
                        var veiculo = JSON.parse(data);
                        if (veiculo.error) {
                            alert(veiculo.error);
                            return;
                        }

                        // Preencher os campos com os dados do veículo
                        $('#marca').val(veiculo.marca);
                        $('#modelo').val(veiculo.modelo);
                        $('#cor').val(veiculo.cor);
                        $('#tipo').val(veiculo.tipo);
                        $('#motorista').val(veiculo.motorista || 'Motorista não encontrado');
                    }
                });
            }
        });
    });
    
    // Save card data to localStorage
    function salvarCardLocalStorage() {
        localStorage.setItem('total-particular', $('#total-particular').text());
        localStorage.setItem('disponivel-particular', $('#disponivel-particular').text());
        localStorage.setItem('total-moto', $('#total-moto').text());
        localStorage.setItem('disponivel-moto', $('#disponivel-moto').text());
        localStorage.setItem('total-oficial', $('#total-oficial').text());
        localStorage.setItem('disponivel-oficial', $('#disponivel-oficial').text());
    }

    // Load card data from localStorage
    function carregarCardLocalStorage() {
        $('#total-particular').text(localStorage.getItem('total-particular') || 50);
        $('#disponivel-particular').text(localStorage.getItem('disponivel-particular') || 50);
        $('#total-moto').text(localStorage.getItem('total-moto') || 15);
        $('#disponivel-moto').text(localStorage.getItem('disponivel-moto') || 15);
        $('#total-oficial').text(localStorage.getItem('total-oficial') || 20);
        $('#disponivel-oficial').text(localStorage.getItem('disponivel-oficial') || 20);
    }

    // Save all data to localStorage
    function salvarDadosLocalStorage() {
        var veiculos = [];
        $('#saida-veiculo-body tr').each(function () {
            var veiculo = {
                placa: $(this).find('input[name="placa_saida[]"]').val(),
                marca: $(this).find('input[name="marca_saida[]"]').val(),
                modelo: $(this).find('input[name="modelo_saida[]"]').val(),
                cor: $(this).find('input[name="cor_saida[]"]').val(),
                tipo: $(this).find('input[name="tipo_saida[]"]').val(),
                motorista_entrada: $(this).find('input[name="motorista_entrada[]"]').val(),
                horario_entrada: $(this).find('input[name="horario_entrada[]"]').val(),
                motorista_saida: $(this).find('select[name="motorista_saida[]"]').val() || $(this).find('input[name="motorista_saida[]"]').val(),
                qtd_passageiros_saida: $(this).find('input[name="qtd_passageiros_saida[]"]').val(),
                horario_saida: $(this).find('input[name="horario_saida[]"]').val()
            };
            veiculos.push(veiculo);
        });
        localStorage.setItem('veiculos', JSON.stringify(veiculos));
        salvarCardLocalStorage();
    }

    // Load all data from localStorage
    function carregarDadosLocalStorage() {
        carregarCardLocalStorage();
        var veiculos = JSON.parse(localStorage.getItem('veiculos')) || [];
        veiculos.forEach(function (veiculo) {
            adicionarVeiculoSaidaCorrigido(
                veiculo.placa,
                veiculo.marca,
                veiculo.modelo,
                veiculo.cor,
                veiculo.tipo,
                veiculo.motorista_entrada,
                veiculo.horario_entrada
            );
            var row = $('#saida-veiculo-body tr').last();
            row.find('input[name="horario_saida[]"]').val(veiculo.horario_saida);
            row.find('input[name="qtd_passageiros_saida[]"]').val(veiculo.qtd_passageiros_saida);
            if (veiculo.horario_saida) {
                row.find('select[name="motorista_saida[]"], input[name="motorista_saida[]"]').prop('disabled', true);
                row.find('button').prop('disabled', true);
            }
        });
    }

    // Save data on page unload
    $(window).on('beforeunload', function () {
        salvarDadosLocalStorage();
    });

    // Load data on page load
    carregarDadosLocalStorage();



    </script>
</body>
</html>