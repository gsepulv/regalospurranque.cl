<?php
/**
 * Visor de perfil Instagram - Adaptativo por dispositivo
 * iPhone/PC: redirect directo al perfil
 * Android: tarjeta intermedia con deep link + copiar
 * 
 * Subir a: /public_html/v2 (raíz del sitio v2)
 * Uso: ig.php?u=duvaliahuerto_purranque
 * 
 * Este archivo es 100% independiente del framework.
 * No requiere autoload, router, ni base de datos.
 */
$user = preg_replace('/[^a-zA-Z0-9._]/', '', $_GET['u'] ?? '');
if (!$user) { header('Location: ./'); exit; }

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isAndroid = stripos($ua, 'Android') !== false;

// iPhone, iPad, Mac, PC → redirect directo (no tienen el problema)
if (!$isAndroid) {
    header('Location: https://www.instagram.com/' . $user . '/');
    exit;
}

// Android → mostrar tarjeta intermedia
?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@<?= htmlspecialchars($user) ?> en Instagram</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#fafafa;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px}
  .card{background:#fff;border-radius:20px;padding:30px;text-align:center;max-width:360px;width:100%;box-shadow:0 4px 20px rgba(0,0,0,.08)}
  .ig-logo{width:60px;height:60px;margin:0 auto 16px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border-radius:16px;display:flex;align-items:center;justify-content:center}
  .ig-logo svg{width:36px;height:36px}
  .username{font-size:1.3rem;font-weight:700;color:#262626;margin-bottom:6px}
  .subtitle{font-size:.85rem;color:#8e8e8e;margin-bottom:20px}
  .btn{display:block;width:100%;padding:14px;border-radius:12px;font-size:.95rem;font-weight:700;text-decoration:none;text-align:center;margin-bottom:10px;transition:transform .15s;border:none;cursor:pointer}
  .btn:active{transform:scale(.97)}
  .btn-ig{background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff}
  .btn-copy{background:#efefef;color:#262626}
  .btn-back{background:none;color:#8e8e8e;font-size:.8rem;font-weight:400;padding:10px;margin-top:5px}
  .copied{background:#059669!important;color:#fff!important}
  .hint{font-size:.75rem;color:#8e8e8e;margin-top:12px;line-height:1.4}
</style>
</head>
<body>
<div class="card">
  <div class="ig-logo">
    <svg viewBox="0 0 24 24" fill="white"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
  </div>
  <p class="username">@<?= htmlspecialchars($user) ?></p>
  <p class="subtitle">Perfil de Instagram</p>
  
  <a href="instagram://user?username=<?= htmlspecialchars($user) ?>" class="btn btn-ig">📱 Abrir en la App</a>
  
  <button onclick="copiarUsuario()" id="btnCopy" class="btn btn-copy">📋 Copiar @<?= htmlspecialchars($user) ?></button>
  
  <a href="javascript:history.back()" class="btn btn-back">← Volver</a>
  
  <p class="hint">Si la app no abre el perfil, copia el nombre y búscalo en Instagram 🔍</p>
</div>

<script>
function copiarUsuario() {
  var user = '<?= htmlspecialchars($user) ?>';
  if (navigator.clipboard) {
    navigator.clipboard.writeText(user).then(function() { mostrarCopiado(); });
  } else {
    var t = document.createElement('textarea');
    t.value = user;
    document.body.appendChild(t);
    t.select();
    document.execCommand('copy');
    document.body.removeChild(t);
    mostrarCopiado();
  }
}
function mostrarCopiado() {
  var btn = document.getElementById('btnCopy');
  btn.textContent = '✅ ¡Copiado!';
  btn.classList.add('copied');
  setTimeout(function() {
    btn.textContent = '📋 Copiar @<?= htmlspecialchars($user) ?>';
    btn.classList.remove('copied');
  }, 2000);
}
</script>
</body>
</html>
