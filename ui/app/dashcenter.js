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
                                    width   : 330,
                                    on:{
                                        onChange: function(tipo, oO){

                                            tipo = parseInt(tipo);

                                            switch(tipo){
                                                case 201: 
                                                    $$("facturbo.cbte_asoc").disable();
                                                    $$("facturbo.pto_vta_cbte_asoc").disable();
                                                    $$("facturbo.fecha_cbte_asoc").disable();
                                                    $$("facturbo.tipo_asoc").disable();
                                                break;
                                                case 202:
                                                case 203: 
                                                    $$("facturbo.cbte_asoc").enable();
                                                    $$("facturbo.pto_vta_cbte_asoc").enable();
                                                    $$("facturbo.fecha_cbte_asoc").enable();
                                                    $$("facturbo.tipo_asoc").enable();
                                                break;
                                            }


                                        }
                                    }
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
                                    id      : "facturbo.nro",
                                    name    : "nro", 
                                    label   : "Factura", 
                                    view    : "text",
                                    readonly:true,
                                    width   : 330
                                },
                                {},
                                {
                                    name    : "concepto", 
                                    label   : "Concepto", 
                                    view    : "combo",
                                    /*readonly:true,*/
                                    options : __.req({action:"tipo_concepto.combo"}),
                                    width   : 330
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
                                    readonly:true,
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
                                    readonly:true,
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
                                    id: "facturbo.importe_neto",
                                    name        : "importe_neto", 
                                    label       : "Importe Neto", 
                                    labelWidth  : 120, 
                                    view        : "text", 
                                    on: {
                                        onchange: function (importe_neto, oO){
                                            importe_neto = parseFloat(importe_neto);

                                            iva_porc = $$("facturbo.iva_combo").getText(); 
                                            iva_porc = parseFloat(iva_porc); 

                                            iva_neto = importe_neto * (iva_porc/100);

                                            $$("facturbo.iva").setValue(iva_neto); 

                                            $$("facturbo.total").setValue( importe_neto + iva_neto );
                                        }
                                    }
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
                                    id      : "facturbo.iva",
                                    name    : "iva", 
                                    label   : "IVA", 
                                    width   : 175,
                                    view    : "text",
                                    readonly:true
                                },
                                {
                                    id      : "facturbo.iva_combo",
                                    name    : "iva_porc",  
                                    view    : "richselect", 
                                    value   : 1,
                                    width   : 75,
                                    options : __.req({action:"iva.combo"}),
                                    on:{
                                        onChange: function(nN,oO, ev){

                                            importe_neto = parseFloat($$("facturbo.importe_neto").getValue());

                                            iva_porc = $$("facturbo.iva_combo").getText(); 
                                            iva_porc = parseFloat(iva_porc); 

                                            iva_neto = importe_neto * (iva_porc/100);

                                            $$("facturbo.iva").setValue(iva_neto); 
                                            $$("facturbo.total").setValue( importe_neto + iva_neto );

                                        }
                                    }
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                { 
                                    id: "facturbo.total",
                                    name    : "total", 
                                    label   : "Total", 
                                    view    : "text" ,
                                    readonly:true,
                                    width   : 330
                                },
                                {},
                                { 
                                    name    : "moneda", 
                                    label   : "Moneda", 
                                    view    : "richselect", 
                                    value   : 1,
                                    options : __.req({action:"moneda.combo"}),
                                    on:{
                                        onChange: function(nN,oO){
                                            console.log(`moneda(${nN},${oO})`);
                                            if(nN == 'DOL') $$("facturbo.tyc").enable();
                                            if(nN == 'PES') $$("facturbo.tyc").disable();
                                        }
                                    }
                                }
                            ]
                        },
                        { 
                            cols: 
                            [

                                { 
                                    id      : "facturbo.tyc",
                                    name    : "tyc", 
                                    label   : "T/C", 
                                    view    : "text",
                                    disable:true,
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
                                    id      : "facturbo.cae",
                                    name    : "cae", 
                                    label   : "CAE", 
                                    view    : "text",
                                    readonly:true
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
                                    id: "facturbo.cbte_asoc",
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
                                    id: "facturbo.pto_vta_cbte_asoc",
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
                                    id: "facturbo.fecha_cbte_asoc",
                                    name        : "fecha_cbte_asoc", 
                                    label       : "Fecha Cbte. Asoc", 
                                    view        : "datepicker", 
                                    labelWidth  : 150
                                }
                            ]
                        },
                        {
                            cols: 
                            [ 
                                {},
                                {
                                    id      : "facturbo.tipo_asoc",
                                    name    : "tipo_asoc",
                                    label   : "Tipo Cbte. Asoc", 
                                    view    : "combo",
                                    yCount  : "3", 
                                    options : ["91", "88", "988", "990", "991", "993", "994", "995", "996", "997", "201"],
                                    width   : 330
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

                if(response.FeDetResp.FECAEDetResponse.Resultado == "A"){
                    $$("facturbo.cae").setValue(response.FeDetResp.FECAEDetResponse.CAE);
                    $$("facturbo.nro").setValue(response.FeDetResp.FECAEDetResponse.CbteDesde);

                    webix.message("Facturacion exitosa! CAE: " + response.FeDetResp.FECAEDetResponse.CAE);
                }

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
    });

    webix.ui
    ({
        id      : "_sec_tool_option",
        css     : "acople"          , 
        width   : 110               ,
        view    : "switch"          ,
        value   : 1                 ,
        onLabel : "INICIAR"         ,
        offLabel: "DETENER"         ,
        click   : function(id){
            __.GET({action:"afip.login"}, function(response){

                if( response.status ){
                    $$("view.facturador").showProgress({ hide: true }); 
                    $$("_main_tool_option").enable();
                    $$("_sec_tool_option").disable();
                    $$("_sec_tool_option").setValue(0);
                } 
                else{ 
                    $$("view.facturador").showProgress({ hide: false }); 
                    $$("_main_tool_option").disable();
                    $$("_sec_tool_option").setValue(1);

                    webix.alert({
                        type    : "alert-error",
                        title   : "EMISOR",
                        text    : response.message
                    });
                } 
            });
        }

    }, $$("_sec_tool_option"));  

    $$("view.facturador").showProgress({ hide: false }); 
    $$("_main_tool_option").disable();

    
});