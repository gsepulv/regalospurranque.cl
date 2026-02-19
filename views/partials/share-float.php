<?php
/**
 * Floating share button - bottom right
 * Circular red/orange button that expands to show share options
 * Controlled by share_pos_floating_circle + page-level check
 */
use App\Services\RedesSociales;

// Position check
if (!RedesSociales::shouldShowShareAt('floating_circle')) {
    return;
}
// Page check (if $pageType is available from content view)
if (!empty($pageType) && !RedesSociales::shouldShowShare($pageType)) {
    return;
}

$currentUrl = url($_SERVER['REQUEST_URI'] ?? '/');
$pageTitle = $title ?? SITE_NAME;
$encodedUrl = urlencode($currentUrl);
$encodedTitle = urlencode($pageTitle);
?>
<div class="share-float" id="shareFloat">
    <button class="share-float__trigger" id="shareFloatBtn" aria-label="Compartir">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
        </svg>
    </button>
    <div class="share-float__menu" id="shareFloatMenu">
        <a href="https://wa.me/?text=<?= $encodedTitle ?>%20<?= $encodedUrl ?>" target="_blank" rel="noopener" class="share-float__item share-float__item--whatsapp" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="share-float__item share-float__item--facebook" aria-label="Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
        </a>
        <a href="https://twitter.com/intent/tweet?text=<?= $encodedTitle ?>&url=<?= $encodedUrl ?>" target="_blank" rel="noopener" class="share-float__item share-float__item--twitter" aria-label="Twitter">
            <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
        <button class="share-float__item share-float__item--copy" aria-label="Copiar enlace" onclick="copyShareLink()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
        </button>
    </div>
</div>
<script>
function copyShareLink() {
    navigator.clipboard.writeText('<?= $currentUrl ?>').then(function() {
        var btn = document.querySelector('.share-float__item--copy');
        btn.classList.add('share-float__item--copied');
        setTimeout(function() { btn.classList.remove('share-float__item--copied'); }, 2000);
    });
}
</script>
