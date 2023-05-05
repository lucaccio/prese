
var Dialog = {
    element: null,
    title:   null,
    disableTitle: false,
    disableClose: false,
    id: null,
    el: null,
    init: function(t, e, o) {
       
       if(t == null) { 
           t = 'alert'; 
       } 
              
       switch(t) {
           case 'alert':
               this._alertDialog();
           break;
            case 'confirm':
               this._confirmDialog();
            break;
       }
       if(e == null) {
            var div  = this.defaultDiv();
            e = $(div);
       }
       this.el = e
       this._createDialog(t,e,o);
       
       
       return this;
    },
    
    /* crea un default div se non ne viene passato nessuno in init */
    defaultDiv: function() {
      return "<div id=\"dialog\" title=\"default\"><p class=\"message\"></p> </div>";      
    },
            
    open: function(txt, title) {
        //console.log(this.element[0]);
 

        e = this.element;
        $('.message').text( txt );
         
       //  this.enableTitleBar();
       if(this.disableTitle === true) {
            this.disableTitleBar();
          // $(e).parent().children(".ui-dialog-titlebar").hide();
       }
       if(title !== null) {
           this.title = title;
       }
       return e.dialog('open');
    }, 
            
    close: function() {

        e = this.element;
        e.dialog('close')
    },
    createBtn: function(t,o) {
        switch(t) {
            case 'alert':
                return {
                "Ok": function(f) {
                    if(o != null) {
                        if(o.redirect) {
                            window.location.href = o.href; 
                        }
                    }
                    e.dialog("close");
                }
            }
            break;
                
            case 'confirm':
                return {
                "Ok": function(){
                },
                "Cancel": function(){
                     e.dialog("close");
                },
            }
            break;
        }
    },
    enableTitleBar: function() {
        $(".ui-dialog-titlebar").show();
    },
    disableTitleBar: function(){
         
         
          $(this.element).parent().children(".ui-dialog-titlebar").hide();
         
       // console.log(this) 
        //var eu = $(this).find(this.id) 
         
       // var parent = $(".ui-dialog-titlebar").parent( $(this.id) ) 
        
        
       // var d = parent.children(".ui-dialog-titlebar");
         
       /// d.hide();
        
    },
    disableTitleBarClose: function() {
        $(this.element).parent().children().children(".ui-dialog-titlebar").hide();
    }, 
    _createDialog: function(t,e,o) {
       
        /* creo i bottoni in base al tipo t */ 
       var btn = this.createBtn(t, o);
       var idName = t + "_dialog_" + new Date().getMilliseconds();
       this.id = idName
       var cloneEl = e.clone().removeAttr("id").attr("id", idName) ;
       cloneEl.dialog({ 
            autoOpen: false,
            title: this.title,
            width: 400,
            resizable: false,
            modal: true,
            buttons:  btn,
            dialogClass: 'noTitleStuff' ,
            open: function() { 
            }
      });
     
      this.element = cloneEl;
    },
            
    _alertDialog: function() {
        //console.log('alert');
        this.disableClose = true;
        this.disableTitle = true;
                
    },
    _confirmDialog: function() {
        this.disableClose = true;
        this.disableTitle = false;
    }
            
    
    
    
    
    
    
}


 