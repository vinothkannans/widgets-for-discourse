<?php

/**
* Plugin Name: Discourse Widgets
* Description: This plugin provides several discourse widgets for wordpress.
* Version: 0.0.1
* Author: Fanium
* License: GPL2
*/
add_action('admin_menu', 'discwid_admin_menu');
function discwid_admin_head() {
  echo '';
}
add_action( 'admin_head', 'discwid_admin_head' );
class discwid_widget extends WP_Widget {
     
    function __construct() {
    	    parent::__construct(
         
        // base ID of the widget
        'discourse_list_pages_widget',
         
        // name of the widget
        __('Discourse Widgets', 'tutsplus' ),
         
        // widget options
        array (
            'description' => __( 'Display widget in the sidebar', 'tutsplus' )
        )
         
    );
    }
     
    function form( $instance ) {
      $defaults = array(
            'title' => '-1',
            'URL' => '-1',
            'Order' => 'Latest'
        );
        $title = $instance[ 'title' ];
        $URL = $instance[ 'URL' ];
        $Order = $instance[ 'Order' ];
         
        // markup for form ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
            <input required class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
            <label for="<?php echo $this->get_field_id( 'URL' ); ?>">URL:</label>
            <input required class="widefat" type="text" id="<?php echo $this->get_field_id( 'URL' ); ?>" name="<?php echo $this->get_field_name( 'URL' ); ?>" value="<?php echo esc_attr( $URL ); ?>">
            <label for="<?php echo $this->get_field_id( 'Order' ); ?>">Order:</label>
            <select name="<?php echo $this->get_field_name( 'Order' ); ?>" id="<?php echo $this->get_field_id( 'Order' ); ?>">
              <option value="Latest" <?php echo ''.($instance['Order']=='Latest'?'selected':''); ?>>Latest</option>
              <option value="Top" <?php echo ''.($instance['Order']=='Top'?'selected':''); ?>>Top</option>
            </select>

        </p>
        <?php
    }
     
    function update( $new_instance, $old_instance ) {    
    $instance = $old_instance;
    if (filter_var($new_instance[ 'URL' ], FILTER_VALIDATE_URL) !== false) {
        $instance[ 'URL' ] = htmlspecialchars(strip_tags( $new_instance[ 'URL' ] ));
    }
    if (preg_match ("/^[a-zA-Z\s]+$/",$new_instance[ 'title' ])) {
      $instance[ 'title' ] = htmlspecialchars(strip_tags( $new_instance[ 'title' ] ));
    }
    if ($new_instance[ 'Order' ] == "Latest" || $new_instance[ 'Order' ] == "Top") {
        $instance[ 'Order' ] = htmlspecialchars(strip_tags( $new_instance[ 'Order' ] ));
    }
        return $instance;   
    }
     
    function widget( $args, $instance ) {

$instance['URL'] = htmlspecialchars($instance['URL']);
$instance['Order'] = htmlspecialchars($instance['Order']);
$instance[ 'title' ] = htmlspecialchars($instance[ 'title' ]);
      $discourseUrl =  "".$instance['URL']."".strtolower($instance['Order']).".json" ;
         $response = wp_remote_get($discourseUrl);
if( is_wp_error( $response ) ) {
   $error_message = $response->get_error_message();
   echo "Something went wrong: $error_message";
} else {
  $body = wp_remote_retrieve_body( $response ) ;
  $data = json_decode($body);
  if( ! empty( $data) ){
  $i=0;
  echo '<div class="widget widgetElement"><h2 class="widgetTitle"><i class="ion-ios-pricetags"></i>'.$instance[ 'title' ].'</h2><div class="widgetContent"><ul>';
  foreach( $data->topic_list->topics as $one ) {
    if ($i<=4) {
      # code...
    echo '<li style="list-style:none;padding: 11px;
    box-shadow: 0 2px 3px rgba(0,0,0,.1);    border: 1px solid #f5f5f5;
    border-bottom: none;margin-bottom: 10px;"><article>';
      echo '<h1 style="font-size:15px"><a href="'.$instance['URL'].'t/'.$one->slug.'">'.$one->title.'</a></h1>';
      echo '<ul style="margin-left:20px;margin-top: 6px;margin-bottom:6px">';
      $j=0;
      foreach ($one->posters as $poster) {
        echo '<li style="list-style:none;float:left;position: relative;left: -'.($j*6).'px;">';
        //echo $poster->user_id;
        foreach ($data->users as $user) {
          if ($poster->user_id == $user->id) {
            echo '<a href="'.$instance['URL'].'u/'.$user->username.'"><img alt="Photo de '.$user->username.'"  style="border-radius:50%;-moz-border-radius:50%;
    -webkit-border-radius:50%;-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.1);
    -moz-box-shadow: 0 1px 2px rgba(0,0,0,.1);
    box-shadow: 0 1px 2px rgba(0,0,0,.1);" width="25" height="25" src="'.$instance['URL'].''.str_replace("{size}", "64", $user->avatar_template).'"/></a>';
          }
        }
        echo '</li>';
        $j++;
      }
      echo '</ul>

      <ul style="color: #999;">
      <li style="list-style:none;float:right;padding:0 5px">Likes : '.$one->like_count.'</li>
      <li style="list-style:none;float:right;padding:0 5px">Views : '.$one->views.'</li>
      </ul>
      <div style="clear:both"></div>';
    echo '</li></article>';
    $i++;
    }
  }
  echo '</ul></div></div>';
}
}
    }
     
}
function discwid_register() {
if(current_user_can('administrator') ) { 

    register_widget( 'discwid_widget' );
 }
}
add_action( 'widgets_init', 'discwid_register' );

?>