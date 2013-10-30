Phingento
=========

Magento Development and Deployment Toolkit using Phing.  This can be used out of the box or modified to meet your needs.

Features:
* Packages and deploys magento to multiple servers reliably and securely.
* Provides Sass compilation and Javascript compression.
* Creates Symlinked development environment that exactly mirrors the deploy.
* Provides a way to share code between magento projects.
* Creates database / media snapshots from deployed environments for development.

Dependencies
------------

1. [Phing] (http://www.phing.info/trac/wiki/Users/Download)
2. [VersionControl_Git] (http://pear.php.net/package/VersionControl_Git)
3. [pecl-ssh2] (http://pecl.php.net/package/ssh2)
4. [n98-magerun] (https://github.com/netz98/n98-magerun)
5. [Compass] (http://compass-style.org/) (optional)
6. [Closure Compiler] (https://developers.google.com/closure/compiler/) (optional)
7. [Composer] (http://getcomposer.org/) (optional)

Workstation Setup
-----------------
To get your CentOS / RHEL 6.x development environment up install the following software as root.  This guide assumes you
already have the requirements to run magento and a JVM installed.

Install Phing and pecl-ssh2:

    yum install php-pear-phing php-pecl-ssh2

Install VersionContro_Git:

    pear install VersionControl_Git-alpha

Install n98-magerun:

    cd /usr/local/bin
    wget https://raw.github.com/netz98/n98-magerun/master/n98-magerun.phar
    chmod +x n98-magerun.phar

Install Compass (for Sass compilation):

    yum install rubygems
    gem install compass compass-normalize

Install Closure Compiler (for Javascript compression):

    cd /usr/local/lib/java
    wget http://closure-compiler.googlecode.com/files/compiler-latest.zip
    unzip compiler-latest.zip

Install Composer (for composer dependency management):

    cd /usr/local/bin
    curl -sS https://getcomposer.org/installer | php

Project Setup
-------------

1. Copy example/build.xml to your magento project root, setting the project name.
2. Copy example/build.properties and configure your project.

Project Configuration
---------------------

The following properties in build.properties control various aspects of the development and deploy process.

* __project.magento.edition:__ Which magento edition is this based from (enterprise|professional|community)

* __project.magento.version:__ What version of the magento edition.  These project.magento properties determine which tar file
    is used to in the rebuild and package targets.  You can configure where these archives live by setting magento.archive.dir.

* __project.git.remote:__ Git repository URL for the project, used when building the deploy package.

* __project.libs:__ A comma separated list of directories from the shared library repository to include when rebuilding, relinking
    and deploying.

* __project.libs.devlibs:__ A comma separated list of directories from the shared library repository to include when rebuilding, relinking
    BUT NOT when deploying.

* __project.libs.branch:__ The branch from the shared library repository to pull shared code from.

The following properties determine how environments are set up for deployment and backup.  To add or remove environments you can modify
the deploy.environments property in default.properties.

* __deploy.username:__  A user on all deployment servers which has permissions to deploy the application.  Highly recommended
 to set this to ${phing.project.name}.  Can be overridden via deploy.[environment].username.

* __deploy.[environment].host:__ A comma separate list of servers to deploy to.

* __deploy.[environment].password:__ The password for the deploy.username.  If omitted the authentication will be handled with openssh
    public/private keys. You really shouldn't store passwords in plain text.

* __deploy.[environment].branch:__ The git branch to deploy from.

* __deploy.[environment].compressjs:__ When set to true will use the google closure compiler to compress all core and skin js used
    in the front end of the site.  This will add up to 10 minutes to your build time, but reduces Javascript size by about 50%.

* __deploy.[environment].tag:__ When set to true every deploy will create and push a git tag for each deploy to the environment.

Server Setup
------------

1. Copy files in the server directory to all deployment servers.
2. Create one or more users with the same name as each project deployed on the server.

Development Targets
-----------------
* __setup_project:__  Sets up host file and Apache or nginx config for the project.
    Unlike the other tasks this must be run with root permissions.  By default will configure apache.  To change modify the
    default.properties file and set setup.webserver=nginx.
    After setting up Apache or nginx the project will be accessible from http://projectname.yourhostname.com/.  In the
    build.properties file for the project you can define project.runcodes (comma separated), and project.runtype.  These
    tell the setup-project task to create additional virtualhosts for the different websites / stores configured in Magento.
    In that case the default store will be available at http://projectname.yourhostname.com/ while the other websites will be available
    at http://projectname-runcode.yourhostname.com/.

* __rebuild:__ Creates the Magento runtime directory and lib directory (projectname_mage, projectname_lib), applies patch files
    located in the project root, and then runs relink.  The lib directory is for code completion in your ide (add as a
    External Library in PhpStorm), while the runtime directory (projectname_mage) is the directory that actually runs your magento code.

* __relink:__ Deletes all symlinks in the project runtime directory (projectname_mage), and then symlinks in all custom code,
    the media directory, and var/sessions into the runtime directory.  In the course of the development process you will
    need to run relink to see new modules, themes, skins, and locale files.

* __flush_cache:__  Clears the magento cache as configured in the local.xml.

* __backup:__ Creates an archive with media, local.xml, and database dumps from the specified environment.

* __restore_backup:__ Unpacks a backup archive and restores the database and media locally, requires a mysql user with privileges to drop
    and create databases configured in app/etc/local.xml.

* __compass_watch:__ Starts [compass] (http://compass-style.org/) watcher in all compass enabled skin directories.

Shared Code
-----------

To allow your various magento projects to share code you can configure a separate repository in phing/default.properties.
Subdirectories of the git repository set in project.libs.remote are overlayed into the project library and runtime directory
as well as the deployment package when the project build.properties specifies them in a comma separated list in project.libs.
Projects can also define certain directories to include in the development environment but not in the deploy by setting
a list of directories in project.devlibs. You can specify which branch from the lib repository is used by setting project.libs.branch.
