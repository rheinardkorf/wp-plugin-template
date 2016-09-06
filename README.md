# wp-plugin-template
A WordPress plugin template that forms the base for new plugins.

This repository on its own is not a complete plugin, but it is the base for creating new plugins.

The `new-wp-plugin` file is a bash script that does the following:  
* Download this repository.  
* Remove the `.git` folder and initialises a new local repo.  
* Rename a number of items in the plugin with the new plugin details.  
* Add https://github.com/xwp/wp-dev-lib as a submodule.
* Initialize `dev-lib` by setting up pre-commit hooks and linking/copying various files.  
* Install ESLint (*NOTE:* You need npm installed)  
* Creates your first commit.  

## new-wp-plugin usage  

At its simplest you can create new plugin by calling `sh new-wp-plugin "My Awesome Plugin"`.  
This will setup the plugin by using the plugin name in various forms: MyAwesomePlugin, my-awesome-plugin, my_awesome_plugin for 
things like namespaces, action/filter prefixes, package names, etc. Your plugin's file will also be renamed to `my-awesome-plugin.php`.

There are various options to pass to `new-wp-plugin`:

`-ns|--namespace` - Plugin namespace  
`-pkg|--package` - Plugin package name
`-uri|--uri` - Plugin URI  
`-d|--description` - Plugin description  
`-v|--version` - Plugin version  
`-a|--author` - Plugin author  
`-auri|--author-uri` - Author URI  
`-td|--text-domain` - Plugin textdomain
`-l|--languages` - Languages path  
`-nw|--network` - Multisite Network plugin (true|false)  
`-c|--copyright-year` - Copyright year for the plugin (attributed to author)  
`-i|--interactive` - **Avoid all the flags!** Just use -i and answer prompts  
`-go|--global-object` - The global instance object for the plugin (no $)  
`-sk|--settings-key` - The option settings key for the plugin (used by the helper methods)  
`-hpre|--hook-prefix` - The prefix to add to actions and filters  
`-mslug|--menu-slug` - The slug of the admin menu for the plugin  
`-mtitle|--menu-title` - The title of the admin menu for the plugin  
`-pslug|--page-slug` - The initial page for the plugin  
`-jslug|--js-slug` - Enqueue slug for the plugin.js file
`-jobj|--js-object` - The JavaScript object created by wp_localize_script  
`-cslug|--css-slug` - The enqueue slug of plugin.css style  
`-f|--plugin-file` - The name of the plugin file (include .php)  
`-t|--template` - An alternate Git repo to use during cloning  
`-p|--path` - The destination path for the plugin. Defaults to current directory.  

The easiest option to use would be `-i` and follow the onscreen prompts. Example:  

`sh new-wp-plugin "Awesome Thing" -i`  

For extra milage, set the `new-wp-plugin` as an executable and copy to `/usr/local/bin` to use from anywhere.


