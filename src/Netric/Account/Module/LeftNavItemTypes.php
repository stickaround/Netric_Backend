<?php

namespace Netric\Account\Module;

/**
 * Define all left nav item types
 *
 * NOTE 1: The textual name MUST be in UpperCase format to match
 * the component name in the client. Do not use camelCase, snake_case, or hyphen-case
 *
 * NOTE 2: All module-specific types should be
 * prefixed with the name. For exmaple, SETTINGS_ACCOUNT_BILLING
 * would be used for the 'ACCOUNT_BILLING' item of the 'Settings' module
 */
interface LeftNavItemTypes
{
    /**
     * Navigate on click
     */
    const LINK = 'Link';

    /**
     * Display an entity form
     */
    const ENTITY = 'Entity';

    /**
     * Display an entity form in a modal
     */
    const ENTITY_MODAL = 'EntityModal';

    /**
     * Display an entity browser
     */
    const ENTITY_BROWSE = 'EntityBrowse';

    /**
     * List entities as items in the leftnav
     */
    const ENTITY_BROWSE_LEFTNAV = 'EntityBrowseLeftnav';

    /**
     * Show a dashboard
     */
    const DASHBOARD = 'Dashboard';

    /**
     * Display a section header
     */
    const HEADER = 'Header';

    /**
     * Taskboard view
     */
    const TASK_BOARD = 'TaskBoard';

    /**
     * Settings module: User profile
     */
    const SETTINGS_PROFILE = 'SettingsProfile';

    /**
     * Settings module: Account billing
     */
    const SETTINGS_ACCOUNT_BILLING = 'SettingsAccountBilling';

    /**
     * Settings module: manage modules
     */
    const SETTIGS_MODULES = 'SettingsModules';

    /**
     * Settings module: manage entities
     */
    const SETTINGS_ENTITIES = 'SettingsEntities';

    /**
     * Home page feed - similar to social media home pages - but for work
     */
    const HOME_FEED = 'HomeFeed';

    /**
     * Home for work task page feed - similar to social media home pages - but for work tasks
     */
    const WORK_HOME = 'WorkHome';
}
