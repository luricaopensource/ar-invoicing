app.define("app.abm.account_form",function()
{   
    webix.ui
    ({
        id       : 'content',
        view     : "formview",
        dataview : "app.abm.account_view",
        update   : "account.update",
        source   : {"action": "account.row","id":  __.defAttr("accounts", 0, "id" )  }, 
        store    : "accounts",
        title_set: __.defAttr("accounts", "", "nombre" ),
        title_add: "NUEVO CLIENTE",
        elements :
        {
            padding:25,
            rows:
            [
                { template:"<b>Informacion Basica</b>", type:"section"},
                {
                    cols:
                    [
                        { view:"text", name:"nombre", label:"Nombre", inputWidth: 300, width: 400 },
                        { view:"text", name:"apellido", label:"Apellido" },
                        { view:"text", name:"mail", label:"Email" },
                    ]
                },
                { height: 20 },
                {
                    view  : "fieldset",
                    label : "Autenticación",
                    body  :
                    {
                        rows:
                        [
                            { height: 20 },
                            { view:"text",                  name:"user", label:"Usuario" , labelWidth:150 },
                            { height: 20 },
                            { view:"text", type:"password", name:"pass", label:"Password", labelWidth:150 },
                            { height: 20 }
                        ]
                    }
                },
                { height: 20 },
                { template:"<b>Facturador</b>", type:"section"},
                {
                    cols:[
                        { view:"text", name:"tel", label:"CUIT" },
                        { width: 20 },
                        { view:"text", name:"activo", label:"Activo" },
                        { width: 20 },
                        { view:"text", name:"tipo", label:"Tipo" }
                    ]
                },
                
                {}
            ]
        },
        on:
        {
            formReady: function (view) { }
        }
    },
    $$('content'));

});