Phingento
=========

Magento Development and Deployment Toolkit using Phing.

Features:
* Packages and deploys magento to multiple servers reliably and securely.
* Creates Symlinked development environment that exactly mirrors the deploy.
* Provides a way to share code between magento projects.
* Creates database / media snapshots from deployed environments for development.

Dependencies
------------

1. [Phing] (http://www.phing.info/trac/wiki/Users/Download)
2. [VersionControl_Git] (http://pear.php.net/package/VersionControl_Git)
3. [pecl-ssh2] (http://pecl.php.net/package/ssh2)

Project Setup
-------------

1. Copy example/build.xml to your magento project root, setting the project name.
2. Copy example/build.properties and configure your project.

Project Configuration
---------------------

The following properties in build.properties control various aspects of the development and deploy process.

* project.magento.edition: Which magento edition is this based from (enterprise|professional|community)
* project.magento.version: What version of the magento edition.  These project.magento properties determine which tar file
    is used to in the rebuild and package targets.
* project.git.remote: Git repository URL for the project, used when building the deploy package.
* project.libs: A comma separated list of directories from the shared library repository to include when rebuilding, relinking
    and deploying.
* project.libs.devlibs: A comma separated list of directories from the shared library repository to include when rebuilding, relinking
    BUT NOT when deploying.
* project.libs.branch: The branch from the shared library repository to pull shared code from.

The following properties determine how environments are set up for deployment and backup.  By default develop, alpha, beta, and live
environment targets are set up.  To add or remove environments you can edit phing/build.xml.

* deploy.username:  A user on all deployment servers which has permissions to deploy the application.  Highly recommended
 to set this to ${phing.project.name}.

* deploy.[environment].host: A comma separate list of servers to deploy to.

* deploy.[environment].password: The password for the deploy.username.  If omitted the authentication will be handled with openssh
    public/private keys. The recommended approach would be to use public / private keys.

* deploy.[environment].branch: The git branch to deploy from.

Server Setup
------------

1. Copy files in the server directory to all deployment servers.
2. Modify the backup script to include correct database connection information.

Development Targets
-----------------
* setup_apache/setup_nginx:  Sets up host file, and Apache/nginx config for the project.
    Unlike the other tasks this must be run with root permissions.
    After setting up Apache or nginx the project will be accessible from http://projectname.yourhostname.com/.  In the
    build.properies file for the project you can define project.runcodes (comma separated), and project.runtype.  These
    tell the setup-project task to create additional virtualhosts for the different websites / stores configured in Magento.
    In that case the default store will be available at http://projectname.yourhostname.com/ while the other websites will be available
    at http://projectname-runcode.yourhostname.com/.

* rebuild: Creates the Magento runtime directory and lib directory (projectname_mage, projectname_lib), applies patch files
    located in the project root, and then runs relink.  The lib directory is for code completion in your ide (add as a
    External Library in PhpStorm), while the runtime directory (projectname_mage) is the directory that actually runs your magento code.

* relink: Deletes all symlinks in the project runtime directory (projectname_mage), and then symlinks in all custom code,
    the media directory, and var/sessions into the runtime directory.

* flush_cache:  Clears the magento filesystem cache.

* development/beta/live_backup: Creates an archive with media, local.xml, and database dumps from the specified environment.

* compass_watch: Starts [compass] (http://compass-style.org/) watcher in all compass enabled skin directories.

Shared Code
-----------

To allow your various magento projects to share code you can configure a separate repository in phing/default.properties.
Subdirectories of the git repository set in project.libs.remote are overlayed into the project library and runtime directory
as well as the deployment package when the project build.properties specifies them in a comma separated list in project.libs.
Projects can also define certain directories to include in the development environment but not in the deploy by setting
a list of directories in project.devlibs. You can specify which branch from the lib repository is used by setting project.libs.branch.
