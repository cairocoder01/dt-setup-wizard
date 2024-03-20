<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
function is_json_object( $string ) {
    json_decode( $string );
    return json_last_error() === JSON_ERROR_NONE;
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
                <?php $this->main_column() ?>
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
    public function main_column() {
        $parsedown = new Parsedown();
        if ( isset( $_GET['step'] ) ) {
            $step = sanitize_key( wp_unslash( $_GET['step'] ) );
        } else {
            $step = '1';
        }
        $setting = get_option( 'dt_setup_wizard_config' );
        $config = json_decode( $setting );
        ?>
        <!-- Box -->
        <?php //foreach($config->steps as $key=>$item)
                  //{
                    //$key++;
        ?>
        <?php echo esc_html( $config->steps[$step -1]->name )?>
        <?php echo wp_kses_post( $parsedown->text( $config->steps[$step -1]->description ) )?>
        <?php if ( property_exists( $config->steps[$step -1]->config, 'options' ) ){//key, value
            $this->load_options( $config->steps[$step -1]->config->options, $step );
        }
        ?>
    <br>
        <?php
                  //} ?>
        <!-- End Box -->
        <?php
    }
    public function right_column( $setting ) {
        $config = json_decode( $setting );
        ?>
  <!-- Box -->
    Steps:
        <?php
        $link = 'admin.php?page=disciple_tools_setup_wizard&tab=wizard&step=';
        ?>
    <ol>
        <?php
        foreach ( $config->steps as $key =>$item )
        {
            $key++;
            ?>
                <li>
                  <a href="<?php echo esc_attr( $link ) . esc_html( $key ) ?>"><?php echo esc_html( $item->name ) ?></a>
                  <a href="<?php echo esc_attr( $link ) . esc_html( $key ) ?>"><?php echo esc_html( $item->name ) ?></a>
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
                <th colspan="3"><?php echo 'Set Options' ?></th>
              </tr>
            </thead>
            <tbody>
            <?php
            foreach ( $options as $option ){
                $key = $option->key;
                $value = $option->value;
                $db_value = get_option( $key );
                if ( gettype( $value ) == 'object' ){
                    $value = json_encode( $value );
                }
                if ( gettype( $db_value ) == 'object' ){
                    $db_value = json_encode( $db_value );
                }
                ?>
              <tr>
                <td>
                <?php echo esc_html( $key ); ?>
                </td>
                <td>
                <?php echo esc_html( $value ); ?>
                </td>
                <td>
                <?php
                if ( $value == $db_value ){
                    echo 'Done!';
                } else {
                    ?>
                  <input type="text" name=<?php echo esc_html( $key ) ?> value="<?php echo esc_html( $value ) ?>" />
                  <button type="submit" name="button" value="<?php echo esc_html( $key )  ?>">
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
}
?>