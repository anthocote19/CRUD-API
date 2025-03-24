<?php
header("Content-Type: application/json");
$pdo = new PDO("mysql:host=localhost;dbname=crud_api", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$method = $_SERVER["REQUEST_METHOD"];
$request = explode("/", trim($_SERVER["REQUEST_URI"], "/"));

if ($request[0] !== "users") {
    http_response_code(404);
    exit;
}

if ($method === "GET") {
    if (isset($request[1])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$request[1]]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($user ?: []);
    } else {
        $stmt = $pdo->query("SELECT * FROM users");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$data["name"], $data["email"], password_hash($data["password"], PASSWORD_BCRYPT)]);
    http_response_code(201);
    echo json_encode(["message" => "User created"]);
}

if ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ? WHERE email = ?");
    $stmt->execute([$data["name"], password_hash($data["password"], PASSWORD_BCRYPT), $data["email"]]);
    echo json_encode(["message" => "User updated"]);
}

if ($method === "DELETE" && isset($request[1])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$request[1]]);
    echo json_encode(["message" => "User deleted"]);
}
?>
