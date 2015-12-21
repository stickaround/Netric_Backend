/**
 * Text field component
 *
 * @jsx React.DOM
 */
'use strict';

var React = require('react');
var Chamel = require('chamel');
var TextFieldComponent = Chamel.TextField;
var TextFieldRichComponent = Chamel.TextFieldRich;
var EditorComponent = Chamel.Editor;
var EntityCollection = require("../../../../entity/Collection");

/**
 * Base level element for enetity forms
 */
var TextField = React.createClass({

    /**
     * This will contain the instance of EntityCollection
     *
     * @object {entity/collection EntityCollection}
     */
    _entityCollection: null,

    /**
     * Expected props
     */
    propTypes: {
        xmlNode: React.PropTypes.object,
        entity: React.PropTypes.object,
        eventsObj: React.PropTypes.object,
        editMode: React.PropTypes.bool
    },

    componentDidMount: function () {
        if (this.refs.textFieldComponent) {
            var fieldName = this.props.xmlNode.getAttribute('name');
            var fieldValue = this.props.entity.getValue(fieldName);

            this.refs.textFieldComponent.setValue(fieldValue);
        }
    },

    render: function () {

        var xmlNode = this.props.xmlNode;
        var fieldName = xmlNode.getAttribute('name');
        var multiline = (xmlNode.getAttribute('multiline') == 't') ? true : false;
        var rich = (xmlNode.getAttribute('rich') == 't') ? true : false;

        var field = this.props.entity.def.getField(fieldName);
        var fieldValue = this.props.entity.getValue(fieldName);

        if (this.props.editMode) {
            if (rich) {
                return (
                    <EditorComponent
                        value={fieldValue}
                        onBlur={this._handleInputChange}/>
                );

            } else {

                // AutoComplete function that will transform the selected data to something else
                var funcTransform = function (data) {
                    return "[" + data.payload + ":" + data.text + "]";
                }

                var autoCompleteAttributes = {
                    autoComplete: true,
                    autoCompleteDelimiter: '',
                    autoCompleteTrigger: '@',
                    autoCompleteTransform: funcTransform,
                    autoCompleteGetData: this._getAutoCompleteData
                }

                return (
                    <TextFieldComponent
                        {... autoCompleteAttributes}
                        ref='textFieldComponent'
                        floatingLabelText={field.title}
                        multiLine={multiline}
                        onChange={this._handleInputChange}
                        />
                );

            }
        } else {

            // Display view mode text as innerhtml
            var innerHtml = this._processViewModeText(fieldValue, multiline, rich);
            return (
                <div dangerouslySetInnerHTML={innerHtml}/>
            );
        }

    },

    /**
     * Get the users data to be used in autocomplete list
     *
     * @params {string} keyword         The search keyword used to filter the user entities
     * @params {func} doneCallback      This doneCallback function is called one collection has loaded the data
     * @private
     */
    _getAutoCompleteData: function (keyword, doneCallback) {

        if (!this._entityCollection) {
            this._entityCollection = new EntityCollection('user');
        }

        this._entityCollection.clearConditions();
        this._entityCollection.where("*").equalTo(keyword);

        var collectionDoneCallback = function () {
            var entities = this._entityCollection.getEntities();

            var autoCompleteData = entities.map(function (entity) {
                return {
                    payload: entity.id,
                    text: entity.getValue('full_name')
                };
            });

            doneCallback(autoCompleteData);
        }.bind(this);

        this._entityCollection.load(collectionDoneCallback);
    },

    /**
     * Handle value change
     */
    _handleInputChange: function (evt) {
        var val = evt.target.value;
        this.props.entity.setValue(this.props.xmlNode.getAttribute('name'), val);
    },

    /**
     * Process text for view (non-edit) mode
     *
     * @param {string} val The value to process
     * @param {bool} multiline If true allow new lines
     * @param {bool} rich If true allow html/rich text
     */
    _processViewModeText: function (fieldValue, multiline, rich) {
        /*
         * Transform fieldValue for display
         */
        if (rich && fieldValue) {
            var re = new RegExp("\n", 'gi');
            fieldValue = fieldValue.replace(re, "<br />");
        }

        // Activate infocenter_document wikilinks
        if ("infocenter_document" == this.props.entity.def.objType)
            fieldValue = this._activeWikiLink(fieldValue);

        // Convert email addresses into mailto links
        fieldValue = this._activateLinks(fieldValue);

        /*
         * TODO: Make sanitized hrml object. React requires this because
         * setting innherHTML is a pretty dangerous option in that it
         * is often used for cross script exploits.
         */
        return (fieldValue) ? {__html: fieldValue} : null;
    },

    /**
     * Look for wiki links and convert them to clickable links
     *
     * @param {string} val The value to convert
     */
    _activeWikiLink: function (val) {
        var buf = val;

        if (!buf || typeof buf != "string")
            return buf;

        // Convert [[id|Title]]
        //var re=/\[\[(.*?)\|(.*?)\]\]/gi
        var re = /\[\[([^|\]]*)?\|(.*?)\]\]/gi
        buf = buf.replace(re, "<a href=\"/obj/infocenter_document/$1\" target=\"_blank\">$2</a>");

        // Convert [[id]] with id
        //var re=/\[\[(.*?)]\]/gi
        var re = /\[\[([0-9]+)]\]/gi
        buf = buf.replace(re, "<a href=\"/obj/infocenter_document/$1$1\" target=\"_blank\">$1</a>");

        // Convert [[id]] with uname
        //var re=/\[\[(.*?)]\]/gi
        var re = /\[\[([a-zA-Z0-9_-]+)]\]/gi
        buf = buf.replace(re, "<a href=\"/obj/infocenter_document/uname:$1\" target=\"_blank\">$1</a>");

        return buf;
    },

    /**
     * Look for email addresses and convert them to clickable mailto links
     *
     * @param {string} val The value to convert
     */
    _activateLinks: function (val) {
        var buf = val;

        if (!buf || typeof buf != "string")
            return buf;

        // Repalce all existing link swith target=blank
        var exp = /(^|>|\s)(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
        buf = buf.replace(/<a\s+href=/gi, '<a target="_blank" href=');

        //URLs starting with http://, https://, or ftp://
        //var exp = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
        var exp = /(^|>|\s)(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
        buf = buf.replace(exp, '<a href="$2" target="_blank">$2</a>');

        //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
        exp = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
        buf = buf.replace(exp, '$1<a href="http://$2" target="_blank">$2</a>');

        //Change email addresses to mailto:: links.
        exp = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
        var repWith = "<a href=\"javascript:Ant.Emailer.compose('$1', {obj_type:'"
            + this.props.entity.def.objType + "', oid:'" + this.props.entity.id + "'});\">$1</a>"
        buf = buf.replace(exp, repWith);
        //buf = buf.replace(exp, '<a href="mailto:$1">$1</a>');

        // Activate email addresses -- this is what we used before
        //var regEx = /(\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*)/;
        //buf = buf.replace(regEx, "<a href=\"mailto:$1\">$1</a>");

        return buf;
    }
});

module.exports = TextField;