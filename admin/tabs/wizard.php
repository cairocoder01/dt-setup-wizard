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
    $complete = true;
    $db_value = get_option( 'dt_setup_wizard_progress' );

    if ( $db_value[$step['name']] == 'true' ) {
        return true;
    } elseif ( $step['config']['options'] ) {
        $options = $step['config']['options'];
        foreach ( $options as $option ) {
            $key = $option['key'];
            $value = $option['value'];
            $db_value = get_option( $key );
            if ( $value != $db_value ) {
                $complete = false;
                break;
            }
        }
    } elseif ( $step['config']['plugins'] ) {
        $plugins = $step['config']['plugins'];
        foreach ( $plugins as $plugin ) {
            if ( !is_plugin_activated( $plugin['slug'] ) ) {
                $complete = false;
                break;
            }
        }
    } elseif ( $step['config']['users'] ) {
        $complete = false;
        $users = $step['config']['users'];
        foreach ( $users as $user ) {
            if ( username_exists( $user['username'] ) ) {
                $complete = true;
                break;
            }
        }
    } else {
        $complete = false;
    }

    return $complete;
}

/**
 * Class Disciple_Tools_Setup_Wizard_Tab
 */
class Disciple_Tools_Setup_Wizard_Tab
{
    public $step = 1;
    public $config;
    public $progress;

    public function content() {
        $this->config = get_option( 'dt_setup_wizard_config' );
        $this->progress = get_option( 'dt_setup_wizard_progress' );

        if ( isset( $_GET['step'] ) ) {
            $this->step = intval( sanitize_key( wp_unslash( $_GET['step'] ) ) );
        }

        if ( !is_json_object( $this->config ) || empty( $this->config ) ) { ?>
        <div>
          Setup Wizard has not been configured. Please enter a JSON config option on the Settings tab
          <a href='admin.php?page=disciple_tools_setup_wizard&tab=settings'>here</a>.
        </div>
        <?php } else { ?>
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
      <?php }
    }

    public function main_column() {
        $parsedown = new Parsedown();
        $step_idx = $this->step -1;
        $step = $this->config['steps'][$step_idx];
        $step_config = $step['config'];
        ?>

        <h1><?php echo esc_html( $step['name'] )?></h1>
        <?php echo wp_kses_post( $parsedown->text( $step['description'] ) )?>

        <?php
        if ( $step_config['options'] ){//key, value
            $this->display_options( $step_config['options'], $step );
        } else if ( $step_config['plugins'] ){//key, value
            $this->display_plugins( $step_config['plugins'], $step );
        } else if ( $step_config['users'] ){//key, value
            $this->display_users( $step_config['users'], $step );
        } else {
            $this->display_manual_step( $step );
        }
        ?>
        <?php
    }

    public function right_column() {
        $link = 'admin.php?page=disciple_tools_setup_wizard&tab=wizard&step=';
        ?>
      <h2>Steps:</h2>
      <ol class="wizard-step-progress">
        <?php foreach ( $this->config['steps'] as $key =>$item ):
            $step_status = step_status( $item );
            $key++;
            ?>
        <li>
          <span class="icon <?php echo $step_status ? 'complete' : '' ?>"></span>
          <a href="<?php echo esc_attr( $link ) . esc_html( $key ) ?>"><?php echo esc_html( $item['name'] ) ?></a>
        </li>
      <?php endforeach; ?>
    </ol>
        <?php
    }

    public function navigation( $step_name ) {
        if ( $this->progress[$step_name] == 'true' ) {
            $this->progress[$step_name] = false;
        } else {
            $this->progress[$step_name] = true;
        }
        $prev_link = '#';
        $next_link = '#';
        if ( $this->step > 1 ) {
            $prev_link = 'admin.php?page=disciple_tools_setup_wizard&tab=wizard&step=' . ( $this->step - 1 );
        }
        if ( $this->step < count( $this->config['steps'] ) ) {
            $next_link = 'admin.php?page=disciple_tools_setup_wizard&tab=wizard&step=' . ( $this->step + 1 );
        }
        ?>
    <form onsubmit="onClickMarkComplete(event)" class="step-navigation">
      <a <?php echo $prev_link === '#' ? ' role="link" aria-disabled="true"' : 'href="'.esc_attr( $prev_link ).'"' ?> class="prev">&lt;</a>

      <button
        type="submit"
        class="mark-complete <?php echo $this->progress[$step_name] ? '' : 'complete' ?>"
        name="progressButton"
        value="<?php echo esc_html( json_encode( $this->progress ) )  ?>"
        data-next="<?php echo esc_attr( $next_link ) ?>"
      >
        <?php echo $this->progress[$step_name] ? 'Mark Complete' : '' ?>
      </button>

      <a <?php echo $next_link === '#' ? 'role="link" aria-disabled="true"' : 'href="'.esc_attr( $next_link ).'"' ?> class="next">&gt;</a>
    </form>
        <?php
    }

    public function display_options( $options, $step ) {
        ?>
    <form onsubmit="onClickOptionButton(event)">
      <table class="widefat striped wizard-step wizard-step-options">
        <thead>
          <tr>
            <th>Option Name</th>
            <th>Current</th>
            <th>Suggested</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $options as $option ):
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

                <?php if ( $value == $db_value ): ?>
                <td></td>
                <td>
                    <?php echo 'Done'; ?>
                </td>
              <?php else : ?>
                <td>
                  <?php if ( $value[0] == '{' ): ?>
                    <textarea id="<?php echo esc_html( $key ) ?>input" name=<?php echo esc_html( $key ) ?>><?php echo esc_attr( $value ) ?></textarea>
                  <?php else : ?>
                    <input type="text"id="<?php echo esc_html( $key ) ?>input"  name=<?php echo esc_html( $key ) ?> value="<?php echo esc_html( $value ) ?>" />
                  <?php endif; ?>
                </td>
                <td>
                  <button id="<?php echo esc_html( $key ) ?>" type="submit" name="button" value="<?php echo esc_html( $key )  ?>">
                    Update
                  </button>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" align="right">
              <button type="submit" name="button" value="all">
                Update All
              </button>
            </td>
          </tr>
        </tfoot>
      </table>
    </form>
        <?php $this->navigation( $step['name'] );
    }

    public function display_plugins( $plugins, $step ) {
        ?>
    <form id="plugins" onsubmit="onInstallClick(event)">
      <input type="hidden" name="ajax_nonce" value="<?php echo esc_html( wp_create_nonce( 'updates' ) ) ?>" />
      <table class="widefat striped wizard-step">
        <thead>
          <tr>
            <th colspan="3"><?php echo 'Set Plugins' ?></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ( $plugins as $plugin ):
              $slug = $plugin['slug'];
            ?>
          <tr>
            <td>
              <?php echo esc_html( $slug ); ?>
            </td>

            <?php if ( is_plugin_installed( $slug ) ): ?>

                <?php if ( is_plugin_activated( $slug ) ): ?>
                <td>
                    <?php echo 'Active' ?>
                </td>
              <?php else : ?>
                <td>
                  <input type="hidden" id="<?php echo esc_html( $slug )  ?>hidden" name="plugin" value="<?php echo esc_html( json_encode( $plugin ) )  ?>" />
                  <button type="submit" id="<?php echo esc_html( $slug )  ?>" name="button" value="<?php echo esc_html( json_encode( $plugin ) )  ?>">
                  Activate
                  </button>
                </td>
              <?php endif; ?>
            <?php else : ?>
              <td>
                <input type="hidden" id="<?php echo esc_html( $slug )  ?>hidden" name="plugin" value="<?php echo esc_html( json_encode( $plugin ) )  ?>" />
                <button type="submit" id="<?php echo esc_html( $slug )  ?>" name="button" value="<?php echo esc_html( json_encode( $plugin ) )  ?>">
                Install & Activate
                </button>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
          <td colspan="4" align="right">
            <button type="submit" name="button" value="all">
              Install/Activate All
            </button>
          </td>
        </tr>
        </tfoot>
      </table>
    </form>
        <?php $this->navigation( $step['name'] );
    }
    public function display_users( $users, $step ) {
        ?>
      <form onsubmit="onClickUserButton(event)" class="wizard-step">
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
        <?php foreach ( $users as $user ):
            $username = $user['username'];
            $email = $user['email'];
            $display_name = $user['displayName'];
            $roles = implode( ', ', $user['roles'] );
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
              <?php if ( username_exists( $username ) ): ?>
                  Done
              <?php else : ?>
                <input type="hidden" name="<?php echo esc_html( $username ) ?>" id="<?php echo esc_html( $username )  ?>hidden" value="<?php echo esc_html( json_encode( $user ) ) ?>" />
                <button
                  type="submit"
                  name="button"
                  id="<?php echo esc_html( $username )  ?>"
                  value="<?php echo esc_html( $username )  ?>"
                >
                  Add User
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
          <td colspan="5" align="right">
            <button type="submit" name="button" value="all">
              Add All
            </button>
          </td>
        </tr>
        </tfoot>
      </table>
    </form>
        <?php $this->navigation( $step['name'] );
    }

    public function display_manual_step( $step ) {
        $this->navigation( $step['name'] );
    }
}
?>
