<?php
require_once '../Conexao.php'; // Conexão com o banco de dados
require_once '../vendor/autoload.php'; // Carregar o autoloader do dompdf

use Dompdf\Dompdf;

// Processar os filtros recebidos via POST
$filters = [];
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['filtered_localizacao'])) {
        $filters[] = "localizacao = :localizacao";
        $params[':localizacao'] = $_POST['filtered_localizacao'];
    }
    if (!empty($_POST['filtered_data_inicio']) && !empty($_POST['filtered_hora_inicio'])) {
        $filters[] = "horario >= :data_hora_inicio";
        $params[':data_hora_inicio'] = $_POST['filtered_data_inicio'] . ' ' . $_POST['filtered_hora_inicio'];
    }
    if (!empty($_POST['filtered_data_fim']) && !empty($_POST['filtered_hora_fim'])) {
        $filters[] = "horario <= :data_hora_fim";
        $params[':data_hora_fim'] = $_POST['filtered_data_fim'] . ' ' . $_POST['filtered_hora_fim'];
    }
}

// Consultar os dados da tabela ocorrencias com filtros
try {
    $sqlOcorrencias = "
        SELECT 
            id,
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
    $sqlOcorrencias .= " ORDER BY horario ASC"; // Changed to ascending order

    $stmtOcorrencias = $pdo->prepare($sqlOcorrencias);
    $stmtOcorrencias->execute($params);
    $ocorrencias = $stmtOcorrencias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar as ocorrências: " . $e->getMessage();
    exit;
}

// Gerar o conteúdo HTML para o PDF
$html = '
<style>
    body {
        font-family: Arial, sans-serif;
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    table tr:hover {
        background-color: #f1f1f1;
    }
</style>
<h2>Relatório de Ocorrências</h2>';

if (count($ocorrencias) > 0) {
    $html .= '<table>
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Ocorrência</th>
                        <th>Horário</th>
                        <th>Usuário</th>
                        <th>Localização</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($ocorrencias as $ocorrencia) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($ocorrencia['placa']) . '</td>
                    <td>' . htmlspecialchars($ocorrencia['ocorrencia']) . '</td>
                    <td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($ocorrencia['horario']))) . '</td>
                    <td>' . htmlspecialchars($ocorrencia['usuario']) . '</td>
                    <td>' . htmlspecialchars($ocorrencia['localizacao']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p>Nenhuma ocorrência encontrada.</p>';
}

// Configurar o dompdf
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Orientação paisagem para melhor visualização
$dompdf->render();

// Enviar o PDF para o navegador em uma nova aba
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="relatorio_ocorrencias.pdf"');
echo $dompdf->output();
exit;
?>
