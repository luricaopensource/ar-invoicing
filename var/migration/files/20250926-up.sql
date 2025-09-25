-- Agregar nuevos campos a la tabla emisores
ALTER TABLE `emisores` 
ADD COLUMN `afip_service` varchar(255) NOT NULL AFTER `afip_tra`,
ADD COLUMN `afip_cuit` varchar(255) NOT NULL AFTER `afip_service`;

-- Actualizar registro 1 con los nuevos campos
UPDATE `emisores` SET 
  `afip_service` = 'wsfe', 
  `afip_cuit` = '33716282819' 
WHERE `id` = 1;

-- Insertar nuevo registro para wsfecred
INSERT INTO `emisores` (`id`, `nombre`, `afip_crt`, `afip_key`, `afip_passphrase`, `afip_tra`, `afip_service`, `afip_cuit`) VALUES 
(2, 'LURICA', 
 (SELECT `afip_crt` FROM `emisores` WHERE `id` = 1), 
 (SELECT `afip_key` FROM `emisores` WHERE `id` = 1), 
 'cust0mer.', 
 (SELECT `afip_tra` FROM `emisores` WHERE `id` = 1), 
 'wsfecred', 
 '33716282819');
