-- Crear tabla para sistema de menús personalizados por tipo de usuario
CREATE TABLE `menu` (
  `id`      int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo` int(11) NOT NULL,
  `vista`   varchar(255) NOT NULL,
  `value`   varchar(255) NOT NULL,
  `icon`    varchar(255) NOT NULL,
  `orden`   int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_tipo` (`id_tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Migrar datos existentes del menú actual para tipo de usuario 1 (admin)
INSERT INTO `menu` (`id_tipo`, `vista`, `value`, `icon`, `orden`) VALUES
(1, 'app.dashcenter', 'EMISION DE FACTURAS', 'file-text-o', 1),
(1, 'app.abm.comprobantes_view', 'COMPROBANTES EMITIDOS', 'list-alt', 2),
(1, 'app.abm.comprobantes_afip_view', 'COMPROBANTES AFIP', 'file-pdf-o', 3);
