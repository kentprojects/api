/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
var loadQueue = loadQueue || [];

loadQueue.execute = function ExecuteLoader() {
	for (var i = 0; i < this.length; i++) {
		typeof this[i] === 'function' && this[i]();
	}
	this.push = function (c) {
		c();
	}
};

loadQueue.push(function OnScriptLoad() {
	/**
	 * Run generic stuff here!
	 */
});

$(document).ready(function jQueryReady() {
	loadQueue.execute();
});