<?php
/*
Plugin Name: Like App.
Plugin URI: https://github.com/kerimlcr/Like-App
Description: ~
Version: 1.1.1
Author: Kerim Ölçer
Author URI: https://github.com/kerimlcr/
*/


class LikeApp{

    function __construct(){
        add_filter("the_content", array(&$this, 'like_app_vote_content'));
        add_action('wp_enqueue_scripts', array(&$this, 'scripts'));
      	add_action('admin_init', array(&$this, 'like_app_admin_init'));
        add_action('publish_post', array(&$this, 'like_app_post_meta'));
        add_action('wp_ajax_like-app-main', array(&$this, 'like_app_main'));
        add_action('wp_ajax_nopriv_like-app-main', array(&$this, 'like_app_main'));
        add_action('widgets_init', create_function('', 'register_widget("LikeApp_Widget_Likes");'));
        add_action('widgets_init', create_function('', 'register_widget("LikeApp_Widget_Tags");'));
    }

    function like_app_admin_init(){
        register_setting( 'like-app', 'like_app_settings' );
    }

    function like_app_post_meta($post_id){
        if(!is_numeric($post_id)) return;
        add_post_meta( $post_id, 'like_app_vote', 0, true );
    }

    function like_app_vote_content($content){
        global $post;
        $active = '';
        if(isset($_COOKIE['like_app-'.$post->ID])){
            $active = 'active';
        }
        $content .= '<p class="vote-submit" id="vote-submit-'.$active.'" name="'.$post->ID.'"><i id="fa-'.$post->ID.'" class="fa fa-thumbs-up '.$active.'"></i></p>';
        $content .= $this->like_app_main($post->ID);
        return $content;

    }

    function like_app_main($post_id, $action='get'){

        if(isset($_POST['post_id'])){
            $action = $_POST['x'];
            $post_id = $_POST['post_id'];
        }

        switch ($action) {
            case 'get':
                $count = get_post_meta($post_id, 'like_app_vote', true);
                if($count==""){
                    return '<span class="like-app-count-'.$post_id.'">0</span>';
                }
                return '<span class="like-app-count-'.$post_id.'">'. $count .'</span>';
                break;

            case 'update':
                $count = get_post_meta($post_id, 'like_app_vote', true);
                if(isset($_COOKIE['like_app-'.$post_id])) return $count;
                $count++;
                update_post_meta( $post_id,'like_app_vote', $count);
                setcookie('like_app-'.$post_id, $post_id, time()*5, '/');
                return '<span class="like-app-count-'.$post_id.'">'. $count .'</span>';
                break;

            case 'dis_update':
                $count = get_post_meta($post_id, 'like_app_vote', true);
                $count--;
                unset($_COOKIE['like_app-'.$post_id]);
                setcookie('like_app-'.$post_id, null, -1, '/');
                update_post_meta( $post_id,'like_app_vote', $count);
                return '<span class="like-app-count-'.$post_id.'">'. $count .'</span>';
                break;
        }
    }

    function scripts(){
        wp_enqueue_script( 'like-app-like', plugins_url( '/scripts/myScript.js', __FILE__ ), array('jquery') );
        wp_enqueue_script( 'datatable', plugins_url( '/scripts/datatable.js', __FILE__ ), array('jquery') );
        wp_enqueue_style( 'like-css', plugins_url( '/styles/button.css', __FILE__ ) );
        wp_enqueue_style('Font_Awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
        wp_enqueue_style('datatable-css', 'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css');
        wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.3.1.js');
        wp_enqueue_script('data-table-js', 'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js');
        wp_localize_script( 'like-app-like', 'like_app', array('ajaxurl' => admin_url('admin-ajax.php')) );
    }
}

global $LikeApp;
$LikeApp = new LikeApp();

class LikeApp_Widget_Likes extends WP_Widget {

	public function __construct() {
		$widget_ops = array(
			'classname' => 'LikeApp Likes',
			'description' => 'En çok beğeni alan yazılar',
		);
		parent::__construct( 'like_app_widget_likes', 'En çok beğenilen yazılar', $widget_ops );
	}

	public function widget( $args, $instance ) {
		extract($args);
        $title = apply_filters('widget_title_likes', $instance['title']);
        $posts = $instance['posts'];

        echo $before_widget;
        if( !empty( $title ) ) echo $before_title . $title . $after_title;
        $likes_posts_args = array(
			'numberposts' => $posts,
            'orderby' => 'meta_value_num',
			'order' => 'DESC',
            'meta_key' => 'like_app_vote',
			'post_type' => 'post',
			'post_status' => 'publish'
		);
        $likes_posts = get_posts($likes_posts_args);
        foreach( $likes_posts as $likes_post ) {
			//ip
			$count =get_post_meta( $likes_post->ID, 'like_app_vote', true);
			$count_output = " <span class='like-app-count'>($count)</span>";

			echo '<li><a href="' . get_permalink($likes_post->ID) . '">' . get_the_title($likes_post->ID) . '</a>' . $count_output . '</li>';
		}
        echo '</ul>';

		echo $after_widget;
	}

	public function form( $instance ) {
        $instance = wp_parse_args($instance);
        $default = array(
            'title' => 'En çok beğenilenler',
            'posts' => 10,
        );

        $instance = wp_parse_args($instance, $default);

        $title = $instance['title'];
        $posts = $instance['posts'];
        ?>
        <p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Gösterilecek yazı sayısı:'); ?></label>
			<input id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo $posts; ?>" size="3" />
		</p>
        <?php

	}

	public function update( $new_instance, $old_instance ) {
        $old_instance['title'] = strip_tags($new_instance['title']);
        $old_instance['posts'] = strip_tags($new_instance['posts']);
        return $old_instance;
	}
}


class LikeApp_Widget_Tags extends WP_Widget {

    public function __construct() {
		$widget_ops = array(
			'classname' => 'LikeApp Likes',
			'description' => 'En çok beğenilen etiketler.',
		);
		parent::__construct( 'like_app_widget_tags', 'En çok beğenilen etiketler', $widget_ops );
	}

	public function widget( $args, $instance ) {
        extract($args);
        $title = apply_filters('widget_title_tag', $instance['title']);

        global $wpdb;
		$tablename = $wpdb->prefix.'posts';
		$query = "select * from $tablename where post_type='post' and post_status!='trash'";
		$posts = $wpdb->get_results($query);
		$tags_array = array();
        foreach ($posts as $post) {
            if(get_the_tags($post->ID)){
                foreach (get_the_tags($post->ID) as $key ) {
                    $array[$key->slug] = $post->ID;
                }
            }
        }

        if(empty($array)) return;
        foreach (array_keys($array) as $key) {
            $tags_array[$key] = 0;
        }
        foreach ($posts as $key) {
            $tag_arr = get_the_tags($key->ID);
            if(is_array($tag_arr) || is_object($tag_arr)){
                foreach ($tag_arr as $slug ) {
                    if(in_array($slug->slug, $tags_array)){
                        $count = get_post_meta($key->ID, 'like_app_vote', true);
                        // if(!is_numeric($count)){
                        //     continue;
                        // }
                        $tags_array[$slug->slug] =  $tags_array[$slug->slug] +$count ;
                    }
                }
            }
        }
        echo '<table id="tags_table" class="display" style="width:100%"><thead><th>Tag</th><th>Like</th></thead><tbody>';
        foreach (array_keys($tags_array) as $key ) {
            echo "<tr><td>$key</td> <td>$tags_array[$key]</td></tr>";
        }
        echo '</tbody></table>';
        echo $after_widget;
	}

	public function form( $instance ) {
        $instance = wp_parse_args($instance);

        $default = array(
            'title' => 'En çok beğenilen etiketler',
        );

        $instance = wp_parse_args($instance, $default);

        $title = $instance['title'];
        ?>
        <p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
        <?php
	}

	public function update( $new_instance, $old_instance ) {
        $old_instance['title'] = strip_tags($new_instance['title']);
        return $old_instance;
	}
}
