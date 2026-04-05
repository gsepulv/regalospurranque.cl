
    /**
     * Label con icono para el estado
     */
    public static function getEstadoLabel(string $estado): string
    {
        return match ($estado) {
            'disponible' => "\u{2705} Disponible",
            'vendido'    => "\u{1F534} Vendido",
            'reservado'  => "\u{1F7E1} Reservado",
            'agotado'    => "\u{26AB} Agotado",
            default      => $estado,
        };
    }

    /**
     * Label con icono para el tipo
     */
    public static function getTipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'producto'  => "\u{1F4E6} Producto",
            'servicio'  => "\u{1F527} Servicio",
            'arriendo'  => "\u{1F3E0} Arriendo",
            'propiedad' => "\u{1F3E1} Propiedad",
            default     => $tipo,
        };
    }

    /**
     * Solo productos disponibles y activos de un comercio
     */
    public static function findDisponiblesByComercioId(int $comercioId): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM productos WHERE comercio_id = ? AND activo = 1 AND estado = 'disponible' ORDER BY orden ASC, created_at DESC",
            [$comercioId]
        );
    }

    /**
     * Actualizar solo el estado de un producto
     */
    public static function updateEstado(int $id, string $estado): int
    {
        return Database::getInstance()->update('productos', ['estado' => $estado], 'id = ?', [$id]);
    }
}
