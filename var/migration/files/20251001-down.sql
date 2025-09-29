-- Revertir migración 20251001
-- Eliminar usuario creado
DELETE FROM `usuarios` WHERE `user` = 'gov_user' AND `tipo` = 2;

-- Eliminar menús del tipo 'gov'
DELETE FROM `menu` WHERE `id_tipo` = 2;

-- Eliminar tipo de usuario 'gov'
DELETE FROM `usuarios_tipo` WHERE `nombre` = 'gov' AND `id` = 2;
