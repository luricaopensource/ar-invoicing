app.define("app.abm.account_view",function()
{  
    webix.ui
    ({
        id     : 'content'  ,
        view   : "datalist" ,
        title  : "CLIENTES" ,
        form   : "app.abm.account_form",
        store  : "accounts" ,
        columns: [ 
            {id:"nombre"    , header:"Nombre"   , sort: 'string' , adjust   : true }, 
            {id:"apellido"  , header:"Apellido" , sort: 'string' , adjust   : true }, 
            {id:"user"      , header:"Usuario"  , sort: 'string' , adjust   : true }, 
            {id:"email"     , header:"Email"    , sort: 'string' , fillspace: true }, 
            {id:"tel"       , header:"CUIT"     , sort: 'string' , adjust   : true }      
        ],
        url : { "action":"account.list" } 
    },
    $$('content'));
});