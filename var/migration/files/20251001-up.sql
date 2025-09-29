-- Agregar nuevo tipo de usuario 'gov'
INSERT INTO `usuarios_tipo` 
SET 
	nombre = 'gov',
	color = '#000000',
	dashboard = 'app.dashboard',
	dashcenter = 'app.dashcenter';

-- Agregar nuevos items de menu para el tipo 'gov'
INSERT INTO `menu` 
SET 
	id_tipo = 2,
	vista = 'app.dashcenter',
	value = 'EMISION DE FACTURAS',
	icon = 'file-text-o',
	orden = 1;

INSERT INTO `menu` 
SET 
	id_tipo = 2,
	vista = 'app.abm.menu_view',
	value = 'MENU',
	icon = 'list-alt',
	orden = 2;

-- Agregar nuevo usuario del tipo 'gov'
INSERT INTO `usuarios` 
SET 
	tipo = 2,
	nombre = 'gov_user',
	apellido = 'gov_user',
	user = 'gov_user',
	pass = MD5('gov_user'),
	mail = '',
	tel = '30500010084',
	activo = 1;
