-- Eliminar el registro insertado
DELETE FROM `emisores` WHERE `id` = 2;

-- Eliminar los nuevos campos
ALTER TABLE `emisores` 
DROP COLUMN `afip_cuit`,
DROP COLUMN `afip_service`;
