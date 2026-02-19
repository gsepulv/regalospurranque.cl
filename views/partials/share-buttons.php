<?php
/**
 * Share Buttons (Sistema B)
 * Variables requeridas: $sharePageType (string), $sharePosition (string)
 * Opcionales: $shareUrl, $shareTitle, $shareDescription, $shareSlug
 */
$sharePageType = $sharePageType ?? '';
$sharePosition = $sharePosition ?? 'below_content';

// Dual check: page + position
if ($sharePageType && !\App\Services\RedesSociales::shouldShowShare($sharePageType)) {
    return;
}
if (!\App\Services\RedesSociales::shouldShowShareAt($sharePosition)) {
    return;
}

$activeButtons = \App\Services\RedesSociales::getActiveShareButtons();
if (empty($activeButtons)) {
    return;
}

$shareUrl    = $shareUrl ?? url($_SERVER['REQUEST_URI'] ?? '/');
$shareTitle  = $shareTitle ?? ($title ?? SITE_NAME);
$shareDesc   = $shareDescription ?? ($description ?? '');
$shareSlug   = $shareSlug ?? '';
$shareType   = $sharePageType ?: 'page';
$shareText   = \App\Services\RedesSociales::formatShareText($shareTitle, $shareDesc, $shareUrl);
$hashtags    = \App\Services\RedesSociales::get('share_hashtags', '');

$encodedUrl   = rawurlencode($shareUrl);
$encodedTitle = rawurlencode($shareTitle);
$encodedText  = rawurlencode($shareText);
$encodedDesc  = rawurlencode($shareDesc);
?>
<div class="share-buttons share-buttons--pos-<?= e($sharePosition) ?>" role="group" aria-label="Compartir">
    <span class="share-buttons__label">Compartir:</span>

    <?php if (isset($activeButtons['facebook'])): ?>
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>"
       class="share-btn share-btn--facebook"
       data-share="facebook" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       target="_blank" rel="noopener noreferrer" aria-label="Compartir en Facebook">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['twitter'])): ?>
    <a href="https://twitter.com/intent/tweet?url=<?= $encodedUrl ?>&text=<?= $encodedText ?><?= $hashtags ? '&hashtags=' . rawurlencode($hashtags) : '' ?>"
       class="share-btn share-btn--twitter"
       data-share="twitter" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       target="_blank" rel="noopener noreferrer" aria-label="Compartir en X (Twitter)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['whatsapp'])): ?>
    <a href="https://wa.me/?text=<?= $encodedText ?>%20<?= $encodedUrl ?>"
       class="share-btn share-btn--whatsapp"
       data-share="whatsapp" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       target="_blank" rel="noopener noreferrer" aria-label="Compartir por WhatsApp">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['linkedin'])): ?>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encodedUrl ?>"
       class="share-btn share-btn--linkedin"
       data-share="linkedin" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       target="_blank" rel="noopener noreferrer" aria-label="Compartir en LinkedIn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['telegram'])): ?>
    <a href="https://t.me/share/url?url=<?= $encodedUrl ?>&text=<?= $encodedText ?>"
       class="share-btn share-btn--telegram"
       data-share="telegram" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       target="_blank" rel="noopener noreferrer" aria-label="Compartir en Telegram">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['pinterest'])): ?>
    <a href="https://pinterest.com/pin/create/button/?url=<?= $encodedUrl ?>&description=<?= $encodedDesc ?>"
       class="share-btn share-btn--pinterest"
       data-share="pinterest" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       target="_blank" rel="noopener noreferrer" aria-label="Compartir en Pinterest">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12.017 24c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641 0 12.017 0z"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['email'])): ?>
    <a href="mailto:?subject=<?= $encodedTitle ?>&body=<?= $encodedText ?>%20<?= $encodedUrl ?>"
       class="share-btn share-btn--email"
       data-share="email" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
       aria-label="Compartir por email">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
    </a>
    <?php endif; ?>

    <?php if (isset($activeButtons['copy'])): ?>
    <button class="share-btn share-btn--copy"
            data-copy-url="<?= e($shareUrl) ?>"
            data-share="copy" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
            aria-label="Copiar enlace">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
    </button>
    <?php endif; ?>

    <?php if (isset($activeButtons['native']) && isset($_SERVER['HTTP_USER_AGENT'])): ?>
    <button class="share-btn share-btn--native" style="display:none"
            data-native-share
            data-share-title="<?= e($shareTitle) ?>"
            data-share-url="<?= e($shareUrl) ?>"
            data-share="native" data-share-slug="<?= e($shareSlug) ?>" data-share-type="<?= e($shareType) ?>"
            aria-label="Compartir">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
    </button>
    <?php endif; ?>
</div>
