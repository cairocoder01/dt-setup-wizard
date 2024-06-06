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
            $setting = json_encode( $setting, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
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
    "steps": [
        {
            "name": "Basic Settings",
            "description": "Enter basic blog settings",
            "config": {
                "options": [
                    {
                        "key": "blogname",
                        "value": "My Disciple Tools"
                    },
                    {
                        "key": "blogdescription",
                        "value": " "
                    },
                    {
                        "key": "admin_email",
                        "value": "admin@test.com"
                    }
                ]
            }
        },
        {
            "name": "Mapping",
            "description": "Create a [geocoding key](https://console.cloud.google.com/). Key should have permissions for\r\n\r\n - Geocoding API\r\n - Maps Javascript API\r\n - Places API"
        },
        {
            "name": "Plugins",
            "description": "Confirm the appropriate plugins are installed and activated",
            "config": {
                "plugins": [
                    {
                        "slug": "easy-wp-smtp"
                    },
                    {
                        "slug": "disciple-tools-bulk-magic-link-sender",
                        "url": "https://github.com/DiscipleTools/disciple-tools-bulk-magic-link-sender/releases/latest/download/disciple-tools-bulk-magic-link-sender.zip"
                    },
                    {
                        "slug": "disciple-tools-dashboard",
                        "url": "https://github.com/DiscipleTools/disciple-tools-dashboard/releases/latest/download/disciple-tools-dashboard.zip"
                    },
                    {
                        "slug": "disciple-tools-facebook",
                        "url": "https://github.com/DiscipleTools/disciple-tools-facebook/releases/latest/download/disciple-tools-facebook.zip"
                    },
                    {
                        "slug": "disciple-tools-genmapper",
                        "url": "https://github.com/DiscipleTools/disciple-tools-genmapper/releases/latest/download/disciple-tools-genmapper.zip"
                    },
                    {
                        "slug": "disciple-tools-import",
                        "url": "https://github.com/DiscipleTools/disciple-tools-import/releases/latest/download/disciple-tools-import.zip"
                    },
                    {
                        "slug": "disciple-tools-mobile-app-plugin",
                        "url": "https://github.com/DiscipleTools/disciple-tools-mobile-app-plugin/releases/latest/download/disciple-tools-mobile-app-plugin.zip"
                    },
                    {
                        "slug": "disciple-tools-network-dashboard",
                        "url": "https://github.com/DiscipleTools/disciple-tools-network-dashboard/releases/latest/download/disciple-tools-network-dashboard.zip"
                    },
                    {
                        "slug": "disciple-tools-setup-wizard",
                        "url": "https://github.com/cairocoder01/dt-setup-wizard/releases/latest/download/disciple-tools-setup-wizard.zip"
                    },
                    {
                        "slug": "disciple-tools-training",
                        "url": "https://github.com/discipletools/disciple-tools-training/releases/latest/download/disciple-tools-training.zip"
                    }
                ]
            }
        },
        {
            "name": "Wordfence",
            "description": "Go to [Wordfence Central](https://www.wordfence.com/central) to add this site."
        },
        {
            "name": "User Setup",
            "description": "Select the users from the list below that should be included in this site. In addition, add the site admin user that is specific to this site.",
            "config": {
                "users": [
                    {
                        "username": "user1",
                        "email": "user1@test.com",
                        "roles": [
                            "dt_admin"
                        ],
                        "displayName": "One"
                    },
                    {
                        "username": "user2",
                        "email": "user2@test.com",
                        "roles": [
                            "multiplier"
                        ],
                        "displayName": "Two"
                    },
                    {
                        "username": "user3",
                        "email": "user3@test.com",
                        "roles": [
                            "dispatcher"
                        ],
                        "displayName": "Three"
                    },
                    {
                        "username": "user4",
                        "email": "user4@test.com",
                        "roles": [
                            "partner",
                            "strategist"
                        ],
                        "displayName": "Four"
                    }
                ]
            }
        }
    ]
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
          <a href="javascript:copySample()" class="copy-sample">

            <img class="dt-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/duplicate.svg' ) ?>"/>
            <?php echo esc_html( __( 'Copy sample to config', 'disciple-tools-setup-wizard' ) ) ?>
          </a>
        <pre><code id="sample-config"><?php echo json_encode( $sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ?></code></pre>
        </td>
      </tr>
      </tbody>
    </table>
    <br>
    <!-- End Box -->
        <?php
    }
}

