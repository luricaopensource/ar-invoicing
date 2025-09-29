app.define("app.dashboard",function()
{   
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
