<?php

/**
  Plugin Name: AAM User Activity Helper
  Description: Different helpers for AAM User Activity extension
  Version: 1.0
  Author: Vasyl Martyniuk <vasyl@vasyltech.com>
  Author URI: https://vasyltech.com

  -------
  LICENSE: This file is subject to the terms and conditions defined in
  file 'license.txt', which is part of this source package.
 *
 */

/**
 * AAM user activity helper
 *
 * @package AAM
 * @author Vasyl Martyniuk 
 */
class AAM_UserActivity_Helper {

    /**
     * Single instance of itself
     * 
     * @var AAM_UserActivity_Helper 
     * 
     * @access protected
     * @static
     */
    protected static $instance = null;
    
    /**
     * Construct the instance
     * 
     * @return void
     * 
     * @access protected
     */
    protected function __construct() {
        add_filter('aam-user-activity-hooks-filter', array($this, 'getHooks'));
    }
    
    /**
     * Get more hooks
     * 
     * @param array $hooks
     * 
     * @return array
     * 
     * @access public
     */
    public function getHooks($hooks) {
        $filename = dirname(__FILE__) . '/hooks.php';
        
        if (file_exists($filename)) {
            $more = require $filename;
        }
        
        return array_merge($hooks, $more);
    }
    
    /**
     * 
     * @param type $event
     * @return type
     */
    public static function save_post($event) {
        $metadata = unserialize($event['metadata']);
        $label    = self::$instance->getPostTypeLabel($metadata[1]->post_type);
        
        if ($metadata[2]) { //post updated
            $message = sprintf(
                '%s <b>%s</b> <small>(ID: %d)</small> has been updated', 
                $label, 
                $metadata[1]->post_title,
                $metadata[1]->ID
            );
        } else {
             $message = sprintf(
                '%s <b>%s</b> <small>(ID: %d)</small> has been created', 
                $label, 
                $metadata[1]->post_title,
                $metadata[1]->ID
            );
        }
        
        return $message;
    }
    
    /**
     * New user registered hook
     * 
     * @param array $event
     * 
     * @return string
     */
    public static function user_register($event) {
        $metadata = unserialize($event['metadata']);
        $user     = get_user_by('ID', $metadata[0]);
        
        if (is_a($user, 'WP_User')) {
            $message = sprintf(
                'New user <b>%s</b> <small>(ID: %d)</small> was added with role %s', 
                $user->user_login, 
                $user->ID,
                translate_user_role(array_shift($user->roles))
            );
        } else {
            $message = sprintf('New user added with ID %s', $metadata[0]);
        }
        
        return $message;
    }
    
    /**
     * Profile updated
     * 
     * @param array $event
     * 
     * @return string
     */
    public static function profile_update($event) {
        $metadata = unserialize($event['metadata']);
        $user     = get_user_by('ID', $metadata[0]);
        
        if (is_a($user, 'WP_User')) {
            $message = sprintf(
                'User <b>%s</b> <small>(ID: %d)</small> was updated', 
                $user->user_login, 
                $user->ID
            );
        } else {
            $message = sprintf('User with ID %s was updated', $metadata[0]);
        }
        
        return $message;
    }
    
    /**
     * User login
     * 
     * @param array $event
     * 
     * @return string
     */
    public static function wp_login($event) {
        $metadata = unserialize($event['metadata']);
        if (is_a($metadata[1], 'WP_User')) {
            $message = sprintf(
                'User <b>%s</b> <small>(ID: %d)</small> logged in', 
                $metadata[1]->display_name, 
                $metadata[1]->ID
            );
        } else {
            $message = sprintf('User %s logged in', $metadata[0]);
        }
        
        return $message;
    }
    
    /**
     * User logout callback
     * 
     * @return void
     * 
     * @access public
     */
    public static function wp_logout_callback() {
        $user = get_current_user_id();
        
        AAM_UserActivity::getInstance()->save(
                'wp_logout', array($user, get_user_by('ID', $user))
        );
    }
    
    /**
     * User login callback
     * 
     * @return void
     * 
     * @access public
     */
    public static function wp_login_callback($username) {
        $user = get_user_by('login', $username);
        
        AAM_UserActivity::getInstance()->save(
                'wp_login', array($username, $user), ($user ? $user->ID : 0)
        );
    }
    
    /**
     * Decorate event message
     * 
     * Decorate post trashed event message
     * 
     * @param array $event
     * 
     * @return string
     * 
     * @access public
     */
    public static function trashed_post($event) {
        $metadata = unserialize($event['metadata']); //metadata is always serialized
        $post     = get_post($metadata[0]); //the first argument $post_id
        
        if (is_a($post, 'WP_Post')) {
            $message = sprintf(
                '%s <b>%s</b> <small>(ID: %d)</small> was moved to trash', 
                self::$instance->getPostTypeLabel($post->post_type),
                $post->post_title, 
                $post->ID
            );
        } else {
            $message = sprintf('Post with ID %s was moved to trash', $metadata[0]);
        }

        return $message;
    }
    
    /**
     * Decorate event message
     * 
     * Decorate post untrashed event message
     * 
     * @param array $event
     * 
     * @return string
     * 
     * @access public
     */
    public static function untrashed_post($event) {
        $metadata = unserialize($event['metadata']); //metadata is always serialized
        $post     = get_post($metadata[0]); //the first argument $post_id
        
        if (is_a($post, 'WP_Post')) {
            $message = sprintf(
                '%s <b>%s</b> <small>(ID: %d)</small> was restored', 
                self::$instance->getPostTypeLabel($post->post_type),
                $post->post_title, 
                $post->ID
            );
        } else {
            $message = sprintf('Post with ID %s was restored', $metadata[0]);
        }

        return $message;
    }
    
    /**
     * Get post type label
     * 
     * @param type $post_type
     * @return type
     */
    protected function getPostTypeLabel($post_type) {
        $type  = get_post_type_object($post_type);
        $label = 'Post';
        
        if (!empty($type->labels->singular_name)) {
            $label = $type->labels->singular_name;
        }
        
        return $label;
    }
    
    /**
     * Bootstrap the plugin
     * 
     * @return void
     * 
     * @access public
     * @static
     */
    public static function bootstrap() {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
    }

}

if (defined('ABSPATH')) {
    add_action('init', 'AAM_UserActivity_Helper::bootstrap', -2); //!important
}