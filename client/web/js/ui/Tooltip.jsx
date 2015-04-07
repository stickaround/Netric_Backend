var React = require('react');
var Classable = require('./mixins/classable.jsx');

var Tooltip = React.createClass({

  mixins: [Classable],

  propTypes: {
    className: React.PropTypes.string,
    label: React.PropTypes.string.isRequired,
    show: React.PropTypes.bool,
    touch: React.PropTypes.bool
  },

  componentDidMount: function() {
    this._setRippleSize();
  },

  componentDidUpdate: function(prevProps, prevState) {
    this._setRippleSize();
  },

  render: function() {

    var className = this.props.className;
    var label = this.props.label;
    var classes = this.getClasses('tooltip', {
      'is-shown': this.props.show,
      'is-touch': this.props.touch
    });

    return (
      <div {...other} className={classes}>
        <div ref="ripple" className="tooltip-ripple" />
        <span className="tooltip-label">{this.props.label}</span>
      </div>
    );
  },

  _setRippleSize: function() {
    var ripple = this.refs.ripple.getDOMNode();
    var tooltipSize = this.getDOMNode().offsetWidth;
    var ripplePadding = this.props.touch ? 45 : 20;
    var rippleSize = tooltipSize + ripplePadding + 'px';

    if (this.props.show) {
      ripple.style.height = rippleSize;
      ripple.style.width = rippleSize;
    } else {
      ripple.style.width = '0px';
      ripple.style.height = '0px';
    }
  }

});

module.exports = Tooltip;