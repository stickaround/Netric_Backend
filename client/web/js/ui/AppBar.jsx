/**
 * Main application toolbar
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.AppBar");

/** 
 * Make sure namespace exists
 */
var netric = netric || {};
netric.ui = netric.ui || {};

/**
 * Small application component
 */
netric.ui.AppBar = React.createClass({

    propTypes: {
        onNavBtnClick: React.PropTypes.func,
        showMenuIconButton: React.PropTypes.bool,
        iconClassNameLeft: React.PropTypes.string,
        iconElementLeft: React.PropTypes.element,
        iconElementRight: React.PropTypes.element,
        title : React.PropTypes.node,
        zDepth: React.PropTypes.number,
    },

    getDefaultProps: function() {
        return {
            showMenuIconButton: true,
            title: '',
            iconClassNameLeft: 'fa fa-bars',
            zDepth: 1
        }
    },

	render: function() {

		// Set the back/menu button
		if (this.props.onNavBtnClick) {
            menuElementLeft = (
                <div className="app-bar-navigation-icon-button">
                    <i className={this.props.iconClassNameLeft} onClick={this.props.onNavBtnClick}></i>
                </div>
            );
		}

        var classes = 'mui-app-bar', title, menuElementLeft, menuElementRight;

        if (this.props.title) {
            // If the title is a string, wrap in an h1 tag.
            // If not, just use it as a node.
            title = toString.call(this.props.title) === '[object String]' ?
                <h1 className="app-bar-title">{this.props.title}</h1> :
                this.props.title;
        }

		return (
            <netric.ui.Paper rounded={false} className="app-bar" zDepth={this.props.zDepth}>
                {menuElementLeft}
                {title}
                {menuElementRight}
            </netric.ui.Paper>
		);
	}
});