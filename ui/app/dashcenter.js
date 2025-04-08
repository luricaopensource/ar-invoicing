app.define("app.dashcenter", function()
{  
    __.setTitle("Bienvenido");
    webix.ui
    ({
        id          : "content",  
        type        : "space", 
        borderless  : true,
        rows        :
        [
            {
                view    : "fieldset", 
                label   : "Emision de Facturas",
                body    :
                {
                    id  : "facturador",
                    view: "form", 
                    elements:
                    [
                        { 
                            cols: 
                            [
                                {
                                    id      : "factura.tipo",
                                    name    : "tipo",
                                    label   : "Tipo", 
                                    view    : "richselect",
                                    options : __.req({action:"tipo_factura.combo"}),
                                    width   : 250
                                },
                                {},
                                { 
                                    name        : "punto_venta",
                                    label       : "Punto de Venta",
                                    labelWidth  : 130, 
                                    view        : "richselect",
                                    options : __.req({action:"pto_vta.combo"})
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                {
                                    name    : "nro", 
                                    label   : "Factura", 
                                    view    : "text",
                                    width   : 330
                                },
                                {},
                                {
                                    name    : "concepto", 
                                    label   : "Concepto", 
                                    view    : "text"
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                {
                                    name    : "tipo_doc", 
                                    label   : "Tipo Doc.", 
                                    view    : "richselect",
                                    options : __.req({action:"tipo_doc.combo"}),
                                    width   : 250
                                },
                                {},
                                { 
                                    name    : "receptor", 
                                    label   : "Receptor", 
                                    view    : "text"
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                { 
                                    name    : "emisor", 
                                    label   : "Emisor", 
                                    view    : "text",
                                    width   : 250
                                },
                                {
                                    name    : "tipo_agente",  
                                    view    : "richselect", 
                                    width   : 75,
                                    options : __.req({action:"tipo_agente.combo"})
                                },
                                {},
                                { 
                                    name        : "importe_neto", 
                                    label       : "Importe Neto", 
                                    labelWidth  : 120, 
                                    view        : "text"
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                { 
                                    name        : "fecha_vto", 
                                    label       : "Fecha de Vto", 
                                    labelWidth  : 110, 
                                    view        : "datepicker",
                                    width       : 250
                                },
                                {},
                                { 
                                    name    : "iva", 
                                    label   : "IVA", 
                                    width   : 175,
                                    view    : "text"
                                },
                                {
                                    name    : "iva_porc",  
                                    view    : "richselect", 
                                    value   : 1,
                                    width   : 75,
                                    options : __.req({action:"iva.combo"})
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                { 
                                    name    : "total", 
                                    label   : "Total", 
                                    view    : "text" ,
                                    width   : 330
                                },
                                {},
                                { 
                                    name    : "moneda", 
                                    label   : "Moneda", 
                                    view    : "richselect", 
                                    value   : 1,
                                    options : __.req({action:"moneda.combo"})
                                }
                            ]
                        },
                        { 
                            cols: 
                            [

                                { 
                                    name    : "tyc", 
                                    label   : "T/C", 
                                    view    : "text",
                                    width   : 250
                                },
                                {},
                                { 
                                    name    : "cbu", 
                                    label   : "CBU", 
                                    view    : "text"
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                { 
                                    name    : "alias", 
                                    label   : "Alias CBU", 
                                    view    : "text",
                                    width   : 250
                                },
                                {},
                                { 
                                    name    : "cae", 
                                    label   : "CAE", 
                                    view    : "text"
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                { 
                                    name        : "orden_compra", 
                                    label       : "Orden de Compra", 
                                    view        : "text", 
                                    labelWidth  : 150,
                                    width       : 330
                                },
                                {},
                                { 
                                    name        : "cbte_asoc", 
                                    label       : "Cbte. Asociado", 
                                    view        : "text", 
                                    labelWidth  : 150
                                }
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { 
                                    name        : "pto_vta_cbte_asoc", 
                                    label       : "Pto Vta. Cbte. Asociado", 
                                    view        : "text", 
                                    labelWidth  : 175
                                }
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { 
                                    name        : "fecha_cbte_asoc", 
                                    label       : "Fecha Cbte. Asoc", 
                                    view        : "text", 
                                    labelWidth  : 150
                                }
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { 
                                    name        : "cond_iva_receptor", 
                                    label       : "Cond IVA Receptor", 
                                    view        : "text", 
                                    labelWidth  : 150
                                }
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { 
                                    width       : 160, 
                                    name        : "es_anulacion",
                                    label       : "Es de Anulacion?", 
                                    view        : "checkbox", 
                                    labelWidth  : 130
                                }
                            ]
                        },
                        {}
                    ]
                }
            },
            {css:"spacer"}
        ]
    }, $$("content")); 
 
    __.GET({action:"home.stats"}, function(response){
         
        $$("facturador").setValues(response)

    });
    
});