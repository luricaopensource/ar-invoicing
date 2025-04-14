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
                id      : "view.facturador",
                view    : "fieldset", 
                label   : "Emision de Facturas",
                body    :
                {
                    id      : "facturador",
                    view    : "form", 
                    elements:
                    [
                        { 
                            cols: 
                            [
                                {
                                    id      : "factura.tipo",
                                    name    : "tipo",
                                    label   : "Tipo", 
                                    view    : "combo",
                                    yCount  : "3", 
                                    options : __.req({action:"tipo_factura.combo"}),
                                    width   : 330
                                },
                                {},
                                { 
                                    name        : "punto_venta",
                                    label       : "Punto de Venta",
                                    labelWidth  : 130, 
                                    view        : "richselect",
                                    options     : ["00001","00002","00003"]
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
                                    view    : "combo",
                                    options : __.req({action:"tipo_doc.combo"}),
                                    width   : 330
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
                                    options : ["ADC","SCA"]
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

    webix.extend($$("view.facturador"), webix.ProgressBar);
    $$("view.facturador").showProgress({ hide: false });

    webix.ui
    ({
        id    : "_main_tool_option" ,
        view  : "button"            ,
        type  : "icon"              ,
        label : "<b>FACTURAR</b>"   ,
        icon  : "fa fa-credit-card" ,
        align : "right"             ,
        css   : "acople"            , 
        width : 200                 ,
        click : function()
        {
            $$("view.facturador").showProgress({ hide: false }); 
            __.POST({action:"home.facturacion" }, $$("facturador").getValues(), function(response){

                if("Errors" in response){
                    webix.alert({
                        type    : "alert-error",
                        title   : "FACTURADOR",
                        text    : "<textarea style='width: 200px; height: 100px;'>"+JSON.stringify(response.Errors.Err)+"</textarea>"
                      });
                }

                if("Observaciones" in response.FeDetResp.FECAEDetResponse){
                    webix.alert({
                        type    : "alert-warning",
                        title   : "FACTURADOR",
                        text    : "<textarea style='width: 200px; height: 100px;'>"+JSON.stringify(response.FeDetResp.FECAEDetResponse.Observaciones)+"</textarea>"
                      });
                }                
                
                $$("view.facturador").showProgress({ hide: true });
            });
        }
    }, $$("_main_tool_option")); 
 
    __.GET({action:"home.stats"}, function(response){
         
        $$("facturador").setValues(response);
        $$("view.facturador").showProgress({ hide: true });

    });
    
});