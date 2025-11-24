<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");
include_once("../conexao.php");

$input = json_decode(file_get_contents("php://input"), true);
$acao = $input["acao"] ?? ($_GET["acao"] ?? "");

switch ($acao) {
    case "listar":
        $busca = $input["busca"] ?? "";
        
        if (!empty($busca)) {
            $busca = $conn->real_escape_string($busca);
            $sql = "SELECT * FROM carros WHERE marca LIKE '%$busca%' OR modelo LIKE '%$busca%' OR cor LIKE '%$busca%' ORDER BY id DESC";
        } else {
            $sql = "SELECT * FROM carros ORDER BY id DESC";
        }
        
        $res = $conn->query($sql);
        if (!$res) {
            echo json_encode(["status" => "erro", "mensagem" => "Erro na consulta: " . $conn->error]);
            break;
        }
        
        $dados = $res->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["status" => "ok", "dados" => $dados]);
        break;

    case "criar":
        $marca = $input["marca"] ?? "";
        $modelo = $input["modelo"] ?? "";
        $ano = $input["ano"] ?? "";
        $cor = $input["cor"] ?? "";
        $preco = $input["preco"] ?? 0;
        $data_compra = $input["data_compra"] ?? date("Y-m-d");
        $observacoes = $input["observacoes"] ?? "";
        
        $sql = $conn->prepare("INSERT INTO carros (marca, modelo, ano, cor, preco, data_compra, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sql->bind_param("ssidsss", $marca, $modelo, $ano, $cor, $preco, $data_compra, $observacoes);
        echo $sql->execute()
            ? json_encode(["status" => "ok", "mensagem" => "Carro cadastrado com sucesso!", "id" => $conn->insert_id])
            : json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar carro: " . $sql->error]);
        break;

    case "atualizar":
        $id = $input["id"] ?? 0;
        $marca = $input["marca"] ?? "";
        $modelo = $input["modelo"] ?? "";
        $ano = $input["ano"] ?? "";
        $cor = $input["cor"] ?? "";
        $preco = $input["preco"] ?? 0;
        $data_compra = $input["data_compra"] ?? date("Y-m-d");
        $observacoes = $input["observacoes"] ?? "";
        
        $sql = $conn->prepare("UPDATE carros SET marca=?, modelo=?, ano=?, cor=?, preco=?, data_compra=?, observacoes=? WHERE id=?");
        $sql->bind_param("ssidssssi", $marca, $modelo, $ano, $cor, $preco, $data_compra, $observacoes, $id);
        echo $sql->execute()
            ? json_encode(["status" => "ok", "mensagem" => "Carro atualizado com sucesso!"])
            : json_encode(["status" => "erro", "mensagem" => "Erro ao atualizar carro: " . $sql->error]);
        break;

    case "deletar":
        $id = $input["id"] ?? 0;
        $sql = $conn->prepare("DELETE FROM carros WHERE id=?");
        $sql->bind_param("i", $id);
        echo $sql->execute()
            ? json_encode(["status" => "ok", "mensagem" => "Carro excluído com sucesso!"])
            : json_encode(["status" => "erro", "mensagem" => "Erro ao excluir carro: " . $sql->error]);
        break;

    default:
        echo json_encode(["status" => "erro", "mensagem" => "Ação inválida."]);
        break;
}
?>