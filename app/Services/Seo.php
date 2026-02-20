<?php
namespace App\Services;

/**
 * Servicio de SEO
 * Genera meta tags, Open Graph, Twitter Cards, Schema.org JSON-LD
 */
class Seo
{
    private string $title;
    private string $description;
    private string $url;
    private string $image;
    private string $type;
    private string $keywords;
    private bool $noindex;
    private array $schemas = [];

    public function __construct()
    {
        $this->title = SITE_NAME;
        $this->description = SITE_DESCRIPTION;
        $this->url = url($_SERVER['REQUEST_URI'] ?? '/');
        $this->image = asset('img/og/default.jpg');
        $this->type = 'website';
        $this->keywords = '';
        $this->noindex = false;
    }

    /**
     * Configurar titulo SEO
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Formatear titulo: "Pagina — Regalos Purranque"
     */
    public static function formatTitle(string $page): string
    {
        return $page . ' — ' . SITE_NAME;
    }

    /**
     * Configurar descripcion (max 160 chars)
     */
    public function setDescription(string $desc): self
    {
        $this->description = mb_substr($desc, 0, 160);
        return $this;
    }

    /**
     * Configurar URL canonica
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Configurar imagen OG
     */
    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Configurar tipo OG
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Configurar noindex
     */
    public function setNoindex(bool $noindex = true): self
    {
        $this->noindex = $noindex;
        return $this;
    }

    /**
     * Agregar schema JSON-LD
     */
    public function addSchema(array $schema): self
    {
        $this->schemas[] = $schema;
        return $this;
    }

    /**
     * Schema.org WebSite para la home
     */
    public static function schemaWebSite(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => SITE_NAME,
            'url' => SITE_URL,
            'description' => SITE_DESCRIPTION,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => SITE_URL . '/buscar?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
            'inLanguage' => 'es-CL',
        ];
    }

    /**
     * Schema.org LocalBusiness para un comercio
     */
    public static function schemaLocalBusiness(array $comercio): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $comercio['nombre'],
            'description' => $comercio['descripcion'] ?? '',
            'url' => url('/comercio/' . $comercio['slug']),
        ];

        // Categoría principal como additionalType
        if (!empty($comercio['categorias'])) {
            foreach ($comercio['categorias'] as $cat) {
                if (!empty($cat['es_principal']) || true) {
                    $schema['additionalType'] = $cat['nombre'];
                    break;
                }
            }
        }

        $schema['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $comercio['direccion'] ?? '',
            'addressLocality' => 'Purranque',
            'addressRegion' => 'Los Lagos',
            'addressCountry' => 'CL',
        ];
        $schema['telephone'] = $comercio['telefono'] ?? null;
        $schema['image'] = !empty($comercio['portada']) ? asset('img/portadas/' . $comercio['portada']) : null;

        if (!empty($comercio['lat']) && !empty($comercio['lng'])) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $comercio['lat'],
                'longitude' => $comercio['lng'],
            ];
        }

        if (!empty($comercio['email'])) {
            $schema['email'] = $comercio['email'];
        }

        if (!empty($comercio['sitio_web'])) {
            $schema['sameAs'] = $comercio['sitio_web'];
        }

        if (!empty($comercio['calificacion_promedio'])) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $comercio['calificacion_promedio'],
                'reviewCount' => $comercio['total_resenas'],
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        return $schema;
    }

    /**
     * Schema.org Article para noticias
     */
    public static function schemaArticle(array $noticia): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $noticia['titulo'],
            'datePublished' => date('c', strtotime($noticia['fecha_publicacion'])),
            'dateModified' => date('c', strtotime($noticia['updated_at'] ?? $noticia['fecha_publicacion'])),
            'author' => [
                '@type' => 'Person',
                'name' => $noticia['autor'] ?? SITE_NAME,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => SITE_NAME,
                'url' => SITE_URL,
            ],
            'image' => !empty($noticia['imagen']) ? asset('img/noticias/' . $noticia['imagen']) : null,
            'mainEntityOfPage' => url('/noticia/' . $noticia['slug']),
            'inLanguage' => 'es-CL',
        ];
    }

    /**
     * Schema.org BreadcrumbList
     */
    public static function schemaBreadcrumbs(array $breadcrumbs): array
    {
        $items = [];
        foreach ($breadcrumbs as $i => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['label'],
                'item' => isset($crumb['url']) ? url($crumb['url']) : null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Schema.org ItemList para listados
     */
    public static function schemaItemList(array $items, string $name): array
    {
        $listItems = [];
        foreach ($items as $i => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['nombre'] ?? $item['titulo'] ?? '',
                'url' => isset($item['slug']) ? url('/comercio/' . $item['slug']) : null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($items),
            'itemListElement' => $listItems,
        ];
    }

    /**
     * Schema.org Event para fechas especiales
     */
    public static function schemaEvent(array $fecha): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $fecha['nombre'] . ' en Purranque',
            'description' => $fecha['descripcion'] ?: 'Encuentra comercios y regalos para ' . $fecha['nombre'] . ' en Purranque, Chile',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => 'Purranque',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Purranque',
                    'addressRegion' => 'Los Lagos',
                    'addressCountry' => 'CL',
                ],
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => SITE_NAME,
                'url' => SITE_URL,
            ],
        ];

        if (!empty($fecha['fecha_inicio'])) {
            $schema['startDate'] = $fecha['fecha_inicio'];
        }
        if (!empty($fecha['fecha_fin'])) {
            $schema['endDate'] = $fecha['fecha_fin'];
        }

        return $schema;
    }

    /**
     * Renderizar schemas como JSON-LD
     */
    public static function renderSchemas(array $schemas): string
    {
        $output = '';
        foreach ($schemas as $schema) {
            $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $output .= '<script type="application/ld+json">' . "\n" . $json . "\n</script>\n";
        }
        return $output;
    }
}
