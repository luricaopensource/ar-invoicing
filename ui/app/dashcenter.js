app.define("app.dashcenter", function()
{  
    __.setTitle("Bienvenido");
    
    // Mostrar botones de la mainbar cuando se regresa a dashcenter
    if($$("_sec_tool_option")) $$("_sec_tool_option").show();
    if($$("_main_tool_option")) $$("_main_tool_option").show();
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
                                    options : [],
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
                                                    $$("facturbo.orden_compra").enable();
                                                break;
                                                case 202:
                                                case 203: 
                                                    $$("facturbo.cbte_asoc").enable();
                                                    $$("facturbo.pto_vta_cbte_asoc").enable();
                                                    $$("facturbo.fecha_cbte_asoc").enable();
                                                    $$("facturbo.tipo_asoc").enable();
                                                    $$("facturbo.orden_compra").disable();
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
                                    id      : "concepto",
                                    name    : "concepto", 
                                    label   : "Concepto", 
                                    view    : "combo",
                                    /*readonly:true,*/
                                    options : [],
                                    width   : 330
                                }
                            ]
                        },
                        { 
                            cols: 
                            [
                                {
                                    id      : "tipo_doc",
                                    name    : "tipo_doc", 
                                    label   : "Tipo Doc.", 
                                    view    : "combo",
                                    options : [],
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
                                    id      : "emisor",
                                    name    : "emisor", 
                                    label   : "Emisor", 
                                    view    : "combo",
                                    options : [],
                                    width   : 300
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
                                    options : [],
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
                                    id      : "moneda",
                                    name    : "moneda", 
                                    label   : "Moneda", 
                                    view    : "richselect", 
                                    value   : 1,
                                    options : [],
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
                                    id          : "facturbo.orden_compra",
                                    name        : "orden_compra", 
                                    label       : "Referencia Comercial", 
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

    // Extender con OverlayBox y mostrar overlay inicial
    webix.extend($$("view.facturador"), webix.OverlayBox);
    $$("facturador").disable();
    $$("view.facturador").showOverlay("<div style='display: flex; align-items: center; justify-content: center; height: 100%; font-size: 16px; font-weight: bold;'>Desconectado de ARCA (AFIP)</div>");

    // Función para cargar todos los combos después de conectar con AFIP
    function loadCombos() {
        console.log("Iniciando carga de combos...");
        
        // Deshabilitar todos los combos inicialmente
        $$("factura.tipo").disable();
        $$("concepto").disable();
        $$("tipo_doc").disable();
        $$("emisor").disable();
        $$("facturbo.iva_combo").disable();
        $$("moneda").disable();
        
        // Cargar tipo de factura
        __.GET({action:"tipo_factura.combo"}, function(data) {
            console.log("Tipo factura cargado:", data);
            $$("factura.tipo").define("options", data);
            $$("factura.tipo").refresh();
            $$("factura.tipo").enable();
            
            // Cargar tipo de concepto
            __.GET({action:"tipo_concepto.combo"}, function(data) {
                console.log("Tipo concepto cargado:", data);
                $$("concepto").define("options", data);
                $$("concepto").refresh();
                $$("concepto").enable();
                
                // Cargar tipo de documento
                __.GET({action:"tipo_doc.combo"}, function(data) {
                    console.log("Tipo doc cargado:", data);
                    $$("tipo_doc").define("options", data);
                    $$("tipo_doc").refresh();
                    $$("tipo_doc").enable();
                    
        // Cargar emisores del usuario
        __.GET({action:"emisores.usuario.combo"}, function(data) {
            console.log("Emisores del usuario cargado:", data);
            
            if(data.length === 0) {
                // No hay emisores habilitados, mantener overlay
                $$("view.facturador").showOverlay("<div style='display: flex; align-items: center; justify-content: center; height: 100%; font-size: 16px; font-weight: bold; color: orange;'>No posees emisores habilitados</div>");
                return;
            }
            
            $$("emisor").define("options", data);
            $$("emisor").refresh();
            $$("emisor").enable();
                        
                        // Cargar IVA
                        __.GET({action:"iva.combo"}, function(data) {
                            console.log("IVA cargado:", data);
                            $$("facturbo.iva_combo").define("options", data);
                            $$("facturbo.iva_combo").refresh();
                            $$("facturbo.iva_combo").enable();
                            
                            // Cargar moneda
                            __.GET({action:"moneda.combo"}, function(data) {
                                console.log("Moneda cargado:", data);
                                $$("moneda").define("options", data);
                                $$("moneda").refresh();
                                $$("moneda").enable();
                                
                                // Cargar datos iniciales del formulario
                                __.GET({action:"home.stats"}, function(response){
                                    console.log("Datos iniciales cargados:", response);
                                    $$("facturador").setValues(response);
                                    
                                    // Habilitar formulario y ocultar overlay
                                    $$("facturador").enable();
                                    $$("view.facturador").hideOverlay();
                                    console.log("Todos los combos cargados, formulario habilitado");
                                });
                            });
                        });
                    });
                });
            });
        });
    }

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
            // Deshabilitar formulario y mostrar overlay
            $$("facturador").disable();
            $$("view.facturador").showOverlay("<div style='display: flex; align-items: center; justify-content: center; height: 100%; font-size: 16px; font-weight: bold;'>Enviando factura a afip...</div>");
            
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

                // Habilitar formulario y ocultar overlay
                $$("facturador").enable();
                $$("view.facturador").hideOverlay();

            });
        }
    }, $$("_main_tool_option")); 
 
    // Los datos se cargarán solo después de conectar con AFIP exitosamente
    // __.GET({action:"home.stats"}, function(response){
    //     $$("facturador").setValues(response); 
    // });

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
            // Cambiar mensaje del overlay a "Conectando a afip..."
            $$("view.facturador").showOverlay("<div style='display: flex; align-items: center; justify-content: center; height: 100%; font-size: 16px; font-weight: bold;'>Conectando a afip...</div>");
            
            __.GET({action:"afip.login"}, function(response){

                if( response.status ){
                    console.log("AFIP login exitoso, cargando combos...");
                    $$("_main_tool_option").enable();
                    $$("_sec_tool_option").disable();
                    $$("_sec_tool_option").setValue(0);
                    
                    // Cargar todos los combos después de conectar exitosamente con AFIP
                    loadCombos();
                } 
                else{ 
                    // Mostrar overlay de error
                    $$("view.facturador").showOverlay("<div style='display: flex; align-items: center; justify-content: center; height: 100%; font-size: 16px; font-weight: bold; color: red;'>Error de conexión AFIP</div>");
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

    // Estado inicial: botón FACTURAR deshabilitado
    $$("_main_tool_option").disable();

    
});