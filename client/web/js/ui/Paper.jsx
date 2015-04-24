/**
 * Paper is a concept taken from google Material design standards
 *
 * @jsx React.DOM
 */
'use strict';
var React = require('react');

/**
 * Small application component
 */
var Paper = React.createClass({

  //mixins: [Classable],

  propTypes: {
    circle: React.PropTypes.bool,
    innerClassName: React.PropTypes.string,
    rounded: React.PropTypes.bool,
    zDepth: React.PropTypes.oneOf([0,1,2,3,4,5])
  },

  getDefaultProps: function() {
    return {
      innerClassName: '',
      rounded: true,
      zDepth: 1
    };
  },

  render: function() {

      var classes = "";

      if (this.props.className) {
        classes += this.props.className + " ";
      } 

      classes += "paper z-depth-" + this.props.zDepth;
      
      if (this.props.rounded) {
        classes += " rounded";
      }
      
      if (this.props.circle) {
        classes += " circle"; 
      }
      
      var insideClasses = 
        this.props.innerClassName + ' ' +
        'paper-container ' +
        'z-depth-bottom';

    return (
      <div {...this.props} className={classes}>
        <div ref="innerContainer" className={insideClasses}>
          {this.props.children}
        </div>
      </div>
    );
  },

  getInnerContainer: function() {
    return this.refs.innerContainer;
  }

});

module.exports = Paper;