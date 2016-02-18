/**
 * @fileOverview Plugins
 *
 * This contains the list of plugins that are used
 *
 * @author:    Marl Tumulak, marl.tumulak@aereus.com;
 *            Copyright (c) 2016 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Global plugins namespace
 */
var plugins = {}

plugins.List = {
    workflow: {
        Conditions: require('../ui/entity/plugin/workflow/Conditions.jsx'),
        Actions: require('../ui/entity/plugin/workflow/Actions.jsx')
    },
    workflow_action: {
        ActionDetails: require('../ui/entity/plugin/workflow_action/ActionDetails.jsx')
    },
    task: {
        LogTime: require('../ui/entity/plugin/task/LogTime.jsx')
    },
    reminder: {
        ExecuteTime: require('../ui/entity/plugin/reminder/ExecuteTime.jsx')
    },
    lead: {
        Convert: require('../ui/entity/plugin/lead/Convert.jsx')
    },
    global: {
        Members: require('../ui/entity/plugin/global/Members.jsx')
    }
}

module.exports = plugins;