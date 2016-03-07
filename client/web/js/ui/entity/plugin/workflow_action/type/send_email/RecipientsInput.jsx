/**
 * The sub-component for SendEmail workflow action that will display the checkbox for each user
 *
 * This selector is used by workflow action dialogs to get available
 *  options to select an entity field as a variable based on the field.type/field.subtype provided
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var ReactDOM = require('react-dom');
var netric = require("../../../../../../base");
var definitionLoader = require("../../../../../../entity/definitionLoader");
var Controls = require('../../../../../Controls.jsx');
var Checkbox = Controls.Checkbox;

/**
 * Displays the checbox for each user/email field with type=object
 */
var RecipientsInput = React.createClass({

    /**
     * Expected props
     */
    propTypes: {

        /**
         * The object type where we will get the entity fields
         *
         * @type {string}
         */
        objType: React.PropTypes.string.isRequired,

        /**
         * The field that was selected
         *
         * @var {string}
         */
        selectedField: React.PropTypes.any,

        /**
         * Callback called when the user selects a field (Applicable only with checkbox)
         *
         * @var {function}
         */
        onCheck: React.PropTypes.func,
    },

    /**
     * Get the starting state of this component
     */
    getInitialState: function () {

        return {
            entityDefinition: null
        };
    },

    /**
     * We have entered the DOM
     */
    componentDidMount: function () {
        definitionLoader.get(this.props.objType, function (def) {
            this._handleEntityDefinititionLoaded(def);
        }.bind(this));
    },

    /**
     * Render the component
     */
    render: function () {

        if (!this.state.entityDefinition) {
            // Entity definition is loading still so return an empty div
            return (<div />);
        }

        let fields = this.state.entityDefinition.getFields();
        let checkboxDisplay = [];

        // Loop through fields and prepare the checkbox inputs
        for (var idx in fields) {
            let field = fields[idx];

            /*
             * We will only consider a field as an email recipient if the field has an email subtype
             *  or user subtype with field.type equals to object
             */
            if (field.subtype == "email" || (field.type == "object" && field.subtype == "user")) {
                let isChecked = false;
                let value = '<%' + field.name + '%>';

                // Make sure the selectedField is an array, and it contains the currentFieldData then we set the checkbox to checked
                if (this.props.selectedField instanceof Array && this.props.selectedField.indexOf(value) > -1) {
                    isChecked = true;
                }

                checkboxDisplay.push(
                    <Checkbox
                        key={idx}
                        value={value}
                        label={field.title}
                        onCheck={this._handleFieldCheck}
                        defaultSwitched={isChecked}/>
                );

                /*
                 * Every user can have a manager listed in a field called 'manager_id' so we add it here
                 * as an option to email the selected user's manager.
                 */
                let valueManager = '<%' + field.name + ".manager_id" + '%>';
                let isCheckedManager = false;

                // Make sure the selectedField is an array, and it contains the currentFieldData then we set the checkbox to checked
                if (this.props.selectedField instanceof Array && this.props.selectedField.indexOf(valueManager) > -1) {
                    isCheckedManager = true;
                }

                checkboxDisplay.push(
                    <Checkbox
                        key={'manager' + idx}
                        value={valueManager}
                        label={field.title + '.Manager'}
                        onCheck={this._handleFieldCheck}
                        defaultSwitched={isCheckedManager}/>
                );
            }
        }

        return (
            <div>
                {checkboxDisplay}
            </div>
        );
    },

    /**
     * Handles the clicking of checkbox when user selects a field name
     *
     * @param {DOMEvent} e Reference to the DOM event being sent
     * @param {bool} isChecked The current state of the checkbox
     *
     * @private
     */
    _handleFieldCheck: function (e, isChecked) {
        if (this.props.onCheck) {
            this.props.onCheck(e.target.value, isChecked);
        }
    },


    /**
     * Callback used when an entity definition loads (or changes)
     *
     * @param {EntityDefinition} entityDefinition The loaded definition
     */
    _handleEntityDefinititionLoaded: function (entityDefinition) {
        this.setState({entityDefinition: entityDefinition})
    }
});

module.exports = RecipientsInput;
