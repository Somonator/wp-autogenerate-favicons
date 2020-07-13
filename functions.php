<?
class autogenerate_favicons {
	function __construct() {
		add_action('wp_head', [$this, 'header_custom_code'], 2); //insert favicons
		add_action('acf/update_value/name=d_favicon', [$this, 'generate_favicons'], 20, 3); // check update acf field with name "d_favicon"
	}

	function header_custom_code() { // check value acf field and insert all favicons
		if (get_field('d_favicon', 'option')) { 
			$favicons_folder = get_template_directory_uri() . '/generated-favicons/';
	
			echo '<link rel="apple-touch-icon" sizes="57x57" href="' . $favicons_folder . 'icon-57x57.png">';
			echo '<link rel="apple-touch-icon" sizes="60x60" href="' . $favicons_folder . 'icon-60x60.png">';
			echo '<link rel="apple-touch-icon" sizes="72x72" href="' . $favicons_folder . 'icon-72x72.png">';
			echo '<link rel="apple-touch-icon" sizes="76x76" href="' . $favicons_folder . 'icon-76x76.png">';
			echo '<link rel="apple-touch-icon" sizes="114x114" href="' . $favicons_folder . 'icon-114x114.png">';
			echo '<link rel="apple-touch-icon" sizes="120x120" href="' . $favicons_folder . 'icon-120x120.png">';
			echo '<link rel="apple-touch-icon" sizes="144x144" href="' . $favicons_folder . 'icon-144x144.png">';
			echo '<link rel="apple-touch-icon" sizes="152x152" href="' . $favicons_folder . 'icon-152x152.png">';
			echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $favicons_folder . 'icon-180x180.png">';
			echo '<link rel="shortcut icon" type="image/png" sizes="16x16" href="' . $favicons_folder . 'icon-16x16.png">';
			echo '<link rel="shortcut icon" type="image/png" sizes="32x32" href="' . $favicons_folder . 'icon-32x32.png">';
			echo '<link rel="shortcut icon" type="image/png" sizes="96x96" href="' . $favicons_folder . 'icon-96x96.png">';						
			echo '<link rel="shortcut icon" type="image/png" sizes="192x192" href="' . $favicons_folder . 'icon-192x192.png">';
			echo '<meta name="msapplication-TileImage" content="' . $favicons_folder . 'icon-144x144.png">';
		}
	}

	function generate_favicons($value, $post_id, $field) {
		$old_value = get_field($field['name'], $post_id);

		if ($old_value) {
			$old_value = attachment_url_to_postid($old_value['url']);
		}

		if ($value && $value !== $old_value) {
			$img_url = wp_get_attachment_url($value);
			$server_path = ABSPATH . str_replace(get_home_url() . '/', '', $img_url); 
			$image_edit = wp_get_image_editor($server_path); // init wp image editor
	
			if (!is_wp_error($image_edit)) {
				$image_edit->save(TEMPLATEPATH . '/generated-favicons/icon.png'); // save img at template directory in "generated-favicons" folder

				$sizes_array = 	[
					'16x16' => ['width' => 16, 'height' => 16, 'crop' => ['center', 'center']],
					'32x32' => ['width' => 32, 'height' => 32, 'crop' => ['center', 'center']],
					'57x57' => ['width' => 57, 'height' => 57, 'crop' => ['center', 'center']],
					'60x60' => ['width' => 60, 'height' => 60, 'crop' => ['center', 'center']],												
					'72x72' => ['width' => 72, 'height' => 72, 'crop' => ['center', 'center']],
					'76x76' => ['width' => 76, 'height' => 76, 'crop' => ['center', 'center']],
					'96x96' => ['width' => 96, 'height' => 96, 'crop' => ['center', 'center']],
					'114x114' => ['width' => 114, 'height' => 114, 'crop' => ['center', 'center']],
					'120x120' => ['width' => 120, 'height' => 120, 'crop' => ['center', 'center']],
					'144x144' => ['width' => 144, 'height' => 144, 'crop' => ['center', 'center']],
					'152x152' => ['width' => 152, 'height' => 152, 'crop' => ['center', 'center']],						
					'180x180' => ['width' => 180, 'height' => 180, 'crop' => ['center', 'center']],
					'192x192' => ['width' => 192, 'height' => 192, 'crop' => ['center', 'center']]
				]; // all sizes favicons
	
				$resize = $image_edit->multi_resize($sizes_array); // generate images with all sizes in "generated-favicons" folder
					
				if (!empty($resize)) {
					return $value; // if everything is okay, save field
				}
			}

			return false; // if any error occurred, return false
		}

		return $value; // if old value = new value, save field without generation
	}
}

new autogenerate_favicons();
?>