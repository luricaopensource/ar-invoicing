-- Revertir migración 20251004
-- Eliminar item de menu agregado
DELETE FROM `menu` WHERE `id_tipo` = 2 AND `vista` = 'app.abm.usuarios_emisores_view' AND `orden` = 6;

