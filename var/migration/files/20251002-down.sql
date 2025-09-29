-- Revertir migración 20251002
-- Eliminar item de menu agregado
DELETE FROM `menu` WHERE `id_tipo` = 2 AND `vista` = 'app.abm.emisores_view' AND `orden` = 3;
