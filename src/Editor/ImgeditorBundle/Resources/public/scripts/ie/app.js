
;
var IE = window.IE = function(){
    
    
};

IE.options = {
    viewProject: {
        idTpl: "tpl-project"
    },
    viewAction: {
        idTpl: "tpl-action",
        idTplImage: "tpl-image",
    }
}

IE.init = function(options){    
    Backbone.emulateHTTP = true;
    Backbone.emulateJSON = true;    
   
    var options = $.extend({}, IE.options, options);     
    var router = new IE.Router(options);
    
    Backbone.history.start({
       pushState: true,
       root: ''
    });
    
};



IE.Registry = function(options){
    this.urls = options.urls;    
};


$.extend(IE.Registry.prototype, {
     getUrl: function(name, id_action) {
        var id_action = id_action || false;
        var url = this.urls[name];
        if (id_action) {
            url = url.replace(":id_action", id_action);
        }
        return url;
    }
});

    



IE.Action = Backbone.Model.extend({
    idAttribute: "id_action",
   
    defaults: {
        src: "",
        id_action: ""
    },
    
    initialize: function(options){
        this.urlRoot = options.urlRoot;
        
    }
    
});


IE.ViewProject = Backbone.View.extend({   
    events: {
        "change #file": "upload"
    },
    initialize: function(options) {        
        this.router = options.router;
        this.idTpl = options.idTpl;        
        this.template = _.template($("#" + this.idTpl).html()),
        _.bindAll(this, "upload", "render");
        this.render();
    },
    render: function() {
        this.$el.html(this.template({data: null}));
    },
    upload: function() {
        var self = this;
        var formData = new FormData(this.$el.find("#f-image").get(0));
        var file = this.$el.find("#file").get(0).files[0];
        formData.append("file", file);       
        $.ajax({
            url: self.router.registry.getUrl("urlProject"),
            data: formData,
            processData: false,
            contentType: false,
            method: "POST",
            success: function(data) {
                self.router.navigate(self.router.registry.getUrl("urlAction", data.id_action), {trigger: true, replace: true});
            }
        });
    }
});


IE.ViewAction = Backbone.View.extend({    
    events: {
        "click #btn-rotate": "rotate",
        "click #btn-undo": "undo",
        "click #btn-select": "select"
    },
    initialize: function(options){
        this.idTpl          = options.idTpl;
        this.idTplImage     = options.idTplImage;
        this.router         = options.router;       
    
        _.bindAll(this, "rotate", "undo", "render");
        this.template = _.template($("#" + this.idTpl).html());
        this.templateImage = _.template($("#" + this.idTplImage).html());
        this.$el.html(this.template({data: null}));
        this.model.on("change", this.render);

    },
    render: function() {
        this.$el.find("#scene").html(this.templateImage({data: this.model.attributes}));
    },
    rotate: function() {
        var self = this;
        if(this.areaSelect){
            var image = this.$el.find("img").get(0);
            $(image).imgAreaSelect({remove: true});
        }
        var url_rotate = self.router.registry.getUrl("urlRotate", this.model.id);
        $.post(url_rotate, function(data) {
            self.router.navigate(self.router.registry.getUrl("urlAction", data.id_action), {trigger: true, replace: false});
        });
    },
    
    select: function(){
        var image = this.$el.find("img").get(0);
        
        this.areaSelect = $(image).imgAreaSelect({
           handles: true,
           instance: true
        });
        
//         this.areaSelect.cancelSelection();
      
        
        
    },
    
    
    
    undo: function() {

    }
});


 IE.Router = Backbone.Router.extend({   
     
     
    
    _setAction: function(){
        var urlRoot   = this.registry.getUrl("urlFetch");
        this.action =  new IE.Action({
            urlRoot: urlRoot            
        });
    },
    
    
    _setRouteProject: function(){  
        var urlProject = this._stripFirstSlash(this.registry.getUrl("urlBase"));
        this.route(urlProject, "projectRoute");       
    },
    
    
    _setRouteAction: function(){
        var urlAction = this._stripFirstSlash(this.registry.getUrl("urlAction"));        
        this.route(urlAction, "actionRoute");     
        
    },
    
    _stripFirstSlash: function(url){
        var regex = /^\//;
        var url = url.replace(regex, '');
        return url;
    },   
     
    
    initialize: function(options){
        this.options        = options;
        this.viewAction     = null;
        this.viewProject    = null;
        
        this.registry = new IE.Registry({
            urls: options.urls
        });
        
        this._setAction();
        
        this._setRouteProject();
        
        this._setRouteAction();       
        
       
        this.viewProjectOptions = options.viewProject;
        this.viewActionOptions  = options.viewAction;
        
    },
    
    
    _getViewAction: function(){
        if(null === this.viewAction){
            var options = $.extend(this.options.viewAction, {model: this.action, router: this});
            this.viewAction = new IE.ViewAction(options);
            $("#cnt-app").append(this.viewAction.el);
        }        
    },
    
    _removeViewAction: function(){
      if(null !== this.viewAction){
          this.viewAction.remove();
      }        
    },
    
     _getViewProject: function(){
        if(null === this.viewProject){
            var options = $.extend(this.options.viewProject, {router: this});
            this.viewProject = new IE.ViewProject(options);
            $("#cnt-app").append(this.viewProject.el);
        }        
    },
    
    _removeViewProject: function(){
      if(null !== this.viewProject){
          this.viewProject.remove();
      }        
    },
    
    projectRoute: function() {       
        this._removeViewAction();
        this._getViewProject();
    },
    
    actionRoute: function(id_action) {      
        this._removeViewProject();
        this._getViewAction();      
        this.action.set("id_action", id_action, {silent: true});
        this.action.fetch();
    }
});













