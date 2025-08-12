<?php
session_start();

// Configuración
$config = require 'config.php';

// Conexión a la base de datos
try {
    $pdo = new PDO('mysql:host=localhost;dbname=proy-bodega', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Paso 1: Redireccionar al usuario para autenticarse
if (!isset($_GET['code'])) {
    $authUrl = "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/authorize?" . http_build_query([
        'client_id' => $config['clientId'],
        'response_type' => 'code',
        'redirect_uri' => $config['redirectUri'],
        'response_mode' => 'query',
        'scope' => $config['scopes'],
    ]);

    header('Location: login.php' . $authUrl);
    exit();
}

// Paso 2: Obtener el token de acceso
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $tokenUrl = "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/token";

    $response = file_get_contents($tokenUrl, false, stream_context_create([
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query([
                'client_id' => $config['clientId'],
                'client_secret' => $config['clientSecret'],
                'code' => $code,
                'redirect_uri' => $config['redirectUri'],
                'grant_type' => 'authorization_code',
            ]),
        ],
    ]));

    $tokenResponse = json_decode($response, true);

    if (isset($tokenResponse['access_token'])) {
        $_SESSION['access_token'] = $tokenResponse['access_token'];

        // Paso 3: Obtener información del usuario
        $userResponse = file_get_contents('https://graph.microsoft.com/v1.0/me', false, stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer {$tokenResponse['access_token']}\r\n",
            ],
        ]));

        $userData = json_decode($userResponse, true);
        
        // Almacenar nombre y correo en la sesión
        $_SESSION['user_name'] = $userData['displayName'];
        $_SESSION['user_email'] = $userData['mail'] ?? $userData['userPrincipalName'];

        // Paso 4: Guardar en la base de datos
        $userName = $_SESSION['user_name'];
        $userEmail = $_SESSION['user_email'];

        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM t_usuarios WHERE CORREO_USUARIO	 = :correo");
        $stmt->execute(['correo' => $userEmail]);
        $userExists = $stmt->fetchColumn() > 0;

        if (!$userExists) {
            // Insertar el nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO t_usuarios (NOMBRE_USUARIO, CORREO_USUARIO	) VALUES (:nombre, :correo)");
            $stmt->execute(['nombre' => $userName, 'correo' => $userEmail]);
            echo "Usuario registrado exitosamente.";
        } else {
            echo "El usuario ya está registrado.";
        }

        // Redirigir a la página de inicio
        header('Location: inicio.php');
        exit();
    } else {
        echo 'Error al obtener el token';
    }
} else {
    echo 'Error: No se recibió el código de autorización.';
}
?>
