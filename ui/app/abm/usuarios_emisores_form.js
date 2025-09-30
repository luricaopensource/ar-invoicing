app.define("app.abm.usuarios_emisores_form", function() {
    webix.ui({
        id: 'content',
        view: "formview",
        dataview: "app.abm.usuarios_emisores_view",
        update: "usuarios_emisores-update",
        source: {"action": "usuarios_emisores-row", "id": __.defAttr("usuarios_emisores", 0, "id")},
        store: "usuarios_emisores",
        title_set: __.defAttr("usuarios_emisores", "", "usuario_nombre"),
        title_add: "NUEVA RELACIÓN USUARIO-EMISOR",
        elements: {
            padding: 25,
            rows: [
                {
                    cols: [
                        {
                            "id": "usuarios_list",
                            "name": "id_user",
                            "view": "combo",
                            "label": "Usuario",
                            labelPosition: "top",
                            options: __.req({action: "usuarios.combo"})
                        },
                        {width: 25},
                        {
                            "id": "emisores_list",
                            "name": "id_emisor",
                            "view": "combo",
                            "label": "Emisor",
                            labelPosition: "top",
                            options: __.req({action: "emisores.combo"})
                        },
                        {}
                    ]
                },
                {}
            ]
        }
    }, $$('content'));
});

