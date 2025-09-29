app.define("app.abm.menu_form", function() {
    webix.ui({
        id: 'content',
        view: "formview",
        dataview: "app.abm.menu_view",
        update: "menu-update",
        source: {"action": "menu-row", "id": __.defAttr("menu", 0, "id")},
        store: "menu",
        title_set: __.defAttr("menu", "", "value"),
        title_add: "NUEVA VISTA DEL MENU",
        elements: {
            padding: 25,
            rows: [
                {
                    cols: [
                        {
                            "id": "usuarios_list",
                            "name": "id_tipo",
                            "view": "combo",
                            "label": "Tipo de Usuario",
                            labelPosition: "top",
                            options: __.req({action: "usuarios_tipo.combo"})
                        },
                        {width: 25},
                        {
                            "name": "vista",
                            "view": "text",
                            "label": "Vista",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "value",
                            "view": "text",
                            "label": "Nombre",
                            labelPosition: "top"
                        }
                    ]
                },
                {height: 25},
                {
                    cols: [
                        {
                            "name": "icon",
                            "view": "text",
                            "label": "Icono",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "orden",
                            "view": "text",
                            "label": "Orden",
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
