<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
function is_json_object( $array ) {
    json_encode( $array );
    return json_last_error() === JSON_ERROR_NONE;
}
function is_plugin_installed( $slug ) {
    $plugins = array_keys( get_plugins() );
    $is_installed = false;
    foreach ( $plugins as $plugin ) {
        if ( str_contains( $plugin, $slug ) ) {
            $is_installed = true;
            break;
        }
    }
    return $is_installed;
}
function is_plugin_activated( $slug ) {
    $active_plugins = get_option( 'active_plugins' );
    foreach ( $active_plugins as $plugin ){
        if ( str_contains( $plugin, $slug ) ){
            return true;
        }
    }
}
function step_status( $step ) {
    $checkmark = true;
    if ( $step['config']['options'] ) {
        $options = $step['config']['options'];
        foreach ( $options as $option ) {
            $key = $option['key'];
            $value = $option['value'];
            $db_value = get_option( $key );
            if ( $value != $db_value ) {
                $checkmark = false;
                break;
            }
        }
    } elseif ( $step['config']['plugins'] ) {
        $plugins = $step['config']['plugins'];
        foreach ( $plugins as $plugin ) {
            if ( !is_plugin_activated( $plugin['slug'] ) ) {
                $checkmark = false;
                break;
            }
        }
    } elseif ( $step['config']['users'] ) {
        $checkmark = false;
        $users = $step['config']['users'];
        foreach ( $users as $user ) {
            if ( username_exists( $user['username'] ) ) {
                $checkmark = true;
                break;
            }
        }
    } else {
        $db_value = get_option( 'dt_manual_steps' );
        if ( $db_value[$step['name']] == 'false' ) {
            $checkmark = false;
        }
    }
    return $checkmark;
}
/**
 * Class Disciple_Tools_Setup_Wizard_Tab
 */
class Disciple_Tools_Setup_Wizard_Tab
{
    public function content() {
        $setting = get_option( 'dt_setup_wizard_config' );
        if ( !is_json_object( $setting ) || empty( $setting ) ) {
            ?>
          <div>
            Setup Wizard has not been configured. Please enter a JSON config option on the Settings tab
            <a href='admin.php?page=disciple_tools_setup_wizard&tab=settings'>here</a>.
          </div>
            <?php
        }
        else {
            ?>
        <div class="wrap tab-advanced">
          <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
              <div id="post-body-content">
                <!-- Main Column -->
                <?php $this->main_column( $setting ) ?>
                <!-- End Main Column -->
              </div><!-- end post-body-content -->
              <div id="postbox-container-1" class="postbox-container">
                <!-- Right Column -->
                <?php $this->right_column( $setting ) ?>
                <!-- End Right Column -->
              </div><!-- postbox-container 1 -->
              <div id="postbox-container-2" class="postbox-container">
              </div><!-- postbox-container 2 -->
            </div><!-- post-body meta box container -->
          </div><!--poststuff end -->
        </div><!-- wrap end -->
            <?php
        }
    }
    public function main_column( $setting ) {
        $parsedown = new Parsedown();
        if ( isset( $_GET['step'] ) ) {
            $step = sanitize_key( wp_unslash( $_GET['step'] ) );
        } else {
            $step = '1';
        }
        //$config = json_encode( $setting );
        ?>
        <h1><?php echo esc_html( $setting['steps'][$step -1]['name'] )?></h1>
        <?php echo wp_kses_post( $parsedown->text( $setting['steps'][$step -1]['description'] ) )?>
        <?php if ( $setting['steps'][$step -1]['config']['options'] ){//key, value
            $this->load_options( $setting['steps'][$step -1]['config']['options'], $step );
        } elseif ( $setting['steps'][$step -1]['config']['plugins'] ){//key, value
            $this->load_plugins( $setting['steps'][$step -1]['config']['plugins'], $step );
        } elseif ( $setting['steps'][$step -1]['config']['users'] ){//key, value
            $this->load_users( $setting['steps'][$step -1]['config']['users'], $step );
        } else {
            $this->load_manual( $setting['steps'][$step -1], $step );
        }
        ?>
    <br>
        <?php
                  //} ?>
        <!-- End Box -->
        <?php
    }
    public function right_column( $setting ) {
      //$config = json_encode( $setting );
        ?>
  <!-- Box -->
    Steps:
        <?php
        $link = 'admin.php?page=disciple_tools_setup_wizard&tab=wizard&step=';
        ?>
    <ol>
        <?php
        foreach ( $setting['steps'] as $key =>$item )
        {
            $step_status = step_status( $item );
            $key++;
            ?>
              <li>
                <a href="<?php echo esc_attr( $link ) . esc_html( $key ) ?>"><?php echo esc_html( $item['name'] ) ?></a>
                <?php
                if ( $step_status ) {
                    ?>
                  <span>&#10003;</span>
                    <?php
                }
                ?>
              </li>
            <?php
        }
        ?>
    </ol>
  <br>
  <!-- End Box -->
        <?php
    }
    public function load_options( $options, $step ) {
        ?>
    <form onsubmit="onClickOptionButton(event)">
    <table class="widefat striped">
          <thead>
            <tr>
              <th colspan="4"><?php echo 'Set Options' ?></th>
            </tr>
          </thead>
          <tbody>
          <?php
            foreach ( $options as $option ){
                $key = $option['key'];
                $value = $option['value'];
                $db_value = get_option( $key );
                if ( gettype( $value ) == 'array' ){
                    $value = json_encode( $value, JSON_PRETTY_PRINT );
                }
                if ( gettype( $db_value ) == 'array' ){
                    $db_value = json_encode( $db_value, JSON_PRETTY_PRINT );
                }
                ?>
            <tr>
              <td>
                <?php echo esc_html( $key ); ?>
              </td>
              <td>
                <span id="<?php echo esc_html( $key ) ?>value"><?php echo esc_html( $db_value ); ?></span>
              </td>
                <?php
                if ( $value == $db_value ){
                    ?>
                <td>
                </td>
                <td>
                    <?php
                    echo 'Done!';
                    ?>
                </td>
                    <?php
                } else {
                    ?>
              <td>
                    <?php
                    if ( $value[0] == '{' ) {?>
                  <textarea id="<?php echo esc_html( $key ) ?>input" name=<?php echo esc_html( $key ) ?>><?php echo esc_attr( $value ) ?></textarea>
                          <?php
                    } else {
                        ?>
                  <input type="text"id="<?php echo esc_html( $key ) ?>input"  name=<?php echo esc_html( $key ) ?> value="<?php echo esc_html( $value ) ?>" />
                        <?php
                    }
                    ?>
              </td>
              <td>
                <button id="<?php echo esc_html( $key ) ?>" type="submit" name="button" value="<?php echo esc_html( $key )  ?>">
                Update
                </button>
                    <?php
                }
                ?>
              </td>
            </tr>
                <?php
            }
            ?>
          </tbody>
        </table>
        <button type="submit" name="button" value="all">
        Update All
        </button>
        </form>
        <?php
    }
    public function load_plugins( $plugins, $step ) {
        ?>
    <form id="plugins" onsubmit="onInstallClick(event)">
    <input type="hidden" name="ajax_nonce" value="<?php echo esc_html( wp_create_nonce( 'updates' ) ) ?>" />
    <table class="widefat striped">
          <thead>
            <tr>
              <th colspan="3"><?php echo 'Set Plugins' ?></th>
            </tr>
          </thead>
          <tbody>
          <?php
            foreach ( $plugins as $plugin ){
                $slug = $plugin['slug'];
                ?>
            <tr>
              <td>
                <?php echo esc_html( $slug ); ?>
              </td>
                <?php
                if ( is_plugin_installed( $slug ) ){
                  //if ( is_plugin_active( $slug . '/' . $slug . '.php' )){
                    if ( is_plugin_activated( $slug ) ){
                        ?>
                      <td>
                        <?php echo 'Active' ?>
                      </td>
                        <?php
                    } else {
                        ?>
                      <td>
                        <input type="hidden" id="<?php echo esc_html( $slug )  ?>hidden" name="plugin" value="<?php echo esc_html( json_encode( $plugin ) )  ?>" />
                        <button type="submit" id="<?php echo esc_html( $slug )  ?>" name="button" value="<?php echo esc_html( json_encode( $plugin ) )  ?>">
                        Activate
                        </button>
                      </td>
                        <?php
                    }
                } else {
                    ?>
                  <td>
                    <input type="hidden" id="<?php echo esc_html( $slug )  ?>hidden" name="plugin" value="<?php echo esc_html( json_encode( $plugin ) )  ?>" />
                    <button type="submit" id="<?php echo esc_html( $slug )  ?>" name="button" value="<?php echo esc_html( json_encode( $plugin ) )  ?>">
                    Install & Activate
                    </button>
                  </td>
                    <?php
                }
                ?>
            </tr>
                <?php
            }
            ?>
          </tbody>
        </table>
        <button type="submit" name="button" value="all">
          Install/Activate All
        </button>
        </form>
        <?php
    }
    public function load_users( $users, $step ) {
        ?>
<form onsubmit="onClickUserButton(event)">
<table class="widefat striped">
      <thead>
        <tr>
          <th><?php echo 'Set Users' ?></th>
          <td>
          <?php echo esc_html( 'Email' ); ?>
          </td>
          <td>
          <?php echo esc_html( 'Display Name' ); ?>
          </td>
          <td colspan="2">
          <?php echo esc_html( 'Roles' ); ?>
          </td>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ( $users as $user ){
            $username = $user['username'];
            $email = $user['email'];
            $display_name = $user['displayName'];
            $roles = $user['roles'];
            ?>
        <tr>
          <td>
            <?php echo esc_html( $username ); ?>
          </td>
          <td>
            <?php echo esc_html( $email ); ?>
          </td>
          <td>
            <?php echo esc_html( $display_name ); ?>
          </td>
          <td>
            <?php echo esc_html( $roles ); ?>
          </td>
          <td>
            <?php
            if ( username_exists( $username ) ){
                echo 'Done!';
            } else {
                ?>
              <input type="hidden" name="<?php echo esc_html( $username ) ?>" id="<?php echo esc_html( $username )  ?>hidden" value="<?php echo esc_html( json_encode( $user ) ) ?>" />
              <button type="submit" name="button" id="<?php echo esc_html( $username )  ?>" value="<?php echo esc_html( $username )  ?>">
              Add User
              </button>
                <?php
            }
            ?>
          </td>
        </tr>
            <?php
        }
        ?>
      </tbody>
    </table>
    <button type="submit" name="button" value="all">
    Add All
    </button>
    </form>
        <?php
    }
    public function load_manual( $option, $step ) {
        $db_value = get_option( 'dt_manual_steps' );
        $db_value[$option['name']] = true;
        ?>
    <form onsubmit="onClickManualButton(event)">
        <button type="submit" name="button" value="<?php echo esc_html( json_encode( $db_value ) )  ?>">
        Mark as Complete
        </button>
        </form>
        <?php
    }
}
?>