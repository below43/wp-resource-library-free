# wp-simple-resource-library
A simple, free, resource library plugin for Wordpress


## Setup

### Slug
Make sure the "resources" slug is free for this plugin to use.

### Resource posts in your theme
Copy the theme samples from this project to your theme folder (remove the "sample-" prefix).

This file should be placed in your active theme's directory. If you're using a child theme, place it in the child theme's directory.
By default, WordPress uses the single.php template to display a single post. However, for custom post types, you need to create a new template file in your theme's directory.

After creating this file, WordPress should use it to display your 'resource' custom post type. If you're still having issues, make sure to flush your permalinks by going to "Settings" -> "Permalinks" and clicking "Save Changes".