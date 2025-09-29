app.define("app.abm.comprobantes_afip_view",function()
{  
    webix.ui
    ({
        id     : 'content'  ,
        view   : "datalist" ,
        title  : "COMPROBANTES AFIP (FACTURAS DE CRÉDITO - ULTIMOS 30 DIAS)" ,
        columns: [ 
            {id:"tipo_cbte"             , header:"Tipo"         , sort: 'int'    , adjust : true }, 
            {id:"pto_vta"               , header:"PtoVta"       , sort: 'int'    , adjust : true }, 
            {id:"nro_cbte"              , header:"NroCmp"       , sort: 'int'    , adjust : true }, 
            {id:"cuit_emisor"           , header:"Emisor"       , sort: 'string' , adjust   : true }, 
            {id:"cuit_receptor"         , header:"Receptor"     , sort: 'string' , adjust   : true },  
            {id:"fecha_cbte"            , header:"F. Emisión"   , sort: 'string' , adjust   : true, format: webix.Date.dateToStr("%d/%m/%Y") },
            {id:"imp_total"             , header:"Imp. Total"   , sort: 'string' , adjust   : true },
            {id:"imp_neto"              , header:"Imp. Neto"    , sort: 'string' , adjust   : true },
            {id:"imp_iva"               , header:"Imp. IVA"     , sort: 'string' , adjust   : true }, 
            {id:"estado"                , header:"Estado"       , sort: 'string' , adjust   : true }, 
            {id:"fecha_ven_pago"        , header:"Ven. Pago"    , sort: 'string' , adjust   : true, format: webix.Date.dateToStr("%d/%m/%Y") },
            {id:"fecha_ven_acep"        , header:"Ven. Acep"    , sort: 'string' , fillspace: true, format: webix.Date.dateToStr("%d/%m/%Y") }      
        ],
        url : { "action":"comprobantes.afip.list" } 
    },
    $$('content'));
});
