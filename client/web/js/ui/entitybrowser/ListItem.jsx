/**
 * Default compressed view of an entity
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.entitybrowser.ListItem");

/**
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.entitybrowser = netric.ui.entitybrowser || {};

/**
 * Module shell
 */
netric.ui.entitybrowser.ListItem = React.createClass({

    render: function () {
        var entity = this.props.entity;
        var classes = "entity-browser-item entity-browser-item-cmp";
        if (this.props.selected) {
            classes += " selected";
        }

        return (
            <div className={classes}>
                <div className="entity-browser-item-cmp-icon">
                    <input type="checkbox" checked={this.props.selected} onChange={this.toggleSelected} />
                </div>
                <div className="entity-browser-item-cmp-body"
                    onClick={this.props.onClick}>
                    <div className="entity-browser-item-cmp-header">
                        Subject Here
                    </div>
                    <div className="entity-browser-item-cmp-subheader">
                        From Here
                    </div>
                    <div className="entity-browser-item-cmp-caption">
                        Snippet here {entity.id}
                    </div>
                </div>
            </div>
        );
    },

    /**
     * Toggle selected state
     */
    toggleSelected: function() {
        if (this.props.onSelect) {
            this.props.onSelect();
        }
    }

});
