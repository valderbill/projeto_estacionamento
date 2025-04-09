<?php
require_once '../Conexao.php';
require_once '../vendor/autoload.php'; // Certifique-se de que o DomPDF está instalado via Composer

use Dompdf\Dompdf;

// Recuperar os filtros e parâmetros enviados
$filters = isset($_POST['filters']) ? json_decode($_POST['filters'], true) : [];
$params = isset($_POST['params']) ? json_decode($_POST['params'], true) : [];
$idsFiltrados = $_POST['idsFiltrados'] ?? '';

// Verificar se os filtros e parâmetros foram enviados corretamente
if (!is_array($filters) || !is_array($params)) {
    die("Erro: Filtros ou parâmetros não foram enviados corretamente.");
}

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
            localizacao,
            qtd_passageiros,
            qtd_passageiros_saida
        FROM registro_veiculos
    ";
    $whereClauses = [];
    $queryParams = [];

    // Construir cláusulas WHERE dinamicamente com base nos filtros
    foreach ($filters as $key => $value) {
        $whereClauses[] = "$key = :$key";
        $queryParams[":$key"] = $value;
    }

    if (!empty($idsFiltrados)) {
        $idsArray = explode(',', $idsFiltrados);
        $placeholders = implode(',', array_fill(0, count($idsArray), '?'));
        $whereClauses[] = "id IN ($placeholders)";
        $queryParams = array_merge($queryParams, $idsArray);
    }

    if (!empty($whereClauses)) {
        $sqlRegistroVeiculos .= " WHERE " . implode(' AND ', $whereClauses);
    }
    $sqlRegistroVeiculos .= " ORDER BY horario_entrada DESC";

    $stmtRegistroVeiculos = $pdo->prepare($sqlRegistroVeiculos);
    $stmtRegistroVeiculos->execute($queryParams);
    $registroVeiculos = $stmtRegistroVeiculos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao consultar os registros de veículos: " . $e->getMessage());
}

// Gerar o HTML do relatório
$html = '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 10px; /* Reduzido o tamanho da fonte */
    }
    th, td {
        border: 1px solid #000;
        padding: 4px; /* Reduzido o padding */
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    h1 {
        text-align: center;
        font-size: 16px; /* Reduzido o tamanho do título */
        margin-bottom: 15px;
    }
</style>
<h1>Relatório de Entrada e Saída de Veículos</h1>';

if (count($registroVeiculos) > 0) {
    $html .= '<table>';
    $html .= '<thead>
                <tr>
                    <th style="width: 8%;">Placa</th>
                    <th style="width: 8%;">Marca</th>
                    <th style="width: 12%;">Modelo</th>
                    <th style="width: 8%;">Cor</th>
                    <th style="width: 8%;">Tipo</th>
                    <th style="width: 12%;">Motorista Entrada</th>
                    <th style="width: 12%;">Motorista Saída</th>
                    <th style="width: 12%;">Horário Entrada</th>
                    <th style="width: 12%;">Horário Saída</th>
                    <th style="width: 12%;">Usuário Logado</th>
                    <th style="width: 12%;">Usuário Saída</th>
                    <th style="width: 8%;">Qtd. Passageiros Entrada</th>
                    <th style="width: 8%;">Qtd. Passageiros Saída</th>
                </tr>
              </thead>';
    $html .= '<tbody>';
    foreach ($registroVeiculos as $registro) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($registro['placa']) . '</td>
                    <td>' . htmlspecialchars($registro['marca']) . '</td>
                    <td>' . htmlspecialchars($registro['modelo']) . '</td>
                    <td>' . htmlspecialchars($registro['cor']) . '</td>
                    <td>' . htmlspecialchars($registro['tipo']) . '</td>
                    <td>' . htmlspecialchars($registro['motorista_entrada']) . '</td>
                    <td>' . htmlspecialchars($registro['motorista_saida']) . '</td>
                    <td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($registro['horario_entrada']))) . '</td>
                    <td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($registro['horario_saida']))) . '</td>
                    <td>' . htmlspecialchars($registro['usuario_logado']) . '</td>
                    <td>' . htmlspecialchars($registro['usuario_saida']) . '</td>
                    <td>' . htmlspecialchars($registro['qtd_passageiros']) . '</td>
                    <td>' . htmlspecialchars($registro['qtd_passageiros_saida']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p>Nenhum registro encontrado.</p>';
}

// Gerar o PDF com DomPDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Exibir o PDF em outra página
header("Content-Type: application/pdf");
echo $dompdf->output();
exit;
?> 
