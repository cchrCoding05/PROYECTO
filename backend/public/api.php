<?php
// Configuración de CORS
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 3600');
header('Content-Type: application/json');

// Registro de todas las solicitudes para depuración
error_log("Solicitud recibida: " . $_SERVER['REQUEST_URI'] . " - Método: " . $_SERVER['REQUEST_METHOD']);

// Para peticiones OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Extraer la ruta de la API
$requestUri = $_SERVER['REQUEST_URI'];
// Simplificar la extracción de la ruta
if (strpos($requestUri, '/api/') !== false) {
    // La URL es /api/algo - extraemos la parte después de /api/
    $path = preg_replace('/^.*\/api\//', '', $requestUri);
} else if (strpos($requestUri, '/api.php/') !== false) {
    // La URL es /api.php/algo
    $path = preg_replace('/^.*\/api\.php\//', '', $requestUri);
} else if (strpos($requestUri, '/api.php') !== false) {
    // La URL es solo /api.php
    $path = '';
} else {
    // Cualquier otra cosa
    $path = $requestUri;
}

// Quitar parámetros GET de la ruta si existen
$path = strtok($path, '?');

// Registrar la ruta extraída
error_log("Ruta extraída: {$path}");

// Si no hay ruta especificada, mostrar mensaje de bienvenida
if (empty($path) || $path === '/') {
    echo json_encode(['message' => 'API de muestra para HelpEx']);
    exit();
}

// Responder según la ruta solicitada
switch (true) {
    case $path === 'user/current' || $path === 'user/current/':
        // Datos de usuario de muestra
        echo json_encode([
            'id' => 1,
            'username' => 'usuario_demo',
            'credits' => 500,
            'roles' => ['ROLE_USER'],
            'description' => 'Este es un usuario de prueba',
            'avatarUrl' => null,
            'profession' => 'Desarrollador'
        ]);
        break;
        
    case $path === 'professionals/search' || strpos($path, 'professionals/search?') === 0:
        // Datos de profesionales de muestra
        $professionals = [
            [
                'id' => 1,
                'name' => 'Mr. El Mejor fontanero del mundo',
                'profession' => 'Fontanero',
                'rating' => 5,
                'ratingCount' => 10000,
                'description' => 'Fontanero con más de 10 años de experiencia',
                'avatarUrl' => null
            ],
            [
                'id' => 2,
                'name' => 'Super Mario',
                'profession' => 'Fontanero',
                'rating' => 4,
                'ratingCount' => 8000,
                'description' => 'Especializado en tuberías y rescate de princesas',
                'avatarUrl' => null
            ]
        ];
        
        // Filtrar por consulta si se proporciona
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $query = $_GET['q'];
            $professionals = array_filter($professionals, function($professional) use ($query) {
                return stripos($professional['name'], $query) !== false || 
                       stripos($professional['profession'], $query) !== false;
            });
        }
        
        echo json_encode(array_values($professionals));
        break;
        
    case $path === 'products/search' || strpos($path, 'products/search?') === 0:
        // Datos de productos de muestra
        $products = [
            [
                'id' => 1,
                'name' => 'Bicicleta',
                'description' => 'Bicicleta en buen estado',
                'price' => 1000,
                'imageUrl' => 'https://via.placeholder.com/150',
                'seller' => [
                    'id' => 1,
                    'username' => 'Super Mario',
                    'sales' => 24
                ]
            ],
            [
                'id' => 2,
                'name' => 'Bicicleta antigua',
                'description' => 'Bicicleta clásica en perfecto estado',
                'price' => 876,
                'imageUrl' => 'https://via.placeholder.com/150',
                'seller' => [
                    'id' => 2,
                    'username' => 'Super Luigi Bros',
                    'sales' => 15
                ]
            ]
        ];
        
        // Filtrar por consulta si se proporciona
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $query = $_GET['q'];
            $products = array_filter($products, function($product) use ($query) {
                return stripos($product['name'], $query) !== false || 
                       stripos($product['description'], $query) !== false;
            });
        }
        
        echo json_encode(array_values($products));
        break;
        
    case preg_match('/products\/(\d+)/', $path, $matches):
        $id = $matches[1];
        // Datos de producto específico
        $product = [
            'id' => (int)$id,
            'name' => 'Bicicleta',
            'description' => 'Bicicleta en buen estado',
            'price' => 1000,
            'imageUrl' => 'https://via.placeholder.com/300x200',
            'seller' => [
                'id' => 1,
                'username' => 'SuperMario64',
                'sales' => 24
            ]
        ];
        
        echo json_encode($product);
        break;
        
    case $path === 'login_check' || $path === 'login_check/':
        // Simulación de inicio de sesión
        $data = json_decode(file_get_contents('php://input'), true);
        
        echo json_encode([
            'success' => true,
            'token' => 'dummy_token_1',
            'user' => [
                'id' => 1,
                'username' => isset($data['username']) ? $data['username'] : 'usuario_demo',
                'credits' => 500
            ]
        ]);
        break;
        
    case $path === 'register' || $path === 'register/':
        // Simulación de registro
        echo json_encode([
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'token' => 'dummy_token_' . rand(100, 999)
        ]);
        break;
        
    case $path === 'credits/balance' || $path === 'credits/balance/':
        // Saldo de créditos
        echo json_encode([
            'credits' => 500
        ]);
        break;
        
    default:
        // Ruta no encontrada
        http_response_code(404);
        echo json_encode([
            'error' => 'Ruta no encontrada', 
            'path' => $path,
            'request_uri' => $_SERVER['REQUEST_URI']
        ]);
        break;
} 