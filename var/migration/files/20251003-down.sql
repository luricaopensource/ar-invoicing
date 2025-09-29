-- Revertir migración 20251003
-- Eliminar items de menu agregados
DELETE FROM `menu` WHERE `id_tipo` = 2 AND `vista` = 'app.abm.usuarios_view' AND `orden` = 4;
DELETE FROM `menu` WHERE `id_tipo` = 2 AND `vista` = 'app.abm.usuarios_tipo_view' AND `orden` = 5;
