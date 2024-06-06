<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Setup_Wizard_Menu
 */
class Disciple_Tools_Setup_Wizard_Menu {

    public $token = 'disciple_tools_setup_wizard';
    public $page_title = 'Setup Wizard';

    private static $_instance = null;

    /**
     * Disciple_Tools_Setup_Wizard_Menu Instance
     *
     * Ensures only one instance of Disciple_Tools_Setup_Wizard_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Setup_Wizard_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( 'admin_menu', array( $this, 'register_menu' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'process_scripts' ) );

        $this->page_title = __( 'Setup Wizard', 'disciple-tools-setup-wizard' );

        require_once 'tabs/wizard.php';

        require_once 'tabs/advanced-config.php';

        require_once 'tabs/settings-config.php';
    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        $this->page_title = __( 'Setup Wizard', 'disciple-tools-setup-wizard' );

        add_submenu_page( 'dt_extensions', $this->page_title, $this->page_title, 'manage_dt', $this->token, array( $this, 'content' ) );
    }

    /**
     * Menu stub. Replaced when Disciple.Tools Theme fully loads.
     */
    public function extensions_menu() {}

    public function process_scripts() {
        wp_enqueue_script( 'dt_setup_wizard_script', plugin_dir_url( __FILE__ ) . 'js/wizard.js', array(
            // 'jquery',
            'lodash',
        ), filemtime( __DIR__ . '/js/wizard.js' ), true );

        wp_localize_script(
            'dt_magic_links_general_script', 'dt_magic_links', array(
                'dt_xyz' => '',
            )
        );
        wp_enqueue_style( 'dt_setup_wizard_css', plugin_dir_url( __FILE__ ) . 'css/wizard.css', false,
        filemtime( __DIR__ . '/css/wizard.css' ) );
    }
    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple.Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET['tab'] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET['tab'] ) );
        } else {
            $tab = 'wizard';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->page_title ) ?></h2>
            <?php wp_nonce_field( 'security_headers', 'security_headers_nonce' ); ?>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'wizard' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'wizard' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">Wizard</a>
                <a href="<?php echo esc_attr( $link ) . 'advanced' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'advanced' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">Advanced</a>
                <a href="<?php echo esc_attr( $link ) . 'settings' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'settings' ) ? 'nav-tab-active' : '' ); ?>">Settings</a>
            </h2>

            <?php
            switch ( $tab ) {
                case 'general':
                case 'wizard':
                    $object = new Disciple_Tools_Setup_Wizard_Tab();
                    $object->content();
                    break;
                case 'advanced' :
                    $object = new Disciple_Tools_Setup_Wizard_Tab_Advanced();
                    $object->content();
                    break;
                case 'settings':
                    $object = new Disciple_Tools_Setup_Wizard_Tab_Settings();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <ul class="messages" id="message-container"></ul>

        <div id="log-container">
            <button class="toggle" onclick="toggleLogContainer(event)">
                <span class="dashicons dashicons-arrow-up-alt2"></span>
            </button>
            <ul class="logs"></ul>
        </div>
        <?php
    }
}
Disciple_Tools_Setup_Wizard_Menu::instance();

/**
 * Class Disciple_Tools_Setup_Wizard_Tab_Second
 */
class Disciple_Tools_Setup_Wizard_Tab_Second {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Header</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Content
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Content
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}

