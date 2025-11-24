<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");
include_once("../conexao.php");
session_start();

$input = json_decode(file_get_contents("php://input"), true);
$acao = $input["acao"] ?? ($_GET["acao"] ?? "");

switch ($acao) {
    case "verificar_email":
        $email = $input["email"] ?? "";
        $sql = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE email = ?");
        $sql->bind_param("s", $email);
        $sql->execute();
        $result = $sql->get_result()->fetch_assoc();
        echo json_encode(["existe" => $result["total"] > 0]);
        break;
        
    case "listar":
        $res = $conn->query("SELECT id, nome, email FROM usuarios");
        $dados = $res->fetch_all(MYSQLI_ASSOC);
        echo json_encode($dados);
        break;

    case "criar":
        $nome = $input["nome"] ?? "";
        $email = $input["email"] ?? "";
        $senha = password_hash($input["senha"] ?? "", PASSWORD_DEFAULT);
        $sql = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $sql->bind_param("sss", $nome, $email, $senha);
        echo $sql->execute()
            ? json_encode(["status" => "ok", "mensagem" => "Usuário cadastrado com sucesso!"])
            : json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar usuário."]);
        break;

  case "login":
    $email = $input["email"] ?? "";
    $senha = $input["senha"] ?? "";

    $sql = $conn->prepare("SELECT * FROM usuarios WHERE email=?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $res = $sql->get_result();
    $user = $res->fetch_assoc();
    if (!$user) {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Usuário não encontrado."
        ]);
        break;
    }
    if (!password_verify($senha, $user["senha"])) {
        echo json_encode([
            "status" => "erro",
            "mensagem" => "Senha incorreta."
        ]);
        break;
    }

    unset($user["senha"]); // remove senha antes de enviar ao cliente
    $_SESSION['usuario'] = $user;

    echo json_encode([
        "status" => "ok",
        "mensagem" => "Login realizado com sucesso!",
        "usuario" => $user
    ]);
    break;


    case "logout":
        // encerra a sessão do usuário
        if (session_status() === PHP_SESSION_NONE) session_start();
        // remove dados da sessão
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        echo json_encode(["status" => "ok", "mensagem" => "Logout realizado com sucesso."]);        
        break;

    case "deletar":
        $id = $input["id"] ?? 0;
        $sql = $conn->prepare("DELETE FROM usuarios WHERE id=?");
        $sql->bind_param("i", $id);
        echo $sql->execute()
            ? json_encode(["status" => "ok", "mensagem" => "Usuário excluído com sucesso!"])
            : json_encode(["status" => "erro", "mensagem" => "Erro ao excluir usuário."]);
        break;

    default:
        echo json_encode(["status" => "erro", "mensagem" => "Ação inválida."]);
        break;
}
?>