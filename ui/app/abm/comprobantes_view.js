app.define("app.abm.comprobantes_view",function()
{  
    webix.ui
    ({
        id     : 'content'  ,
        view   : "datalist" ,
        title  : "COMPROBANTES EMITIDOS" ,
        columns: [ 
            {id:"nro_cbte"        , header:"Nro. Comprobante" , sort: 'int'    , adjust   : true }, 
            {id:"pto_vta"         , header:"Pto. Vta"         , sort: 'int'    , adjust   : true }, 
            {id:"tipo_cbte"       , header:"Tipo"             , sort: 'int'    , adjust   : true }, 
            {id:"cuit_receptor"   , header:"CUIT Receptor"    , sort: 'string' , adjust   : true }, 
            {id:"cae"             , header:"CAE"              , sort: 'string' , adjust   : true },
            {id:"imp_total"       , header:"Importe Total"    , sort: 'string' , adjust   : true },
            {id:"fecha_cbte"      , header:"Fecha"            , sort: 'string' , adjust   : true, format: webix.Date.dateToStr("%d/%m/%Y") },
            {id:"emisor_nombre"   , header:"Emisor"           , sort: 'string' , fillspace: true },
            {id:"created_at"      , header:"Fecha Emisión"    , sort: 'string' , adjust   : true, format: webix.Date.dateToStr("%d/%m/%Y [%H:%i]") }      
        ],
        url : { "action":"comprobantes.list" } 
    },
    $$('content'));

    
});
