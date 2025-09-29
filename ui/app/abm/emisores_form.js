app.define("app.abm.emisores_form", function() {
    webix.ui({
        id: 'content',
        view: "formview",
        dataview: "app.abm.emisores_view",
        update: "emisores-update",
        source: {"action": "emisores-row", "id": __.defAttr("emisores", 0, "id")},
        store: "emisores",
        title_set: __.defAttr("emisores", "", "nombre"),
        title_add: "NUEVO EMISOR",
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
                            "name": "afip_cuit",
                            "view": "text",
                            "label": "CUIT",
                            labelPosition: "top"
                        },
                        {width: 25},
                        {
                            "name": "afip_service",
                            "view": "text",
                            "label": "Servicio AFIP",
                            labelPosition: "top"
                        },                        
                        {width: 25},
                        {
                            "name": "afip_passphrase",
                            "view": "text",
                            "label": "Passphrase",
                            labelPosition: "top"
                        }
                    ]
                },
                {height: 25},
                {
                    cols: [
                        {
                            "name": "afip_crt",
                            "view": "textarea",
                            "label": "Certificado CRT",
                            labelPosition: "top",
                            height: 500
                        },
                        {width: 25},
                        {
                            "name": "afip_key",
                            "view": "textarea",
                            "label": "Clave KEY",
                            labelPosition: "top",
                            height: 500
                        },
                        {width: 25},
                        {
                            "name": "afip_tra",
                            "view": "textarea",
                            "label": "TRA",
                            labelPosition: "top",
                            height: 500
                        }
                    ]
                },
                {}
            ]
        }
    }, $$('content'));
});
