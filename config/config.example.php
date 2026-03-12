<?php
// config/config.example.php

// No Railway, estas variáveis vêm do próprio servidor.
// No seu VSCode (local), você cria um config.php real com os dados do seu Postgres local.
define('DB_HOST', getenv('PGHOST') ?: 'localhost');
define('DB_PORT', getenv('PGPORT') ?: '5432');
define('DB_NAME', getenv('PGDATABASE') ?: 'assinador_db');
define('DB_USER', getenv('PGUSER') ?: 'postgres');
define('DB_PASS', getenv('PGPASSWORD') ?: 'sua_senha_local');

// Caminho absoluto para a pasta de uploads (protegida)
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
?>