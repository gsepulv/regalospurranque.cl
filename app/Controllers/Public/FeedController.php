<?php
namespace App\Controllers\Public;

use App\Core\Controller;

/**
 * RSS Feed - Últimas noticias
 */
class FeedController extends Controller
{
    public function rss(): void
    {
        $noticias = $this->db->fetchAll(
            "SELECT titulo, slug, extracto, contenido, autor, fecha_publicacion, imagen
             FROM noticias
             WHERE activo = 1
             ORDER BY fecha_publicacion DESC
             LIMIT 20"
        );

        $lastBuild = !empty($noticias) ? date('r', strtotime($noticias[0]['fecha_publicacion'])) : date('r');

        header('Content-Type: application/rss+xml; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title><?= $this->xmlEscape(SITE_NAME . ' — Noticias') ?></title>
        <link><?= $this->xmlEscape(SITE_URL) ?></link>
        <description><?= $this->xmlEscape(SITE_DESCRIPTION) ?></description>
        <language>es-CL</language>
        <lastBuildDate><?= $lastBuild ?></lastBuildDate>
        <atom:link href="<?= $this->xmlEscape(url('/feed/rss.xml')) ?>" rel="self" type="application/rss+xml"/>
        <generator><?= $this->xmlEscape(SITE_NAME) ?> v<?= APP_VERSION ?></generator>
        <image>
            <url><?= $this->xmlEscape(asset('img/icons/icon-192x192.png')) ?></url>
            <title><?= $this->xmlEscape(SITE_NAME) ?></title>
            <link><?= $this->xmlEscape(SITE_URL) ?></link>
        </image>
<?php foreach ($noticias as $noticia):
    $itemUrl = url('/noticia/' . $noticia['slug']);
    $desc = !empty($noticia['extracto']) ? $noticia['extracto'] : mb_substr(strip_tags($noticia['contenido']), 0, 300);
    $pubDate = date('r', strtotime($noticia['fecha_publicacion']));
    $author = !empty($noticia['autor']) ? $noticia['autor'] : SITE_NAME;
?>
        <item>
            <title><?= $this->xmlEscape($noticia['titulo']) ?></title>
            <link><?= $this->xmlEscape($itemUrl) ?></link>
            <guid isPermaLink="true"><?= $this->xmlEscape($itemUrl) ?></guid>
            <description><![CDATA[<?= $desc ?>]]></description>
            <dc:creator><?= $this->xmlEscape($author) ?></dc:creator>
            <pubDate><?= $pubDate ?></pubDate>
<?php if (!empty($noticia['imagen'])): ?>
            <enclosure url="<?= $this->xmlEscape(asset('img/noticias/' . $noticia['imagen'])) ?>" type="image/jpeg"/>
<?php endif; ?>
        </item>
<?php endforeach; ?>
    </channel>
</rss>
<?php
        exit;
    }

    private function xmlEscape(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
