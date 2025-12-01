<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include 'backend/conexao.php';

// Processar edição via AJAX
if (isset($_GET['acao']) && $_GET['acao'] === 'editar') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['id'])) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos']);
        exit;
    }
    
    $id = mysqli_real_escape_string($conn, $input['id']);
    $marca = mysqli_real_escape_string($conn, $input['marca']);
    $modelo = mysqli_real_escape_string($conn, $input['modelo']);
    $ano = mysqli_real_escape_string($conn, $input['ano']);
    $cor = mysqli_real_escape_string($conn, $input['cor']);
    $preco = mysqli_real_escape_string($conn, $input['preco']);
    $data_compra = mysqli_real_escape_string($conn, $input['data_compra']);
    $observacoes = mysqli_real_escape_string($conn, $input['observacoes']);
    
    $sql = "UPDATE carros SET 
            marca = '$marca',
            modelo = '$modelo',
            ano = '$ano',
            cor = '$cor',
            preco = '$preco',
            data_compra = '$data_compra',
            observacoes = '$observacoes'
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar registro']);
    }
    exit;
}

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id = mysqli_real_escape_string($conn, $_GET['excluir']);
    
    $sql = "DELETE FROM carros WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?msg=Carro excluído com sucesso");
    } else {
        header("Location: index.php?erro=Erro ao excluir carro");
    }
    exit();
}

// Processar inserção
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $marca = mysqli_real_escape_string($conn, $_POST['marca']);
    $modelo = mysqli_real_escape_string($conn, $_POST['modelo']);
    $ano = mysqli_real_escape_string($conn, $_POST['ano']);
    $cor = mysqli_real_escape_string($conn, $_POST['cor']);
    $preco = mysqli_real_escape_string($conn, $_POST['preco']);
    $data_compra = mysqli_real_escape_string($conn, $_POST['data_compra']);
    $observacoes = mysqli_real_escape_string($conn, $_POST['observacoes']);
    
    $sql = "INSERT INTO carros (marca, modelo, ano, cor, preco, data_compra, observacoes) 
            VALUES ('$marca', '$modelo', '$ano', '$cor', '$preco', '$data_compra', '$observacoes')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?msg=Carro registrado com sucesso");
    } else {
        header("Location: index.php?erro=Erro ao registrar carro");
    }
}

mysqli_close($conn);
?>