app.define("app.abm.usuarios_form", function() {
    webix.ui({
        id: 'content',
        view: "formview",
        dataview: "app.abm.usuarios_view",
        update: "usuarios-update",
        source: {"action": "usuarios-row", "id": __.defAttr("usuarios", 0, "id")},
        store: "usuarios",
        title_set: __.defAttr("usuarios", "", "nombre"),
        title_add: "NUEVO USUARIO",
        elements: {
            padding: 25,
            rows: [
                {
                    cols: [
                        {
                            "name": "nombre",
                            "view": "text",
                            "label": "Nombre",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "apellido",
                            "view": "text",
                            "label": "Apellido",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "user",
                            "view": "text",
                            "label": "Usuario",
                            labelPosition: "top"
                        }
                    ]
                },
                {height: 25},
                {
                    cols: [
                        {
                            "id": "tipo_list",
                            "name": "tipo",
                            "view": "combo",
                            "label": "Tipo de Usuario",
                            labelPosition: "top",
                            options: __.req({action: "usuarios_tipo.combo"})
                        },
                        {width: 25},
                        {
                            "name": "pass",
                            "view": "text",
                            "type": "password",
                            "label": "Contraseña",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "activo",
                            "view": "checkbox",
                            "label": "Activo",
                            labelPosition: "top"
                        }
                    ]
                },
                {height: 25},
                {
                    cols: [
                        {
                            "name": "mail",
                            "view": "text",
                            "label": "Email",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "tel",
                            "view": "text",
                            "label": "Teléfono",
                            labelPosition: "top"
                        },
                        {}
                    ]
                },
                {}
            ]
        }
    }, $$('content'));
});
