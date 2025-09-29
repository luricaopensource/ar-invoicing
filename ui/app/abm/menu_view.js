app.define("app.abm.menu_view", function() {
    webix.ui({
        id: 'content',
        view: "datalist",
        title: "MENU",
        form: "app.abm.menu_form",
        store: "menu",
        columns: [
            {id:"id", header:"#", sort: 'string', width: 30},
            {id:"tipo", header:["Tipo de Usuario", {content:"selectFilter"}], sort: 'string', fillspace: true},
            {id:"vista", header:["Vista", {content:"textFilter"}], sort: 'int', adjust: true},
            {id:"value", header:["Nombre", {content:"textFilter"}], sort: 'string', adjust: true},
            {id:"icon", header:["Icono", {content:"textFilter"}], sort: 'string', 
             template: "<div style='margin-top: 5px; font-size:10px; text-align: center; color: var(--first-color);'><i class='fa fa-#icon# fa-2x'></i></div>", 
             adjust: true}
        ],
        url: { "action": "menu-list" }
    }, $$('content'));
});
