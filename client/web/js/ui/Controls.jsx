/**
 * The purpose of this is the abstract out any common UI controls libraries
 *

 */
'use strict';
var React = require('react');
var Chamel = require('chamel');

/**
 * We do this to make it easier to switch out UI frameworks.
 *
 * Right now we use chamel, which is just a modified version of material-ui
 * but later we may just want to render our own controls (button, tab, etc)
 */
var UiControls = {
  AppBar: Chamel.AppBar,
  AutoComplete: Chamel.AutoComplete,
  Checkbox: Chamel.Checkbox,
  DatePicker: Chamel.DatePicker,
  Dialog: Chamel.Dialog,
  DropDownIcon: Chamel.DropDownIcon,
  DropDownMenu: Chamel.DropDownMenu,
  Editor: Chamel.Editor,
  FlatButton: Chamel.FlatButton,
  //FloatingActionButton: require('./floating-action-button'),
  FontIcon: Chamel.FontIcon,
  IconButton: Chamel.IconButton,
  //Input: require('./Input'),
  LeftNav: Chamel.LeftNav,
  Menu: Chamel.Menu,
  MenuItem: Chamel.MenuItem,
  Paper: Chamel.Paper,
  RadioButton: Chamel.RadioButton,
  RadioButtonGroup: Chamel.RadioButtonGroup,
  RaisedButton: Chamel.RaisedButton,
  LinearProgress: Chamel.LinearProgress,
  //Slider: require('./slider'),
  SvgIcon: Chamel.SvgIcon,
  Icons: {
      NavigationMenu: Chamel.Icons.NavigationMenu,
      NavigationChevronLeft: Chamel.Icons.NavigationChevronLeft,
      NavigationChevronRight: Chamel.Icons.NavigationChevronRight,
  },
  Tab: Chamel.Tab,
  Tabs: Chamel.Tabs,
  Toggle: Chamel.Toggle,
  Snackbar: Chamel.Snackbar,
  TextField: Chamel.TextField,
  TextFieldRich: Chamel.TextFieldRich,
  Toolbar: Chamel.Toolbar,
  ToolbarGroup: Chamel.ToolbarGroup,
  //Tooltip: Chamel.AppBar,
  Utils: {
      CssEvent: Chamel.Utils.CssEvent,
      Dom: Chamel.Utils.Dom,
      Events: Chamel.Utils.Events,
      KeyCode: Chamel.Utils.KeyCode,
      KeyLine: Chamel.Utils.KeyLine,
  }
};

module.exports = UiControls;
