/**
 * @fileOverview Plugin model that will handle the actions for plugins
 *
 *
 * @author:    Marl Tumulak, marl.tumulak@aereus.com;
 *            Copyright (c) 2016 Aereus Corporation. All rights reserved.
 */
'use strict';

/**
 * Creates an instance of Plugin.
 *
 * @constructor
 */
var Plugin = function () {
}

Plugin.List = {
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

module.exports = Plugin;