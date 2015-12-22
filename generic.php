<?php
/*
Plugin Name: Contributors
Plugin URI:  www.github.com/pushkardravid/contributors
Description: A wordpress plugin to select and display contributors for a post
Version:     1.0.0
Author:      Pushkar Dravid
Author URI:  www.github.com/pushkardravid
License:     GPL2
*/

add_action( 'add_meta_boxes', 'add_custom_box' );

/*
This function is used to add a metabox to our post screen.
*/

    function add_custom_box( $post ) {
        add_meta_box(
            'Meta Box', // ID of the metabox.
            'Contributors', // Meta Box Title.
            'users_meta_box', // The call back function, the function responsible for displying all the users.
            'post', // This metabox will be displayed on all post types.
            'side', // The placement of our contributors meta box, can be normal or side.
            'core' // The priority in which this will be displayed.
        );
}

/*
This is the callback function. This function is called by the metabox to display the list of all the users.
*/

    function users_meta_box($post) {
        wp_nonce_field( 'my_awesome_nonce', 'awesome_nonce' );    
        $checkboxMeta = get_post_meta( $post->ID );
        $all_users = get_users();
        // Array of WP_User objects.
        foreach ( $all_users as $user ) {
?>
       <input type="checkbox" 
        name="<?php echo $user->display_name;?>"
        id="<?php echo $user->display_name;?>" 
        <?php if ( isset ( $checkboxMeta[$user->display_name] ) ){ 
        checked( $checkboxMeta[$user->display_name][0], 'yes' );  }?> />
        <?php echo $user->display_name;?><br />
      
<?php

        } 
    }


    add_action( 'save_post', 'save_users_checkboxes' );

/*
This is function that is responsible for saving the states of the checkboxes and update them accordingly.
*/
    function save_users_checkboxes( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;
        if ( ( isset ( $_POST['my_awesome_nonce'] ) ) && ( ! wp_verify_nonce( $_POST['my_awesome_nonce'], plugin_basename( __FILE__ ) ) ) )
            return;
        if ( ( isset ( $_POST['post_type'] ) ) && ( 'page' == $_POST['post_type'] )  ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }    
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

         $all_users = get_users();
        // Array of WP_User objects.
            foreach ( $all_users as $user ) {
        

        //saves the value of each user
        if( isset( $_POST[ $user->display_name ] ) ) {
            update_post_meta( $post_id, $user->display_name, 'yes' );
        } else {
            update_post_meta( $post_id, $user->display_name, 'no' );
        }

    }

         
}


/*
This function is used to display the outout that is the list of contributors on the front side.
*/
    function display_contributors($content){

        wp_nonce_field( 'my_awesome_nonce', 'awesome_nonce' );    
        $checkboxMeta = get_post_meta( $post->ID );
        $html = '';
        $postid = get_the_id();
        $all_users = get_users();

        $html.= '
        <div 
        style="background-color: #707070;
        box-shadow:2px 5px 12px 2px #333;
        -moz-box-shadow:5px 5px 12px 5px #333;
        -webkit-box-shadow:5px 5px 12px 0px #333;
        float:center;
        width:100%;">
        <h2 style="color:#fff;background-color:#F25C27;padding:12px">Contributors for this post</h2> ';
        $html2 = $html;
        foreach($all_users as $user ){

        global $wpdb;

        $id = $wpdb->get_var("select ID from $wpdb->users where user_login = '$user->display_name'");
        $key = $wpdb->get_var("select meta_value from $wpdb->postmeta where meta_key='$user->display_name' and post_id = '$postid'");
        if($key == 'yes'){
             $html.= '<div style="padding-left:20px;text-decoration:none;color:#fff">'.get_avatar( $id, 32) .'<span>&nbsp &nbsp'.$user->display_name.'</span></div><br>';
        }
    };

        if($html == $html2){
            $html.= "No contributors!";
        }
   
        $html.='</div>';
        $content.= $html;
        return $content;
    }

    add_filter('the_content','display_contributors');

?>