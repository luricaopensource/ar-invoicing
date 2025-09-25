-- Cambiar tipos de datos de fecha en comprobantes_emitidos
ALTER TABLE `comprobantes_emitidos` 
MODIFY COLUMN `fecha_proceso` DATETIME NOT NULL,
MODIFY COLUMN `fecha_cbte` DATE NOT NULL,
MODIFY COLUMN `fecha_cae` DATE NOT NULL;
