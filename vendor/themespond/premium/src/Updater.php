<?php

namespace TP\Premium;


class Updater {
    /**
     * The plugin current version
     *
     * @var string
     */
    public $current_version;

    /**
     * The plugin remote update path
     *
     * @var string
     */
    public $update_path;

    /**
     * Plugin Slug (plugin_directory/plugin_file.php)
     *
     * @var string
     */
    public $plugin_slug;

    /**
     * Plugin name (plugin_file)
     *
     * @var string
     */
    public $slug;

    /**
     * Base API
     *
     * @var
     */
    private $base_api = 'http://server-api.dev/api/v1/premium/product/';
    private $site;
    private $product_base;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     */
    function __construct($product_base, $product_version, $license_type) {
        $this->product_base = $product_base;
        $premium_data = get_option("tp_premium_data");
        $premium_data = json_decode($premium_data, true);

        if (!isset($premium_data[$product_base])) {
            add_action('admin_notices', array($this, 'show_admin_notices')); // Show notice apply license
        }
        $license = $premium_data[$product_base];
        // Set the class public variables
        $this->update_path = $this->base_api . $product_base .
            "/?email=" . $license['email'] . "&key=" . $license['key'];
        $this->current_version = $product_version;

        $this->site = "https://www.themespond.com/product/tp-image-optimizer/";

        $this->plugin_slug = $product_base . "/" . $product_base . ".php";
        // FOR DEV
        $this->plugin_slug = "tp-premium/tp-dashboard.php";

        $this->slug = str_replace('.php', '', $this->plugin_slug);

        if('plugin' == $license_type){
            /**
             * UPDATE PLUGIN *******************************************************************
             */
            add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_plugin_update')); // define the alternative API for updating checking
            add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);// Define the alternative response for information checking
        } else{
            /**
             * UPDATE THEME *******************************************************************
             */
            add_action("pre_set_site_transient_update_themes", array(&$this, 'check_themes_update'));
            //$current = get_site_transient('update_themes');
            //p($current);
        }

        add_action('in_plugin_update_message-' . $this->plugin_slug, array(
            $this,
            'addUpgradeMessageLink',
        ));
    }

    /**
     * If the license has not been configured properly, display an admin notice.
     */
    public function show_admin_notices() {
        $msg = esc_html__('Please enter your email and license key to enable updates to TP Dashboard', 'tp_dashboard');
        ?>
        <div class="update-nag">
            <?php echo $msg; ?>,
            <a href="<?php echo admin_url('options-general.php?page=tp-dashboard') ?>">
                <?php _e('Complete the setup now.', 'tp_dashboard'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Add our self-hosted auto update plugin to the filter transient
     *
     * @param $transient
     * @return object $ transient
     */
    public function check_plugin_update($transient) {
        //p($transient);
        if (empty($transient->checked)) {
            return $transient;
        }
        $remote_version = $this->getRemote_version();// Get the remote version
        if (version_compare($this->current_version, $remote_version, '<')) {// If a newer version is available, add the update
            $obj = new \stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            //$obj->url = $this->update_path;
            $obj->url = $this->site;
            $obj->package = $this->getRemote_package();
            $transient->response[$this->plugin_slug] = $obj;
        }
        return $transient;
    }


    public function check_themes_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        // Get the remote version
        $remote_version = $this->getRemote_version();// Get the remote version
        if (version_compare($this->current_version, $remote_version, '<')) { // If a newer version is available, add the update
            $transient->response = array(
                $this->product_base => array(
                    'theme'       => $this->product_base,
                    'new_version' => $remote_version,
                    'url'         => $this->update_path,
                    'package'     => $this->getRemote_package()
                )
            );
        }
        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array   $action
     * @param object  $arg
     * @return bool|object
     */
    public function check_info($false, $action, $arg) {
        if ($arg->slug === $this->slug) {
            $information = $this->getRemote_information();
            return $information;
        }
        return false;
    }

    /**
     * Return the remote version
     *
     * @return string $remote_version
     */
    public function getRemote_version() {
        $request = wp_remote_get($this->update_path, array('body' => array('action' => 'version')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;

    }

    /**
     * Get information about the remote version
     *
     * @return bool|object
     */
    public function getRemote_information() {
        $request = wp_remote_get($this->update_path, array('body' => array('action' => 'info')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return unserialize($request['body']);
        }
        return false;
    }

    /**
     * Return the status of the plugin licensing
     *
     * @return boolean $remote_license
     */
    public function getRemote_package() {
        $request = wp_remote_get($this->update_path);
        $request_info = '';
        $package = '';
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            $request_info = $request['body'];
        }
        $request_info = json_decode($request_info, true);

        if (!empty($request_info['download_link'])) {
            $package = $request_info['download_link'];
        }
        return $package;
    }

    /**
     * Shows message on Wp plugins page with a link for updating from envato.
     */
    public function addUpgradeMessageLink() {
        $license = $this->getLicenseData();
        $is_activated = $license['active'];
        if (!$is_activated) {
            $url = "";
            $redirect = sprintf('<a href="%s" target="_blank">%s</a>', $url, __('settings', 'tp_dashboard'));

            echo sprintf(' ' . __('To receive automatic updates license activation is required. Please visit %s to activate your TP Dashboard.', 'tp_dashboard'), $redirect) . sprintf(' <a href="https://themespond.com" target="_blank">%s</a>', __('Got ThemesPond product?', 'tp_dashboard'));
        }
    }

    protected function getLicenseData() {
        $premiumData = get_option('tp_premium_data');
        $premiumData = json_decode($premiumData, true);
        if (!isset($premiumData[$this->product_base])) {
            return null;
        }
        return $premiumData[$this->product_base];
    }
}