app.define("app.abm.usuarios_tipo_form", function() {
    webix.ui({
        id: 'content',
        view: "formview",
        dataview: "app.abm.usuarios_tipo_view",
        update: "usuarios_tipo-update",
        source: {"action": "usuarios_tipo-row", "id": __.defAttr("usuarios_tipo", 0, "id")},
        store: "usuarios_tipo",
        title_set: __.defAttr("usuarios_tipo", "", "nombre"),
        title_add: "NUEVO TIPO DE USUARIO",
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
                            "name": "color",
                            "view": "colorpicker",
                            "label": "Color",
                            labelPosition: "top"
                        },
                        {}
                    ]
                },
                {height: 25},
                {
                    cols: [
                        {
                            "name": "dashboard",
                            "view": "text",
                            "label": "Dashboard",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "dashcenter",
                            "view": "text",
                            "label": "Dashcenter",
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
