app.define("app.abm.usuarios_view", function() {
    webix.ui({
        id: 'content',
        view: "datalist",
        title: "USUARIOS",
        form: "app.abm.usuarios_form",
        store: "usuarios",
        columns: [
            {id:"id", header:"#", sort: 'string', width: 30},
            {id:"nombre", header:["Nombre", {content:"textFilter"}], sort: 'string', fillspace: true},
            {id:"apellido", header:["Apellido", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"user", header:["Usuario", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"tipo_nombre", header:["Tipo", {content:"selectFilter"}], sort: 'string', adjust: true},
            {id:"mail", header:["Email", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"activo", header:["Activo", {content:"selectFilter"}], sort: 'string', width: 60,
             template: function(obj) {
                 return obj.activo == 1 ? '<span style="color: green;">✓</span>' : '<span style="color: red;">✗</span>';
             }}
        ],
        url: { "action": "usuarios-list" }
    }, $$('content'));
});
