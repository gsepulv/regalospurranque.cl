-- ============================================================================
-- Migración: Índices de rendimiento
-- Optimiza queries públicas más frecuentes
-- ============================================================================

-- Índice compuesto para filtros públicos de comercios
-- Usado en: getDestacados, getByCategoria, getByFecha, search, getParaMapa
CREATE INDEX IF NOT EXISTS idx_comercios_activo_calidad_plan
    ON comercios(activo, calidad_ok, plan_fin);

-- Índice compuesto para reseñas por comercio y estado
-- Usado en: getPromedio, getDistribucion, countByComercio
CREATE INDEX IF NOT EXISTS idx_resenas_comercio_estado
    ON resenas(comercio_id, estado);

-- Índice único para analytics_diario (permite ON DUPLICATE KEY UPDATE)
-- Si no existe, necesario para el batch insert del cron
CREATE UNIQUE INDEX IF NOT EXISTS idx_analytics_diario_fecha_pagina
    ON analytics_diario(fecha, pagina);
