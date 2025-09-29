app.define("app.abm.usuarios_tipo_view", function() {
    webix.ui({
        id: 'content',
        view: "datalist",
        title: "TIPOS DE USUARIO",
        form: "app.abm.usuarios_tipo_form",
        store: "usuarios_tipo",
        columns: [
            {id:"id", header:"#", sort: 'string', width: 30},
            {id:"nombre", header:["Nombre", {content:"textFilter"}], sort: 'string', fillspace: true},
            {id:"color", header:["Color", {content:"textFilter"}], sort: 'string', width: 80,
             template: function(obj) {
                 return '<div style="background-color: ' + obj.color + '; width: 20px; height: 20px; border-radius: 3px; display: inline-block;"></div>';
             }},
            {id:"dashboard", header:["Dashboard", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"dashcenter", header:["Dashcenter", {content:"textFilter"}], sort: 'string', adjust: true}
        ],
        url: { "action": "usuarios_tipo-list" }
    }, $$('content'));
});
