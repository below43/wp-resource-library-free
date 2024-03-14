# wp-simple-resource-library
A simple, free, resource library plugin for Wordpress

## Installation

1. Download the plugin from the GitHub repository.
2. Upload the plugin to the `/wp-content/plugins/` directory of your WordPress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

### Slug
Make sure the "resources" slug is free for this plugin to use.

### Resource posts in your theme
Copy the theme samples from this project to your theme folder (remove the "sample-" prefix).

After creating this file, WordPress should use it to display your 'resource' custom post type. If you're still having issues, make sure to flush your permalinks by going to "Settings" -> "Permalinks" and clicking "Save Changes".

Note that category.php is generic across an entire category, so if you have other custom post types, you'll need to edit the if statement.

### Resource library page
This plugin supports having a library page that will fetch the resources and display them in a table. It also supports filtering by category and includes a search form.

You can use the shortcode like this: `[display_resources_table]` or with a category filter like this: `[display_resources_table category="category-slug"]`. Replace "category-slug" with the actual slug of the category you want to filter by. 

You may want to hide some columns column on mobile

```
@media only screen and (max-width: 1023px) {
	table.wp-simple-resource-library-results .last-updated {
		display: none;
	}
}

@media only screen and (max-width: 767px) {
	table.wp-simple-resource-library-results .excerpt {
		display: none;
	}
	table.wp-simple-resource-library-results .title {
		width: 70%;
	}
	
}
```


## Changelog

### 1.0
- Initial release

## Contributing

Contributions are welcome! To contribute, fork the repository, make your changes, and submit a pull request.

## License

This project is licensed under the terms of the MIT license. For more information, see the `LICENSE` file in the project directory.