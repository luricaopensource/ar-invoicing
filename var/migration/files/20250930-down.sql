-- Revertir tipos de datos de fecha en comprobantes_emitidos
ALTER TABLE `comprobantes_emitidos` 
MODIFY COLUMN `fecha_proceso` varchar(14) NOT NULL,
MODIFY COLUMN `fecha_cbte` varchar(8) NOT NULL,
MODIFY COLUMN `fecha_cae` varchar(8) NOT NULL;
