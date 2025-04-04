app.define("app.dashcenter", function()
{  
    __.setTitle("Bienvenido");
    webix.ui
    ({
        id      : "content",  
        type    : "space", 
        borderless: true,
        rows:
        [  
            { height: 24, css:"spacer"},
            {
                view:"fieldset", 
                label:"Emision de Facturas",
                body:
                {
                    rows:
                    [
                        { 
                            cols: 
                            [

                                { label:"Tipo", view:"text", value:"201 Factura A"},
                                {},
                                { label:"Punto de Venta", view:"text", value:"00002"}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Factura", view:"text", value:""},
                                {},
                                { label:"Concepto", view:"text", value:"2"}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Tipo Doc.", view:"text", value:""},
                                {},
                                { label:"Receptor", view:"text", value:"2"}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Emisor", view:"text", value:""},
                                {},
                                { label:"Importe Neto", view:"text", value:"2"}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Fecha de Vto", view:"text", value:""},
                                {},
                                { label:"IVA", view:"text", value:"2"}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Total", view:"text", value:""},
                                {},
                                { label:"Moneda", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"T/C", view:"text", value:""},
                                {},
                                { label:"CBU", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Alias CBU", view:"text", value:""},
                                {},
                                { label:"CAE", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [

                                { label:"Orden de Compra", view:"text", value:""},
                                {},
                                { label:"Cbte. Asociado", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { label:"Pto Vta. Cbte. Asociado", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { label:"Fecha Cbte. Asoc", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { label:"Cond IVA Receptor", view:"text", value:""}
                            ]
                        },
                        { 
                            cols: 
                            [ 
                                {},
                                { label:"Es de Anulacion?", view:"text", value:""}
                            ]
                        },
                        {}
                    ]
                }
            },
            {css:"spacer"}
        ]
    }, $$("content")); 
 
     
});