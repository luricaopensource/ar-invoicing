-- Crear tabla de relación entre usuarios y emisores
CREATE TABLE `usuarios_emisores` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_user` int(11) NOT NULL,
  `id_emisor` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar relaciones usuario-emisor
INSERT INTO `usuarios_emisores` (`id`, `id_user`, `id_emisor`) VALUES (1, 1, 1); -- lurica wsfe
INSERT INTO `usuarios_emisores` (`id`, `id_user`, `id_emisor`) VALUES (2, 1, 2); -- lurica wsfecred
