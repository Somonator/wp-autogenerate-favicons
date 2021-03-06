# Readme
Sizes taken from: https://www.favicon-generator.org

"Autogenerate favicons" - handler for ACF Image field. He is generate favicons, manifest and browserconfig. All generated favicons saved to "generated-favicon" folder in your theme directory and will be displayed on the site.

## Usuage
```
require 'autogenerate_favicons.php';

new autogenerate_favicons($field_name, $post_id);
```
$field_name - the field name
$post_id - the post ID where the value is saved