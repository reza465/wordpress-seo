/**
 * Ensures that the release or hotfix branch is checked out.
 *
 * @param {Object} grunt The grunt helper object.
 * @returns {void}
 */
module.exports = function( grunt ) {
	grunt.registerTask(
		"ensure-pre-release-branch",
		"Ensures that the release or hotfix branch is checked out",
		function() {
			// Fetch all existing branches.
			grunt.config( "gitfetch.fetchall.options.all", true );
			grunt.task.run( "gitfetch:fetchall" );

			let version = grunt.option( "plugin-version" );
			let type = grunt.option( "type" );

			// If no type is specified, default to release.
			if ( type !== "hotfix" ) {
				type = "release"
			}

			let basebranch = type === "hotfix" ? 'master' : 'trunk';

			let branchname = type + "/" + version;

			// First switch to either trunk or master to make sure we branch from the correct base branch.
			grunt.config( "gitcheckout.baseBranch.options", {
				branch: basebranch,
			} );

			grunt.task.run( "gitcheckout:baseBranch" );

			const execSync = require('child_process').execSync;
			let command = 'git branch --list ' + branchname;
			const foundBranchName = execSync( command, { encoding: 'utf-8' } );

			// If the release or hotfix branch already existed, it was saved above in foundBranchName.
			if ( foundBranchName ){
				// Checkout the release or hotfix branch.
				grunt.config( "gitcheckout.existingBranch.options", {
					branch: branchname,
				} );
				grunt.task.run( "gitcheckout:existingBranch" );

				// Pull the release or hotfix branch to make sure you have the latest commits.
				grunt.config( "gitpull.pull.options", {
					branch: branchname,
				} );
				grunt.task.run( "gitpull:pull" );
			} else {
				// If the release or hotfix branch doesn't exist yet, we need to create the branch.
				grunt.config( "gitcheckout.newBranch.options", {
					branch: branchname,
					create: true,
				} );
				grunt.task.run( "gitcheckout:newBranch" );
			}
		}
	);
};
