app.define("app.abm.usuarios_emisores_view", function() {
    webix.ui({
        id: 'content',
        view: "datalist",
        title: "USUARIOS EMISORES",
        form: "app.abm.usuarios_emisores_form",
        store: "usuarios_emisores",
        columns: [
            {id:"id", header:"#", sort: 'string', width: 30},
            {id:"usuario_nombre", header:["Usuario", {content:"textFilter"}], sort: 'string', fillspace: true},
            {id:"usuario_apellido", header:["Apellido", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"usuario_user", header:["Login", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"emisor_nombre", header:["Emisor", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"emisor_cuit", header:["CUIT", {content:"textFilter"}], sort: 'string', adjust: true}
        ],
        url: { "action": "usuarios_emisores-list" }
    }, $$('content'));
});

