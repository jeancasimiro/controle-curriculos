<?php
$conn = new mysqli("192.168.0.4", "lab", "lab", "dicweb", 3306);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $conn->real_escape_string(trim($_POST['nome']));
    $nascimento = !empty($_POST['nascimento']) ? $conn->real_escape_string(trim($_POST['nascimento'])) : NULL;
    $telefone = $conn->real_escape_string(trim($_POST['telefone']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $area = $conn->real_escape_string(trim($_POST['area']));
    $descricao = $conn->real_escape_string(trim($_POST['descricao']));
    $resultado = $conn->real_escape_string(trim($_POST['resultado']));
    
    $required_fields = ['nome', 'telefone', 'email', 'area', 'resultado'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $erro = "O campo " . ucfirst($field) . " é obrigatório.";
            break;
        }
    }
    
    if (!isset($erro)) {
        $stmt = $conn->prepare("INSERT INTO candidatos_curriculo 
                (nome_completo, data_nascimento, telefone, email, area_interesse, descricao, resultado) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssss", 
            $nome,
            $nascimento,
            $telefone,
            $email,
            $area,
            $descricao,
            $resultado
        );
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $erro = "Erro ao cadastrar: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Candidato</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-top: 0;
        }
        
        .erro {
            color: #dc3545;
            padding: 10px;
            background: #f8d7da;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }
        
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="email"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-salvar {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-salvar:hover {
            background-color: #218838;
        }
        
        .btn-cancelar {
            display: inline-block;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-left: 10px;
            font-size: 16px;
        }
        
        .btn-cancelar:hover {
            background-color: #5a6268;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#dc3545';
                        valid = false;
                    } else {
                        field.style.borderColor = '#ced4da';
                    }
                });
                
                const email = form.querySelector('[type="email"]');
                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    email.style.borderColor = '#dc3545';
                    valid = false;
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert('Por favor, preencha todos os campos obrigatórios corretamente!');
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Adicionar Candidato</h1>
        
        <?php if (isset($erro)): ?>
            <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <form method="post" accept-charset="UTF-8">
            <div class="form-group">
                <label>Nome Completo:</label>
                <input type="text" name="nome" required>
            </div>
            
            <div class="form-group">
                <label>Data de Nascimento:</label>
                <input type="date" name="nascimento">
            </div>
            
            <div class="form-group">
                <label>Telefone:</label>
                <input type="text" name="telefone" required>
            </div>
            
            <div class="form-group">
                <label>E-mail:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Área de Interesse:</label>
                <select name="area" required>
                    <option value="Técnica">Técnica</option>
                    <option value="Administrativo">Administrativo</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Descrição:</label>
                <textarea name="descricao" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Resultado:</label>
                <select name="resultado" required>
                    <option value="Pendente">Pendente</option>
                    <option value="Aprovado">Aprovado</option>
                    <option value="Reprovado">Reprovado</option>
                    <option value="Possível Contratação">Possível Contratação</option>
                </select>
            </div>
            
            <button type="submit" class="btn-salvar">Salvar</button>
            <a href="index.php" class="btn-cancelar">Cancelar</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>