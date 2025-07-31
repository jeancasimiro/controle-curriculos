<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("192.168.0.4", "lab", "lab", "dicweb", 3306);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    die("Erro ao definir charset: " . $conn->error);
}

$area = isset($_GET['area']) ? $conn->real_escape_string($_GET['area']) : '';
$pesquisa = isset($_GET['pesquisa']) ? $conn->real_escape_string($_GET['pesquisa']) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$ordenar = isset($_GET['ordenar']) ? $conn->real_escape_string($_GET['ordenar']) : 'nome';

$sql = "SELECT id, nome_completo as nome, telefone, email, resultado as status, data_cadastro 
        FROM candidatos_curriculo WHERE ativo = 1";
$conditions = array();
$params = array();

if (!empty($area) && in_array($area, ['Técnica', 'Administrativo'])) {
    $conditions[] = "area_interesse = ?";
    $params[] = $area;
}

if (!empty($pesquisa)) {
    $conditions[] = "(nome_completo LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%'))";
    $params[] = $pesquisa;
    $params[] = $pesquisa;
}

if (!empty($status) && $status != 'todos' && in_array($status, ['Aprovado', 'Reprovado', 'Possível contratação', 'Pendente'])) {
    $conditions[] = "resultado = ?";
    $params[] = $status;
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Ordenação
if ($ordenar == 'recentes') {
    $sql .= " ORDER BY data_cadastro DESC";
} else {
    $sql .= " ORDER BY nome_completo ASC"; // padrão
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Erro na preparação da consulta: " . $conn->error);
}

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Erro na execução da consulta: " . $stmt->error);
}

$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Currículos</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filtro-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filtro-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ordenar-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ordenar-label {
            color: #495057;
        }
        
        .btn-ordenar {
            padding: 8px 12px;
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-ordenar:hover {
            background: #138496;
        }
        
        @media (max-width: 768px) {
            .filtro-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .ordenar-group {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Controle de Currículos</h1>
    
    <div class="area-selecao">
        <p>Selecione a área:</p>
        <form method="get" style="display:inline">
            <input type="hidden" name="area" value="Técnica">
            <button type="submit" class="<?php echo ($area == 'Técnica') ? 'ativo' : ''; ?>">Técnica</button>
        </form>
        <form method="get" style="display:inline">
            <input type="hidden" name="area" value="Administrativo">
            <button type="submit" class="<?php echo ($area == 'Administrativo') ? 'ativo' : ''; ?>">Administrativo</button>
        </form>
    </div>

    <div class="filtro-container">
        <div class="filtro-status">
            <form method="get" class="filtros-form">
                <?php if (!empty($area)): ?>
                    <input type="hidden" name="area" value="<?php echo htmlspecialchars($area); ?>">
                <?php endif; ?>
                
                <?php if (!empty($pesquisa)): ?>
                    <input type="hidden" name="pesquisa" value="<?php echo htmlspecialchars($pesquisa); ?>">
                <?php endif; ?>
                
                <label>Filtrar por status:</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="todos" <?php echo (empty($status) || $status == 'todos') ? 'selected' : ''; ?>>Todos</option>
                    <option value="Aprovado" <?php echo $status == 'Aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                    <option value="Reprovado" <?php echo $status == 'Reprovado' ? 'selected' : ''; ?>>Reprovado</option>
                    <option value="Possível contratação" <?php echo $status == 'Possível contratação' ? 'selected' : ''; ?>>Possível contratação</option>
                    <option value="Pendente" <?php echo $status == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                </select>
            </form>
        </div>
        
        <form method="get" class="ordenar-form">
            <?php if (!empty($area)): ?>
                <input type="hidden" name="area" value="<?php echo htmlspecialchars($area); ?>">
            <?php endif; ?>
            
            <?php if (!empty($pesquisa)): ?>
                <input type="hidden" name="pesquisa" value="<?php echo htmlspecialchars($pesquisa); ?>">
            <?php endif; ?>
            
            <?php if (!empty($status) && $status != 'todos'): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
            <?php endif; ?>
            
            <div class="ordenar-group">
                <span class="ordenar-label">Ordenado por:</span>
                <input type="hidden" name="ordenar" value="<?php echo $ordenar == 'recentes' ? 'nome' : 'recentes'; ?>">
                <button type="submit" class="btn-ordenar">
                    <?php echo $ordenar == 'recentes' ? 'Recentes' : 'Nome'; ?>
                </button>
            </div>
        </form>
    </div>

    <div class="barra-pesquisa">
        <form method="get">
            <?php if (!empty($area)): ?>
                <input type="hidden" name="area" value="<?php echo htmlspecialchars($area); ?>">
            <?php endif; ?>
            
            <?php if (!empty($status) && $status != 'todos'): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
            <?php endif; ?>
            
            <?php if (!empty($ordenar) && $ordenar != 'nome'): ?>
                <input type="hidden" name="ordenar" value="<?php echo htmlspecialchars($ordenar); ?>">
            <?php endif; ?>
            
            <input type="text" name="pesquisa" placeholder="Buscar por nome ou e-mail..." value="<?php echo htmlspecialchars($pesquisa); ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <a href="adicionar.php" class="btn-adicionar">+ Adicionar candidato</a>

    <?php if ($resultado->num_rows > 0): ?>
        <div class="tabela-candidatos">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="col-telefone">Telefone</th>
                        <th class="col-email">E-mail</th>
                        <th>Status</th>
                        <th class="col-acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($candidato = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidato['nome']); ?></td>
                            <td class="col-telefone"><?php echo htmlspecialchars($candidato['telefone']); ?></td>
                            <td class="col-email"><?php echo htmlspecialchars($candidato['email']); ?></td>
                            <td>
                                <?php 
                                    $statusExibicao = htmlspecialchars($candidato['status']);
                                    $statusClasse = strtolower(str_replace([' ', 'ç', 'ã', 'á', 'é', 'í', 'ó', 'ú'], ['-', 'c', 'a', 'a', 'e', 'i', 'o', 'u'], $statusExibicao));
                                ?>
                                <span class="status <?php echo $statusClasse; ?>">
                                    <?php echo $statusExibicao; ?>
                                </span>
                            </td>
                            <td class="col-acoes">
                                <a href="detalhes.php?id=<?php echo (int)$candidato['id']; ?>" class="btn-editar">Detalhes</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="sem-resultados">Nenhum candidato encontrado com os filtros atuais.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>