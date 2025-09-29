-- Revertir migración de sistema de menús personalizados
-- Eliminar datos migrados
DELETE FROM `menu` WHERE `id_tipo` = 1 AND `vista` IN ('app.dashcenter', 'app.abm.comprobantes_view', 'app.abm.comprobantes_afip_view');

-- Eliminar tabla menu
DROP TABLE `menu`;
