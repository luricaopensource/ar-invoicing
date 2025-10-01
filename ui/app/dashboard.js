app.define("app.dashboard",function()
{   
    // Mostrar botones de la mainbar cuando se carga el dashboard
    setTimeout(function() {
        if($$("_sec_tool_option")) $$("_sec_tool_option").show();
        if($$("_main_tool_option")) $$("_main_tool_option").show();
    }, 100);
    
    webix.ui
    ({
        id  : "app.page",  
        rows:
        [
            {
                id  : "app.header", 
                cols:
                [
                    { view : "mainbar", id:"app.mainbar" }
                ]
            },
            {
                id:"app.body",
                cols:
                [
                    {
                        id          : "_sidebar",
                        view        : "sidebar",  
                        borderless  : true,
                        on          :
                        {
                            onItemClick: function(id)
                            {  
                                // Verificar si es el logout
                                if(id === "logout") {
                                    webix.confirm({
                                        title: "Confirmar Salida",
                                        text: "¿Desea salir del sitio?",
                                        ok: "Sí",
                                        cancel: "No"
                                    }).then(function(result) {
                                        if(result) {
                                            // Limpiar cookies
                                            document.cookie.split(";").forEach(function(c) { 
                                                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
                                            });
                                            
                                            // Limpiar localStorage
                                            localStorage.clear();
                                            
                                            // Llamar al endpoint de logout
                                            __.GET({"action": "auth-logout"}, function(response) {
                                                // Recargar la página
                                                window.location.reload();
                                            });
                                        }
                                    });
                                    return;
                                }

                                $$("content").disable();

                                console.log(this.getItem(id));

                                app.require( this.getItem(id).vista );
                            },
                            onAfterRender: webix.once(function(){ 
                                                            
                                var _session = __.get_user();
 
  
                                if(_session == false)
                                {
                                    webix.message({type:"error", text:"Session not found"});
                                    return;
                                }

                                // Llamar a "sidebar-list" y completar el data de _sidebar
                                __.GET({"action": "sidebar-list"}, function(data) {
                                    $$("_sidebar").clearAll();
                                    $$("_sidebar").parse(data);
                                    
                                    // Agregar opción de logout al final
                                    $$("_sidebar").add({
                                        id: "logout",
                                        value: "CERRAR SESIÓN",
                                        icon: "fa fa-sign-out"
                                    });
                                });

                            }) 
                        }
                    },
                    {   id :"content"  }
                ]
            }
        ]
    }); 


    waves.clear();

    app.require("app.dashcenter", function(){

        waves.add({ css: "webix_tree_item" , effect: "waves-dark"});
        waves.add({ css: "webix_el_button" }); 
        waves.add({ css: "logospace" , effect: "waves-blue" });  
    });
});
