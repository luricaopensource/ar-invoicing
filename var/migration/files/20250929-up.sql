-- Crear tabla para registrar comprobantes emitidos exitosamente
CREATE TABLE `comprobantes_emitidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nro_cbte` int(11) NOT NULL,
  `pto_vta` int(11) NOT NULL,
  `tipo_cbte` int(11) NOT NULL,
  `cuit_emisor` varchar(255) NOT NULL,
  `cuit_receptor` varchar(255) NOT NULL,
  `resultado` varchar(1) NOT NULL,
  `concepto` int(11) NOT NULL,
  `fecha_proceso` varchar(14) NOT NULL,
  `fecha_cbte` varchar(8) NOT NULL,
  `fecha_cae` varchar(8) NOT NULL,
  `doc_tipo` int(11) NOT NULL,
  `cae` varchar(20) NOT NULL,
  `imp_total` decimal(15,2) NOT NULL,
  `imp_neto` decimal(15,2) NOT NULL,
  `imp_iva` decimal(15,2) NOT NULL,
  `moneda` varchar(3) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_emisor` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
