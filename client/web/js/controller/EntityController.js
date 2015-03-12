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
 * Function called when controller is first loaded but before the dom ready to render
 *
 * @param {function} opt_callback If set call this function when we are finished loading
 */
netric.controller.EntityController.prototype.onLoad = function(opt_callback) {

    // By default just immediately execute the callback because nothing needs to be done
    if (opt_callback)
        opt_callback();
}

/**
 * Render this controller into the dom tree
 */
netric.controller.EntityController.prototype.render = function() {
    // Set outer application container
    var domCon = this.domNode_;


    var data = {
        objType: this.props.objType,
        oid: this.props.oid
    }

    // Render component
    this.rootReactNode_ = React.render(
        React.createElement(netric.ui.Entity, data),
        domCon
    );
}