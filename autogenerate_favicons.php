<?
class autogenerate_favicons {
	var $theme_color = '#ffffff';	
	var $favicons_folder = '/generated-favicons/';

	function __construct($field_name, $post_id) {
		$this->field = $field_name;
		$this->post_id = $post_id;

		$this->favicons_root = get_template_directory() . $this->favicons_folder;		
		$this->favicons_url = get_template_directory_uri() . $this->favicons_folder;
		
		add_action('admin_init', [$this, 'start_session']); // start session for writing notices
		add_action('admin_notices', [$this, 'display_notices']); // display notices, if exist
		add_action('acf/update_value/name=' . $this->field, [$this, 'generate_favicons'], 20, 3); // check update acf field
		add_action('wp_head', [$this, 'set_header_custom_code'], 2); //insert favicons
	}
	
	function start_session() {
		if (!isset($_SESSION)) {
			session_start();			
		}		
	}
	
	function add_notice($text = '', $type = 'success') {
		$_SESSION['gen_fav_notices'][] = [
			'type' => $type,
			'text' => 'Generate favicons: ' . $text
		];
	}

	function display_notices() {
		if (!isset($_SESSION['gen_fav_notices']) && empty($_SESSION['gen_fav_notices'])) {
			return;
		}

		if (is_array($_SESSION['gen_fav_notices'])) {
			foreach ($_SESSION['gen_fav_notices'] as $notice) {
				echo '<div class="notice notice-' . $notice['type'] . ' is-dismissible">';
				echo '<p>' . $notice['text'] . '</p>';
				echo '</div>';
			}
		}
		
		unset($_SESSION['gen_fav_notices']);
	}

	function get_old_value($field, $post_id) {
		$value = get_field($field['name'], $post_id);

		if (is_array($value)) {
			return $value['id'];
		} else if(is_string($value)) {
			return attachment_url_to_postid($value);
		} else {
			return $value;
		}
	}

	function generate_favicons($value, $post_id, $field) {
		if ($field['type'] !== 'image') {
			$this->add_notice('Field type must be image', 'error');
			return false;
		}

		$old_value = $this->get_old_value($field, $post_id);

		if ($value && $value !== $old_value) {
			$img_url = wp_get_attachment_url($value);
			$server_path = ABSPATH . str_replace(get_home_url() . '/', '', $img_url); 
			$image_edit = wp_get_image_editor($server_path); // init wp image editor
	
			if (!is_wp_error($image_edit)) {
                $image_size = $image_edit->get_size();

                if ($image_size['width'] < 194 && $image_size['height'] < 194) { // check minimum sizes
					$this->add_notice('The image must be larger than 194x194', 'error');
                    return $old_value;
                }

				$image_edit->save($this->favicons_root . 'icon.png'); // save img at template directory

				$sizes_array = [
					'16x16' => ['width' => 16, 'height' => 16, 'crop' => ['center', 'center']],
					'32x32' => ['width' => 32, 'height' => 32, 'crop' => ['center', 'center']],
					'36x36' => ['width' => 36, 'height' => 36, 'crop' => ['center', 'center']],
					'48x48' => ['width' => 48, 'height' => 48, 'crop' => ['center', 'center']],
					'57x57' => ['width' => 57, 'height' => 57, 'crop' => ['center', 'center']],
					'60x60' => ['width' => 60, 'height' => 60, 'crop' => ['center', 'center']],												
					'70x70' => ['width' => 70, 'height' => 70, 'crop' => ['center', 'center']],
					'72x72' => ['width' => 72, 'height' => 72, 'crop' => ['center', 'center']],
					'76x76' => ['width' => 76, 'height' => 76, 'crop' => ['center', 'center']],
					'96x96' => ['width' => 96, 'height' => 96, 'crop' => ['center', 'center']],
					'114x114' => ['width' => 114, 'height' => 114, 'crop' => ['center', 'center']],
					'120x120' => ['width' => 120, 'height' => 120, 'crop' => ['center', 'center']],
					'144x144' => ['width' => 144, 'height' => 144, 'crop' => ['center', 'center']],
					'150x150' => ['width' => 150, 'height' => 150, 'crop' => ['center', 'center']],						
					'152x152' => ['width' => 152, 'height' => 152, 'crop' => ['center', 'center']],						
					'180x180' => ['width' => 180, 'height' => 180, 'crop' => ['center', 'center']],
					'192x192' => ['width' => 192, 'height' => 192, 'crop' => ['center', 'center']]
				]; // all sizes favicons
	
				$resize = $image_edit->multi_resize($sizes_array); // generate images all sizes
					
				if (!empty($resize)) {
					$this->generate_manifest();
					$this->generate_browserconfig();
					$this->add_notice('Favicons generation complete', 'success');

					return $value; // if everything is okay, save field
				}
			}

			$this->add_notice('A runtime error occurred', 'error');
			return $old_value;
		}

		return $value; // if old value = new value, save field without generation
	}

	function generate_manifest() {
		$sizes = ['36x36', '48x48', '72x72', '96x96', '144x144', '192x192'];
		$densities = ['0.75', '1.0', '1.5', '2.0', '3.0', '4.0'];
		$manifest = [
			'name' => '',
			'icons' => []
		];


		$manifest['name'] = get_bloginfo('name');

		for ($i = 0; $i <= count($sizes) - 1; $i++) {
			$manifest['icons'][$i] = [
				'src' => $this->favicons_url . 'icon-' . $sizes[$i] . '.png',
				'sizes' => $sizes[$i],
				'type' => 'image/png',
				'density' => @$densities[$i]
			];
		}

		$manifest_json = json_encode($manifest);

		if (file_put_contents($this->favicons_root . 'manifest.json', $manifest_json) === false) {
			$this->add_notice('An error occured while creating manifest.json file', 'error');
		}
	}

	function generate_browserconfig() {
        $content = '
        <?xml version="1.0" encoding="utf-8"?>
		<browserconfig>
			<msapplication>
				<tile>
					<square70x70logo src="' . $this->favicons_url . 'icon-70x70.png"/>
					<square150x150logo src="' . $this->favicons_url . 'icon-150x150.png"/>
					<square310x310logo src="' . $this->favicons_url . 'icon.png"/>
					<TileColor>' . $this->theme_color . '</TileColor>
				</tile>
			</msapplication>
        </browserconfig>
        ';

		$content = preg_replace('/\r\n\s+/m', '', $content);

		if (file_put_contents($this->favicons_root . 'browserconfig.xml', $content) === false) {
			$this->add_notice('An error occured while creating browserconfig.xml file', 'error');
		}
	}

	function set_header_custom_code() { // check value acf field and insert all favicons
		if (get_field($this->field, $this->post_id)) { 
			$favicons_folder = $this->favicons_url;

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
			echo '<link rel="manifest" href="' . $favicons_folder . 'manifest.json">';	
			echo '<meta name="msapplication-config" content="' . $favicons_folder . 'browserconfig.xml">';		
			echo '<meta name="msapplication-TileImage" content="' . $favicons_folder . 'icon-144x144.png">';
			echo '<meta name="msapplication-TileColor" content="' . $this->theme_color . '">';
			echo '<meta name="theme-color" content="' . $this->theme_color . '">';
		}
    }
}