-- Agregar nuevo item de menu para tipo 'gov'
INSERT INTO `menu` 
SET 
	id_tipo = 2,
	vista = 'app.abm.usuarios_emisores_view',
	value = 'USUARIOS EMISORES',
	icon = 'user-circle',
	orden = 6;

