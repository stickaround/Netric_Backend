/**
 * @fileoverview DataMapper for loading accounts from the local store
 */
alib.declare("netric.account.DataMapperLocal");

alib.require("netric");

/**
 * Make sure account namespace is initialized
 */
netric.account = netric.account || {};

/**
 * Local DataMapper constructor
 *
 * @constructor
 */
netric.account.DataMapperLocal = function() {
	
}

/**
 * Get account from the data store
 *
 * @return {netric.account.Account|bool} Account on sucess, false if not found
 */
netric.account.DataMapperLocal.prototype.load = function() {

}

/**
 * Save account to the data store
 *
 * @param {netric.account.Account} acct The account to save data for
 * @reutrn {bool} True on success, false on failure
 */
netric.account.DataMapperLocal.prototype.save = function(acct) {
	//  TODO: save the actt
}