<?php


namespace ThemesPond\Premium;

class Premium {

    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    public static $instance;

    private $license_type;
    private $product_version;
    private $license_base;
    private $license_url;

    public function __construct($license_type, $license_base, $product_version, $license_url) {
        static::$instance = $this;
        $this->license_type = $license_type;
        $this->product_version = $product_version;
        $this->license_base = $license_base;
        $this->license_url = $license_url;

        $this->hooks();
        $this->includes();
    }

    /**
     * Enqueue admin script
     *
     * @since 1.0
     * @param string $hook
     * @return void
     */
    public function admin_scripts($hook) {
        $screen = get_current_screen();

	    if ($this->license_type == 'plugin') {
		    $page = $this->license_base . "_page_" . $this->license_base . '-license';
	    }

	    if ($screen->id == $page) {
		    wp_enqueue_script('tp-dashboard-admin-js', $this->license_url . '/vendor/themespond/premium/src/assets/js/scripts.js',
			    array(),
			    $this->product_version,
			    true);
		    wp_enqueue_style('tp-dashboard-css', $this->license_url . '/vendor/themespond/premium/src/assets/css/style.css', array(),
			    $this->product_version);
		    wp_localize_script('tp-dashboard-admin-js', 'tp_dashboard_admin_js', array('ajax_url' => admin_url('admin-ajax.php')));
	    }
    }

    /**
     * Hook into actions and filters.
     */
    public function hooks() {
        $service = new Service($this->license_base);
        add_action('admin_menu', array($this, 'register_license_page'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 10);
        // Validate service
        add_action('wp_ajax_validate_service', array($service, 'validate_service'), 10);
    }

    /**
     * Include or Init class code
     */
    public function includes() {
        new Updater($this->license_base, $this->product_version, $this->license_type);
    }

    /**
     * Resolve a object/property and call it if needed.
     *
     * @param  string $id Param ID.
     * @return mixed
     */
    public function factory($id) {
        if (isset($this->$id)) {
            return $this->$id;
        }
    }

    public function register_license_page() {
        if ($this->license_type == 'plugin') {
            add_menu_page($this->title, esc_html__('TP Dashboard', 'tp-dashboard'), 'manage_options', $this->license_base, array($this, 'plugin_load'), 'dashicons-images-alt2', 12); // USE FOR DEV
            // LICENSE PAGE
            add_submenu_page($this->license_base, esc_html__('License Manager', 'tp-dashboard'), esc_html__('License Manager', 'tp-dashboard'), 'manage_options', $this->license_base . '-license', array($this, 'plugin_load'));
        } else {
            // Add setting page for theme
            add_options_page(
                esc_html__('License Manager', 'tp-dashboard'),
                esc_html__('License Manager', 'tp-dashboard'),
                'manage_options',
                $this->license_base . '-license.php',
                array($this, 'plugin_load')
            );
        }
    }

    /**
     * Load content
     *
     * @return void
     * @since 1.0.0
     */
    public function plugin_load() {
        $active = false;
        $key = '';
        $email = '';
        $product = $this->license_base;
        $tp_premium_data = get_option('tp_premium_data');
        $tp_premium_data = json_decode($tp_premium_data, true);
        if (isset($tp_premium_data[$product])) {
            $active = $tp_premium_data[$product]['active'];
            if ($active) {
                $email = $tp_premium_data[$product]['email'];
                $key = $tp_premium_data[$product]['key'];
            }
        }
        $data = array(
            'active' => $active,
            'key'    => $key,
            'email'  => $email
        );
        $this->tp_dashboard_template('dashboard', $data);
    }

    /**
     * Template file
     *
     * @since 1.0
     *
     * @param string $slug short page template file
     * @param array  $data Pass params to template file
     * @return void
     */
    function tp_dashboard_template($slug, $data = array()) {
        if (is_array($data)) {
            extract($data);
        }
        $base_premium = realpath(__DIR__);
        $template = $base_premium . '/templates/' . $slug . '.php';
        include $template;
    }

}
