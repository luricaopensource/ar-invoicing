-- Insertar nuevo registro para wsfecred copiando datos del registro 1
INSERT INTO `emisores` (`id`, `nombre`, `afip_crt`, `afip_key`, `afip_passphrase`, `afip_tra`, `afip_service`, `afip_cuit`) 
SELECT 2, 'LURICA', `afip_crt`, `afip_key`, 'cust0mer.', `afip_tra`, 'wsfecred', '33716282819'
FROM `emisores` 
WHERE `id` = 1;
