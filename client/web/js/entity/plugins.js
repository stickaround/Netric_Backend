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