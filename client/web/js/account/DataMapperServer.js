/**
 * @fileoverview DataMapper for loading accounts from the server
 */
alib.declare("netric.account.DataMapperServer");
alib.require("netric");
/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

/**
 * Server DataMapper constructor
 *
 * @constructor
 */
netric.account.DataMapperServer = function() {
	
}

/**
 * Get account from the data store
 *
 * @return {netric.account.Account|bool} Account on sucess, false if not found
 */
netric.account.DataMapperServer.prototype.load = function(cbLoadedFunction) {

}

/**
 * Save account to the data store
 *
 * @param {netric.account.Account} acct The account to save data for
 * @reutrn {bool} True on success, false on failure
 */
netric.account.DataMapperServer.prototype.save = function(cbSavedFunction) {
	//  TODO: save the actt
}