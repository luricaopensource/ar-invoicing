app.define("app.abm.emisores_view", function() {
    webix.ui({
        id: 'content',
        view: "datalist",
        title: "EMISORES",
        form: "app.abm.emisores_form",
        store: "emisores",
        columns: [
            {id:"id", header:"#", sort: 'string', width: 30},
            {id:"nombre", header:["Nombre", {content:"textFilter"}], sort: 'string', fillspace: true},
            {id:"afip_cuit", header:["CUIT", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"afip_service", header:["Servicio", {content:"textFilter"}], sort: 'string', adjust: true}
        ],
        url: { "action": "emisores-list" }
    }, $$('content'));
});
