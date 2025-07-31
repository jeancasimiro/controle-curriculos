<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("192.168.0.4", "lab", "lab", "dicweb", 3306);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $data_exclusao = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE candidatos_curriculo SET data_exclusao = ?, ativo = 0 WHERE id = ?");
    $stmt->bind_param("si", $data_exclusao, $id);
    
    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        die("Erro ao excluir candidato: " . $conn->error);
    }
}

// Processar alteração de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_situacao'])) {
    $nova_situacao = $conn->real_escape_string($_POST['nova_situacao']);
    $stmt = $conn->prepare("UPDATE candidatos_curriculo SET resultado = ? WHERE id = ?");
    $stmt->bind_param("si", $nova_situacao, $id);
    
    if ($stmt->execute()) {
        header("Location: detalhes.php?id=$id");
        exit();
    } else {
        die("Erro ao atualizar status: " . $conn->error);
    }
}

// Processar edição da descrição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_descricao'])) {
    $nova_descricao = str_replace(["\r\n", "\r"], "\n", trim($_POST['nova_descricao']));
    $stmt = $conn->prepare("UPDATE candidatos_curriculo SET descricao = ? WHERE id = ?");
    $stmt->bind_param("si", $nova_descricao, $id);
    
    if ($stmt->execute()) {
        header("Location: detalhes.php?id=$id");
        exit();
    } else {
        die("Erro ao atualizar descrição: " . $conn->error);
    }
}

// Obter dados do candidato
$stmt = $conn->prepare("SELECT * FROM candidatos_curriculo WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Candidato não encontrado.");
}

$candidato = $resultado->fetch_assoc();
$candidato['resultado'] = utf8_encode(utf8_decode($candidato['resultado']));

function formatarStatusParaClasse($status) {
    $status = iconv('UTF-8', 'ASCII//TRANSLIT', $status);
    $status = strtolower(preg_replace('/[^A-Za-z0-9-]/', '-', $status));
    return preg_replace('/-+/', '-', $status);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">
    <title>Detalhes do Candidato</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos gerais */
        .detalhes-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .detalhes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .detalhes-title {
            color: #333;
            margin: 0;
        }

        .detalhes-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detalhes-group {
            margin-bottom: 15px;
        }

        .detalhes-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .detalhes-value {
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #ddd;
            min-height: 20px;
        }

        .full-width {
            grid-column: span 2;
        }

        .detalhes-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        /* Estilos para botões */
        .btn-excluir {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-excluir:hover {
            background: #c82333;
        }

        .btn-alterar {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-alterar:hover {
            background: #0069d9;
        }

        .btn-editar {
            background: #17a2b8;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            margin-top: 5px;
            justify-content: flex-end;
        }

        .btn-editar:hover {
            background: #138496;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        /* Estilos para status */
        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status.aprovado {
            background: #e0f7e9;
            color: #27ae60;
        }

        .status.reprovado {
            background: #fdecea;
            color: #c0392b;
        }

        .status.poss-vel-contrata-o {
            background: #fff8e1;
            color: #e67e22;
        }

        .btn-cancelar {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-cancelar:hover {
            background: #5a6268;
        }

        /* Estilos específicos para a seção de descrição */
        .descricao-container {
            position: relative;
            transition: all 0.3s ease;
        }

        .descricao-view-mode {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            line-height: 1.6;
            transition: all 0.3s;
            /* Novas propriedades para quebra de texto */
            word-wrap: break-word;      /* Quebra palavras longas */
            overflow-wrap: break-word;  /* Melhora a quebra de palavras */
            justify-content: flex-end;
        }
        .descricao-edit-mode {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px;
            border: 1px solid #ced4da;
        }

        .descricao-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-family: inherit;
            font-size: 15px;
            line-height: 1.5;
            resize: vertical;
            transition: all 0.15s;
        }

        .descricao-textarea:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }

        .descricao-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: flex-end;
        }

        .btn-salvar {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-salvar:hover {
            background: #218838;
        }

        .descricao-container:hover .descricao-view-mode {
            border-color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="detalhes-container">
        <div class="detalhes-header">
            <h1 class="detalhes-title">Detalhes do Candidato</h1>
            <span class="status <?php echo formatarStatusParaClasse($candidato['resultado']); ?>">
                <?php echo htmlspecialchars($candidato['resultado']); ?>
            </span>
        </div>

        <div class="detalhes-content">
            <div class="detalhes-group">
                <span class="detalhes-label">Nome Completo</span>
                <div class="detalhes-value"><?php echo htmlspecialchars($candidato['nome_completo']); ?></div>
            </div>

            <div class="detalhes-group">
                <span class="detalhes-label">Data de Nascimento</span>
                <div class="detalhes-value">
                    <?php 
                    if ($candidato['data_nascimento'] === NULL || $candidato['data_nascimento'] == '0000-00-00') {
                        echo 'Não informada';
                    } else {
                        echo date('d/m/Y', strtotime($candidato['data_nascimento'])); 
                    }
                    ?>
                </div>
            </div>
            <div class="detalhes-group">
                <span class="detalhes-label">Telefone</span>
                <div class="detalhes-value"><?php echo htmlspecialchars($candidato['telefone']); ?></div>
            </div>

            <div class="detalhes-group">
                <span class="detalhes-label">E-mail</span>
                <div class="detalhes-value"><?php echo htmlspecialchars($candidato['email']); ?></div>
            </div>

            <div class="detalhes-group">
                <span class="detalhes-label">Área de Interesse</span>
                <div class="detalhes-value"><?php echo htmlspecialchars($candidato['area_interesse']); ?></div>
            </div>

            <div class="detalhes-group full-width descricao-container">
                <span class="detalhes-label">Descrição</span>
                
                <?php if (!isset($_GET['editar_descricao'])): ?>
                    <div class="descricao-view-mode">
                        <?php $descricao_normalizada = str_replace(['\\r\\n', '\\n', '\\r'], "\n", $candidato['descricao']);
                        echo nl2br(htmlspecialchars($descricao_normalizada));?>
                            <div class="descricao-actions">
                                <?php if (!isset($_GET['editar_descricao'])): ?>
                                    <a href="?id=<?= $id ?>&editar_descricao=1" class="btn-editar">Editar Descrição</a>
                                <?php endif; ?>
                            </div>
                    </div>
                <?php else: ?>
                    <div class="descricao-edit-mode">
                        <form method="POST">
                            <textarea name="nova_descricao" class="descricao-textarea"><?php echo htmlspecialchars($candidato['descricao']); ?></textarea>
                            <div class="descricao-actions">
                                <button type="submit" name="editar_descricao" value="1" class="btn-salvar">
                                    Salvar Descrição
                                </button>
                                <a href="?id=<?= $id ?>" class="btn-cancelar">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detalhes-group">
                <span class="detalhes-label">Data de Cadastro</span>
                <div class="detalhes-value"><?php echo date('d/m/Y H:i', strtotime($candidato['data_cadastro'])); ?></div>
            </div>
        </div>

        <form method="POST">
            <div class="detalhes-group">
                <label for="nova_situacao" class="detalhes-label">Alterar Situação</label>
                <select name="nova_situacao" id="nova_situacao">
                    <option value="Pendente" <?= $candidato['resultado'] === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="Possível Contratação" <?= $candidato['resultado'] === 'Possível Contratação' ? 'selected' : '' ?>>Possível Contratação</option>
                    <option value="Aprovado" <?= $candidato['resultado'] === 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="Reprovado" <?= $candidato['resultado'] === 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
                </select>
                <button type="submit" class="btn-alterar">Atualizar Status</button>
            </div>

            <div class="detalhes-actions">
                <div class="btn-group">
                    <a href="index.php?area=<?= urlencode($candidato['area_interesse']) ?>" class="btn-cancelar">Voltar</a>
                </div>
                <button type="submit" name="excluir" value="1" class="btn-excluir" onclick="return confirm('Deseja realmente excluir este candidato?')">Excluir Candidato</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>