<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada — <?= defined('SITE_NAME') ? SITE_NAME : 'Regalos Purranque' ?></title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f8fafc; color: #1e293b; }
        .error { text-align: center; padding: 2rem; }
        .error h1 { font-size: 6rem; margin: 0; color: #2563eb; font-weight: 800; }
        .error h2 { font-size: 1.5rem; margin: 0.5rem 0; color: #334155; }
        .error p { color: #64748b; font-size: 1.1rem; margin: 1rem 0 2rem; }
        .error a { display: inline-block; padding: 0.75rem 2rem; background: #2563eb; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background 0.2s; }
        .error a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="error">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>Lo sentimos, la página que buscas no existe o ha sido movida.</p>
        <a href="/">Volver al inicio</a>
    </div>
</body>
</html>
