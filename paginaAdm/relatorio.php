<?php
require_once '../Conexao.php';
require_once '../vendor/autoload.php'; // Include DOMPDF autoloader

use Dompdf\Dompdf;

// Processar os dados enviados pelo formulário
$filters = [];
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['localizacao'])) {
        if ($_POST['localizacao'] === 'Garagem Sede') {
            $filters[] = "(localizacao LIKE 'Garagem Sede%')";
        } else {
            $filters[] = "localizacao = :localizacao";
            $params[':localizacao'] = $_POST['localizacao'];
        }
    }
    if (!empty($_POST['data_inicio']) && !empty($_POST['hora_inicio'])) {
        $filters[] = "horario_entrada >= :data_hora_inicio";
        $params[':data_hora_inicio'] = $_POST['data_inicio'] . ' ' . $_POST['hora_inicio'];
    }
    if (!empty($_POST['data_fim']) && !empty($_POST['hora_fim'])) {
        $filters[] = "horario_saida <= :data_hora_fim";
        $params[':data_hora_fim'] = $_POST['data_fim'] . ' ' . $_POST['hora_fim'];
    }
}

// Exemplo de envio dos filtros via formulário ou requisição
?>
<form method="POST" action="imprimirRelatorio.php">
    <input type="hidden" name="filters" value='<?php echo htmlspecialchars(json_encode($filters), ENT_QUOTES, 'UTF-8'); ?>'>
    <input type="hidden" name="params" value='<?php echo htmlspecialchars(json_encode($params), ENT_QUOTES, 'UTF-8'); ?>'>
    <button type="submit">Gerar Relatório</button>
</form>
<?php

// Consultar os dados da tabela registro_veiculos com filtros
try {
    $sqlRegistroVeiculos = "
        SELECT 
            placa,
            marca,
            modelo,
            cor,
            tipo,
            motorista_entrada,
            motorista_saida,
            horario_entrada,
            horario_saida,
            usuario_logado,
            usuario_saida,
            qtd_passageiros,
            qtd_passageiros_saida
        FROM registro_veiculos
    ";
    if (!empty($filters)) {
        $sqlRegistroVeiculos .= " WHERE " . implode(' AND ', $filters);
    }
    $sqlRegistroVeiculos .= " ORDER BY horario_entrada DESC";

    $stmtRegistroVeiculos = $pdo->prepare($sqlRegistroVeiculos);
    $stmtRegistroVeiculos->execute($params);
    $registroVeiculos = $stmtRegistroVeiculos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar os registros de veículos: " . $e->getMessage();
    exit;
}

// Consultar os dados da tabela ocorrencias com filtros
try {
    $sqlOcorrencias = "
        SELECT 
            placa,
            ocorrencia,
            horario,
            usuario,
            localizacao
        FROM ocorrencias
    ";
    if (!empty($filters)) {
        $sqlOcorrencias .= " WHERE " . implode(' AND ', $filters);
    }
    $sqlOcorrencias .= " ORDER BY horario DESC";

    $stmtOcorrencias = $pdo->prepare($sqlOcorrencias);
    $stmtOcorrencias->execute($params);
    $ocorrencias = $stmtOcorrencias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar as ocorrências: " . $e->getMessage();
    exit;
}

// Gerar o conteúdo HTML para o PDF
$html = '<style>
            body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
            h1, h2 { text-align: center; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #000; padding: 4px; text-align: left; }
            th { background-color: #f2f2f2; }
            .no-data { text-align: center; margin-top: 20px; }
            .page-break { page-break-before: always; }
        </style>';

$html .= '<h1>Relatório de Entrada e Saída de Veículos</h1>';

if (count($registroVeiculos) > 0) {
    $html .= '<h2>Registros de Veículos</h2>';
    $html .= '<table>';
    $html .= '<thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Cor</th>
                    <th>Tipo</th>
                    <th>Motorista Entrada</th>
                    <th>Motorista Saída</th>
                    <th>Horário Entrada</th>
                    <th>Horário Saída</th>
                    <th>Usuário Logado</th>
                    <th>Usuário Saída</th>
                    <th>Qtd. Passageiros Entrada</th>
                    <th>Qtd. Passageiros Saída</th>
                </tr>
              </thead>';
    $html .= '<tbody>';
    foreach ($registroVeiculos as $registro) {
        $horarioEntrada = DateTime::createFromFormat('Y-m-d H:i:s', $registro['horario_entrada']);
        $horarioSaida = DateTime::createFromFormat('Y-m-d H:i:s', $registro['horario_saida']);
        $html .= '<tr>
                    <td>' . htmlspecialchars($registro['placa']) . '</td>
                    <td>' . htmlspecialchars($registro['marca']) . '</td>
                    <td>' . htmlspecialchars($registro['modelo']) . '</td>
                    <td>' . htmlspecialchars($registro['cor']) . '</td>
                    <td>' . htmlspecialchars($registro['tipo']) . '</td>
                    <td>' . htmlspecialchars($registro['motorista_entrada']) . '</td>
                    <td>' . htmlspecialchars($registro['motorista_saida']) . '</td>
                    <td>' . ($horarioEntrada ? $horarioEntrada->format('d/m/Y H:i:s') : '') . '</td>
                    <td>' . ($horarioSaida ? $horarioSaida->format('d/m/Y H:i:s') : '') . '</td>
                    <td>' . htmlspecialchars($registro['usuario_logado']) . '</td>
                    <td>' . htmlspecialchars($registro['usuario_saida']) . '</td>
                    <td>' . htmlspecialchars($registro['qtd_passageiros']) . '</td>
                    <td>' . htmlspecialchars($registro['qtd_passageiros_saida']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p class="no-data">Nenhum registro de veículo encontrado.</p>';
}

if (count($ocorrencias) > 0) {
    $html .= '<h2 class="page-break">Ocorrências</h2>';
    $html .= '<table>';
    $html .= '<thead>
                <tr>
                    <th>Placa</th>
                    <th>Ocorrência</th>
                    <th>Horário</th>
                    <th>Usuário</th>
                    <th>Localização</th>
                </tr>
              </thead>';
    $html .= '<tbody>';
    foreach ($ocorrencias as $ocorrencia) {
        $horarioOcorrencia = DateTime::createFromFormat('Y-m-d H:i:s', $ocorrencia['horario']);
        $html .= '<tr>
                    <td>' . htmlspecialchars($ocorrencia['placa']) . '</td>
                    <td>' . htmlspecialchars($ocorrencia['ocorrencia']) . '</td>
                    <td>' . ($horarioOcorrencia ? $horarioOcorrencia->format('d/m/Y H:i:s') : '') . '</td>
                    <td>' . htmlspecialchars($ocorrencia['usuario']) . '</td>
                    <td>' . htmlspecialchars($ocorrencia['localizacao']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p class="no-data">Nenhuma ocorrência encontrada.</p>';
}

// Configurar DOMPDF para evitar margens cortadas
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Configurar para paisagem
$dompdf->set_option('isHtml5ParserEnabled', true);
$dompdf->set_option('isPhpEnabled', true);
$dompdf->set_option('isRemoteEnabled', true);

// Ajustar margens e layout contínuo
$dompdf->set_option('defaultFont', 'Arial');
$dompdf->set_option('dpi', 96); // Ajustar DPI para melhor renderização
$dompdf->set_option('isFontSubsettingEnabled', true);
$dompdf->set_option('isCssFloatEnabled', true);

$dompdf->render();
$dompdf->stream("relatorio.pdf", ["Attachment" => false]);
exit;
?>
