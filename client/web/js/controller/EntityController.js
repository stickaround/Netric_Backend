/**
 * @fileoverview Entity viewer/editor
 */
netric.declare("netric.controller.EntityController");

netric.require("netric.controller.AbstractController");

/**
 * Controller that loads an entity browser
 */
netric.controller.EntityController = function() {
}

/**
 * Extend base controller class
 */
netric.inherits(netric.controller.EntityController, netric.controller.AbstractController);

/**
 * Handle to root ReactElement where the UI is rendered
 *
 * @private
 * @type {ReactElement}
 */
netric.controller.EntityController.prototype.rootReactNode_ = null;

/**
 * Handle to the entity definition
 *
 * @private
 * @type {ReactElement}
 */
netric.controller.EntityController.prototype.entityDefinition_ = null;

/**
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
netric.controller.EntityController.prototype.onLoad = function(opt_callback) {

    if (!this.props.objType) {
        throw "objType is a required property to load an entity";
    }

    // Get the entity definition then call the loaded callback (if set)
    netric.entity.definitionLoader.get(this.props.objType, function(def){
        this.entityDefinition_ = def;

        // Let the application router know we're all loaded
        if (opt_callback) {
            opt_callback();
        }
    }.bind(this));
}

/**
 * Render this controller into the dom tree
 */
netric.controller.EntityController.prototype.render = function() {
    // Set outer application container
    var domCon = this.domNode_;

    var data = {
        objType: this.props.objType,
        oid: this.props.oid,
        onNavBtnClick: function(evt) {
            this.close();
        }.bind(this)
    }

    switch(netric.getApplication().device.size) {
        case netric.Device.sizes.small:
            data.form = this.entityDefinition_.forms.small;
            break;
        case netric.Device.sizes.medium:
            data.form = this.entityDefinition_.forms.medium;
            break;
        case netric.Device.sizes.large:
            data.form = this.entityDefinition_.forms.large;
            break;
    }

    // Render component
    this.rootReactNode_ = React.render(
        React.createElement(netric.ui.Entity, data),
        domCon
    );
}

/**
 * Render this controller into the dom tree
 */
netric.controller.EntityController.prototype.close = function() {
    if (this.getType() == netric.controller.types.DIALOG) {
        this.unload();
    } else if (this.getParentController()) {
        var path = this.getParentController().getRoutePath();
        netric.location.go(path);
    } else {
        window.close();
    }
}