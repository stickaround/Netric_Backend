/**
 * My first test component
 *
 * @jsx React.DOM
 */

alib.declare("netric.ui.component.test");

var netric = netric || {};
netric.ui = netric.ui || {};
netric.ui.component = netric.ui.component || {};

netric.ui.TestSubElement = React.createClass({
  render: function() {
    return (
      <p>Your name is: {this.props.name}...</p>
    );
  }
});

netric.ui.component.test = React.createClass({
  getInitialState: function() {
    return {name: this.props.name};
  },
  changeName: function(changeNameTo) {
    this.setState({name: changeNameTo});
  },
  render: function() {
    return (
      <div>
        <h1>Hello {this.state.name}!</h1>
        <netric.ui.TestSubElement name={this.state.name} />
      </div>
    );
  }
});
