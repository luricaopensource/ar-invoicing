app.define("app.abm.comprobantes_afip_view",function()
{  
    webix.ui
    ({
        id     : 'content'  ,
        view   : "datalist" ,
        title  : "COMPROBANTES AFIP (FACTURAS DE CRÉDITO)" ,
        columns: [ 
            {id:"tipo_cbte"       , header:"Tipo"             , sort: 'int'    , adjust   : true }, 
            {id:"pto_vta"         , header:"Pto. Vta"         , sort: 'int'    , adjust   : true }, 
            {id:"nro_cbte"        , header:"Nro. Comprobante" , sort: 'int'    , adjust   : true }, 
            {id:"cuit_emisor"     , header:"CUIT Emisor"      , sort: 'string' , adjust   : true }, 
            {id:"fecha_cbte"      , header:"Fecha"            , sort: 'string' , adjust   : true },
            {id:"imp_total"       , header:"Importe Total"    , sort: 'string' , adjust   : true },
            {id:"imp_neto"        , header:"Importe Neto"     , sort: 'string' , adjust   : true },
            {id:"imp_iva"         , header:"Importe IVA"      , sort: 'string' , adjust   : true },
            {id:"moneda"          , header:"Moneda"           , sort: 'string' , adjust   : true },
            {id:"cae"             , header:"CAE"              , sort: 'string' , adjust   : true },
            {id:"fecha_cae"       , header:"Fecha CAE"        , sort: 'string' , adjust   : true },
            {id:"resultado"       , header:"Resultado"        , sort: 'string' , fillspace: true }      
        ],
        url : { "action":"comprobantes.afip.list" } 
    },
    $$('content'));
});
