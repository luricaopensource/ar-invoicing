-- Agregar nuevos items de menu para tipo 'gov'
INSERT INTO `menu` 
SET 
	id_tipo = 2,
	vista = 'app.abm.usuarios_view',
	value = 'USUARIOS',
	icon = 'users',
	orden = 4;

INSERT INTO `menu` 
SET 
	id_tipo = 2,
	vista = 'app.abm.usuarios_tipo_view',
	value = 'TIPOS DE USUARIO',
	icon = 'user-plus',
	orden = 5;
