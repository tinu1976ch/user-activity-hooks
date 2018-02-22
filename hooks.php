<?php

return array(
    'user_register' => array(
        'messageCallback' => 'AAM_UserActivity_Helper::user_register'
    ),
    'profile_update' => array(
        'messageCallback' => 'AAM_UserActivity_Helper::profile_update'
    ),
    'save_post' => array(
        'messageCallback' => 'AAM_UserActivity_Helper::save_post'
    ),
    'trashed_post' => array(
        'messageCallback' => 'AAM_UserActivity_Helper::trashed_post'
    ),
    'untrashed_post' => array(
        'messageCallback' => 'AAM_UserActivity_Helper::untrashed_post'
    ),
    'delete_post' => 'Post with ID <b>{$0}</b> was deleted',
    'wp_login' => array(
        'hookCallback'    => 'AAM_UserActivity_Helper::wp_login_callback',
        'messageCallback' => 'AAM_UserActivity_Helper::wp_login'
    ),
    'wp_logout' => array(
        'hookCallback' => 'AAM_UserActivity_Helper::wp_logout_callback',
        'message'      => 'User <b>{$1.display_name}</b> <small>(ID: {$0})</small> logged out'
    )
);
