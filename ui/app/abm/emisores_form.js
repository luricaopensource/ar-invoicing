app.define("app.abm.emisores_form", function() {
    var formView = webix.ui({
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
                            "id": "afip_tra_field",
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
    
    // Agregar botón PROBAR después de que se renderice el formulario
    setTimeout(function() {
        formView.addCustomButton({
            id: "emisores_test_btn",
            view: "button",
            type: "icon",
            icon: "fa fa-flask",
            align: "center",
            css: "app_button",
            borderless: true,
            disabled: false,
            width: 80,
            label: "PROBAR",
            click: function() {
                // Validar campos requeridos
                var formData = $$("emisores_form").getValues();
                
                if(!formData.afip_crt || !formData.afip_key || !formData.afip_passphrase) {
                    webix.message({type:"error", text: "¡Error! Complete los campos Certificado CRT, Clave KEY y Passphrase"});  
                    return;
                }
                
                // Deshabilitar botón durante la prueba
                $$("emisores_test_btn").disable();
                $$("emisores_test_btn").setValue("Probando...");
                
                // Enviar datos al endpoint de prueba
                __.POST({"action":"afip.emisor.test"}, {
                    afip_crt: formData.afip_crt,
                    afip_key: formData.afip_key,
                    afip_passphrase: formData.afip_passphrase
                }, function(response) {
                    
                    // Rehabilitar botón
                    $$("emisores_test_btn").enable();
                    $$("emisores_test_btn").setValue("PROBAR");
                    
                    if(response.status) {
                        // Éxito: mostrar mensaje y llenar TRA
                        webix.message({type:"success", text: response.message});
                        if(response.tra) {
                            $$("afip_tra_field").setValue(response.tra);
                        }
                    } else {
                        // Error: mostrar alerta
                        webix.alert({
                            type: "alert-error",
                            title: "Error de Conexión AFIP",
                            text: response.message
                        });
                    }
                });
            }
        });
    }, 100);
});
