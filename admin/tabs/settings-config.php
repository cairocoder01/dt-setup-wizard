<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Setup_Wizard_Tab_Settings
 */
class Disciple_Tools_Setup_Wizard_Tab_Settings
{
    public function content() {

        ?>
        <div class="wrap tab-advanced">
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
        $setting = get_option( 'dt_setup_wizard_config' );
        if ( $setting ) {
            $setting = json_encode( $setting );
        }
        ?>
      <!-- Box -->
      <table class="widefat striped">
        <thead>
        <tr>
          <th>Settings Config</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td>
            <form name="settingsConfig" onsubmit="settingsConfigSubmit(event)">
              <label for="config">JSON Config</label>
              <textarea id="config" name="config" class="auto-expand" data-min-rows="3" ><?php echo esc_attr( $setting );?></textarea>
              <button type="submit">Submit</button>
            </form>
          </td>
        </tr>
        </tbody>
      </table>
      <br>
      <!-- End Box -->
        <?php
    }

    public function right_column() {
        $sample = json_decode(
            '{
          "steps": [{
            "name": "Basic Settings",
            "description": "Enter basic blog settings",
            "config": {
                "options": [{
                    "key": "blogname",
                    "value": "M2M Journey Tools"
                }, {
                    "key": "blogdescription",
                    "value": " "
                }, {
                    "key": "admin_email",
                    "value": "admin@test.com"
                }]
            }
        }, {
            "name": "Custom Roles",
            "description": "Ensure any custom roles are added.",
            "config": {
                "options": [{
                    "key": "dt_custom_roles",
                    "value": {
                        "custom_plugin_admin": {
                            "description": "This role only has the ability to access D.T\'s wp-admin, manage D.T, and edit plugins, notably to manage plugin integrations. Role has no access to post data.",
                            "label": "Plugin Admin",
                            "slug": "custom_plugin_admin",
                            "capabilities": [
                                "access_disciple_tools",
                                "edit_plugins",
                                "activate_plugins",
                                "manage_dt",
                                "manage_options",
                                "read"
                            ]
                        }
                    }
                }]
            }
        }, {
            "name": "Mapping",
            "description": "Create a geocoding key. Key should have permissions for\n\n - Geocoding API\n - Maps Javascript API\n - Places API\n\nEnter the created API Key below.",
            "config": {
                "options": [{
                    "key": "x"
                }]
            }
        }, {
            "name": "Plugins",
            "description": "Confirm the appropriate plugins are installed and activated",
            "config": {
                "plugins": [
                    { "slug": "easy-wp-smtp"},
                    { "slug": "wp-webhooks" },
                    { "slug": "disciple-tools-advanced-security", "url": "https://github.com/cairocoder01/disciple-tools-advanced-security/releases/latest/download/disciple-tools-advanced-security.zip"},
                    { "slug": "disciple-tools-data-reporting", "url": "https://github.com/cairocoder01/disciple-tools-data-reporting/releases/latest/download/disciple-tools-data-reporting.zip"},
                    { "slug": "disciple-tools-echo", "url": "https://github.com/DiscipleTools/disciple-tools-echo/releases/latest/download/disciple-tools-echo.zip"},
                    { "slug": "disciple-tools-bulk-magic-link-sender", "url": "https://github.com/DiscipleTools/disciple-tools-bulk-magic-link-sender/releases/latest/download/disciple-tools-bulk-magic-link-sender.zip"},
                    { "slug": "disciple-tools-dashboard", "url": "https://github.com/DiscipleTools/disciple-tools-dashboard/releases/latest/download/disciple-tools-dashboard.zip"},
                    { "slug": "disciple-tools-facebook", "url": "https://github.com/DiscipleTools/disciple-tools-facebook/releases/latest/download/disciple-tools-facebook.zip" },
                    { "slug": "disciple-tools-genmapper", "url": "https://github.com/DiscipleTools/disciple-tools-genmapper/releases/latest/download/disciple-tools-genmapper.zip"},
                    { "slug": "disciple-tools-import", "url": "https://github.com/DiscipleTools/disciple-tools-import/releases/latest/download/disciple-tools-import.zip"},
                    { "slug": "disciple-tools-mobile-app-plugin", "url": "https://github.com/DiscipleTools/disciple-tools-mobile-app-plugin/releases/latest/download/disciple-tools-mobile-app-plugin.zip"},
                    { "slug": "disciple-tools-network-dashboard", "url": "https://github.com/DiscipleTools/disciple-tools-network-dashboard/releases/latest/download/disciple-tools-network-dashboard.zip"},
                    { "slug": "dt-outline-vpn", "url": "https://github.com/cairocoder01/dt-outline-vpn/releases/latest/download/disciple-tools-outline-vpn.zip"},
                    { "slug": "disciple-tools-setup-wizard", "url": "https://github.com/cairocoder01/dt-setup-wizard/releases/latest/download/disciple-tools-setup-wizard.zip"},
                    { "slug": "disciple-tools-training", "url": "https://github.com/discipletools/disciple-tools-training/releases/latest/download/disciple-tools-training.zip"}
                ]
            }
        }, {
            "name": "Wordfence",
            "description": "Go to [Wordfence Central](https://www.wordfence.com/central) to add this site."
        }, {
            "name": "User Setup",
            "description": "Select the users from the list below that should be included in this site. In addition, add the site admin user that is specific to this site.",
            "config": {
                "users": [{
                    "username": "user1",
                    "email": "user1@test.com",
                    "roles": ["dt_admin"],
                    "displayName": "One"
                }, {
                    "username": "user2",
                    "email": "user2@test.com",
                    "roles": ["dt_admin"],
                    "displayName": "Two"
                }, {
                    "username": "user3",
                    "email": "user3@test.com",
                    "roles": ["dt_admin"],
                    "displayName": "Three"
                }, {
                    "username": "user4",
                    "email": "user4@test.com",
                    "roles": ["dt_admin"],
                    "displayName": "Four"
                }, {
                    "username": "user5",
                    "email": "user5@test.com",
                    "roles": ["custom_plugin_admin", "strategist"],
                    "displayName": "Five"
                }]
            }
        }]
      }');
        ?>
    <!-- Box -->
    <table class="widefat striped">
      <thead>
      <tr>
        <th>JSON Sample</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td class="overflow-scroll">
        <pre><code><?php echo json_encode( $sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ?></code></pre>
        </td>
      </tr>
      </tbody>
    </table>
    <br>
    <!-- End Box -->
        <?php
    }
}

