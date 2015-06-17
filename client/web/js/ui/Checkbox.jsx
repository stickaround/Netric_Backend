var React = require('react');
var EnhancedSwitch = require('./EnhancedSwitch.jsx');
var Classable = require('./mixins/classable.jsx');
var CheckboxOutline = require('./svg-icons/toggle-check-box-outline-blank.jsx');
var CheckboxChecked = require('./svg-icons/toggle-check-box-checked.jsx');

var Checkbox = React.createClass({

  mixins: [Classable],

  propTypes: {
    onCheck: React.PropTypes.func,
  },

  render: function() {
    var {
      onCheck,
      ...other
    } = this.props;

    var classes = this.getClasses("checkbox");

    var checkboxElement = (
      <div>
        <CheckboxOutline className="checkbox-box" />
        <CheckboxChecked className="checkbox-check" />
      </div>
    );

    var enhancedSwitchProps = {
      ref: "enhancedSwitch",
      inputType: "checkbox",
      switchElement: checkboxElement,
      className: classes,
      iconClassName: "checkbox-icon",
      onSwitch: this._handleCheck,
      labelPosition: (this.props.labelPosition) ? this.props.labelPosition : "right"
    };

    return (
      <EnhancedSwitch 
        {...other}
        {...enhancedSwitchProps}/>
    );
  },

  isChecked: function() {
    return this.refs.enhancedSwitch.isSwitched();
  },

  setChecked: function(newCheckedValue) {
    this.refs.enhancedSwitch.setSwitched(newCheckedValue);
  },

  _handleCheck: function(e, isInputChecked) {
    if (this.props.onCheck) this.props.onCheck(e, isInputChecked);
  }
});

module.exports = Checkbox;
