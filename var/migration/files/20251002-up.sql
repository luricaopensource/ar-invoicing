-- Agregar nuevo item de menu para tipo 'gov'
INSERT INTO `menu` 
SET 
	id_tipo = 2,
	vista = 'app.abm.emisores_view',
	value = 'EMISORES',
	icon = 'file-text-o',
	orden = 3;
