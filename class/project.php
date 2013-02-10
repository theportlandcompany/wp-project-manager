<?php

/**
 * Description of project
 *
 * @author Tareq Hasan (http://tareq.weDevs.com)
 */
class CPM_Project {

    private static $_instance;

    public function __construct() {
        add_filter( 'init', array($this, 'register_post_type') );
    }

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new CPM_Project();
        }

        return self::$_instance;
    }

    function register_post_type() {
        register_post_type( 'project', array(
            'label' => __( 'Project', 'cpm' ),
            'description' => __( 'project manager post type', 'cpm' ),
            'public' => false,
            'show_in_admin_bar' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_admin_bar' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => array('slug' => ''),
            'query_var' => true,
            'supports' => array('title', 'editor'),
            'labels' => array(
                'name' => __( 'Project', 'cpm' ),
                'singular_name' => __( 'Project', 'cpm' ),
                'menu_name' => __( 'Project', 'cpm' ),
                'add_new' => __( 'Add Project', 'cpm' ),
                'add_new_item' => __( 'Add New Project', 'cpm' ),
                'edit' => __( 'Edit', 'cpm' ),
                'edit_item' => __( 'Edit Project', 'cpm' ),
                'new_item' => __( 'New Project', 'cpm' ),
                'view' => __( 'View Project', 'cpm' ),
                'view_item' => __( 'View Project', 'cpm' ),
                'search_items' => __( 'Search Project', 'cpm' ),
                'not_found' => __( 'No Project Found', 'cpm' ),
                'not_found_in_trash' => __( 'No Project Found in Trash', 'cpm' ),
                'parent' => __( 'Parent Project', 'cpm' ),
            ),
        ) );
    }

    /**
     * Create or edit a a project
     *
     * @param null|int $project_id
     * @return int
     */
    function create( $project_id = 0 ) {
        global $wpdb;
        $posted = $_POST;
        $is_update = ( $project_id ) ? true : false;
        $co_worker = isset( $posted['project_coworker'] ) ? $posted['project_coworker'] : '';

        $data = array(
            'post_title' => $posted['project_name'],
            'post_content' => $posted['project_description'],
            'post_type' => 'project',
            'post_status' => 'publish'
        );

        if ( $is_update ) {
            $data['ID'] = $project_id;
            $project_id = wp_update_post( $data );
        } else {
            $project_id = wp_insert_post( $data );
        }

        if ( $project_id ) {
            $co_worker_old = wp_list_pluck( $this->get_users( $project_id ), 'id' );
            $remove_user = array_diff( $co_worker_old, $co_worker );
            $add_user = array_diff( $co_worker, $co_worker_old );

            if ( $remove_user ) {
                $this->remove_co_worker( $remove_user, $project_id );
            }

            if ( $add_user || !$co_worker ) {
                $this->add_co_worker( $add_user, $project_id );
            }  

            if ( $is_update ) {
                do_action( 'cpm_project_update', $project_id, $data );
            } else {
                do_action( 'cpm_project_new', $project_id, $data );
            }
        }

        return $project_id;
    }

    /**
     * Update a project
     *
     * @param int $project_id
     * @return int
     */
    function update( $project_id ) {
        return $this->create( $project_id );
    }

    /**
     * Delete a project
     *
     * @param int $project_id
     * @param bool $force
     */
    function delete( $project_id, $force = false ) {
        do_action( 'cpm_project_delete', $project_id, $force );

        wp_delete_post( $project_id, $force );
    }

    /**
     * Complete a project
     *
     * @param int $project_id
     */
    function complete( $project_id ) {
        do_action( 'cpm_project_complete', $project_id );

        $data = array(
            'ID' => $project_id,
            'post_status' => 'complete'
        );

        wp_update_post( $data );
    }

    /**
     * Change status of a project
     *
     * @param int $project_id
     * @param string $project_status
     */
    function change_status( $project_id, $project_status ) {
        do_action( 'cpm_project_change_status', $project_id, $project_status );

        $data = array(
            'ID' => $project_id,
            'post_status' => $project_status
        );

        wp_update_post( $data );
    }

    /**
     * Trash a project
     *
     * @param int $project_id
     */
    function trash( $project_id ) {
        do_action( 'cpm_project_trash', $project_id );

        $data = array(
            'ID' => $project_id,
            'post_status' => 'trash'
        );

        wp_update_post( $data );
    }

    /**
     * Get all the projects
     *
     * @param int $count
     * @param string $status
     * @param int $user_id 
     * @return object
     */
    function get_projects( $count = -1, $status = 'publish', $user_id = 1 ) {
        global $wpdb;

        $project_coworkers_table = $wpdb->prefix . 'project_coworkers';
        $sql = "SELECT * FROM $project_coworkers_table";
        $sql .= " INNER JOIN $wpdb->posts ON $project_coworkers_table.project_id = $wpdb->posts.ID";
        $sql .= " WHERE $wpdb->posts.post_type = 'project'";
        $sql .= " AND $wpdb->posts.post_status = '%s'";
        if ( $user_id != 1 ) {
            $sql .= " AND $project_coworkers_table.user_id = '%d'";
        }
        $sql .= " GROUP BY $wpdb->posts.ID";

        $projects = $wpdb->get_results( sprintf( $sql, $status, $user_id ) );

        foreach ( $projects as &$project ) {
            $project->info = $this->get_info( $project->ID );
            $project->users = $this->get_users( $project );
        }

        return $projects;
    }

    /**
     * Get details of the project
     *
     * @param int $project_id
     * @return object
     */
    function get( $project_id ) {
        $project = get_post( $project_id );

        if ( !$project ) {
            return false;
        }

        $project->users = $this->get_users( $project );
        $project->info = $this->get_info( $project_id );

        return $project;
    }

    /**
     * Get project activity
     *
     * @since 0.3.1
     * 
     * @param int $project_id
     * @param array $args
     * @return array
     */
    function get_activity( $project_id, $args = array() ) {
        $defaults = array(
            'order' => 'DESC',
            'offset' => 0,
            'number' => 20
        );

        $args = wp_parse_args( $args, $defaults );
        $args['post_id'] = $project_id;

        return get_comments( apply_filters( 'cpm_activity_args', $args, $project_id ) );
    }

    /**
     * Get project info
     *
     * Gets all the project info such as number of discussion, todolist, todos,
     * comments, files and milestones. These info's are cached for performance
     * improvements.
     *
     * @global object $wpdb
     * @param int $project_id
     * @return stdClass
     */
    function get_info( $project_id ) {
        global $wpdb;

        $ret = wp_cache_get( 'cpm_project_info_' . $project_id );

        if ( false === $ret ) {
            //get discussions
            $sql = "SELECT ID, comment_count FROM $wpdb->posts WHERE `post_type` = '%s' AND `post_status` = 'publish' AND `post_parent` IN (%s);";
            $sql_files = "SELECT COUNT(ID) FROM $wpdb->posts p INNER JOIN $wpdb->postmeta m ON (p.ID = m.post_id) WHERE p.post_type = 'attachment' AND (p.post_status = 'publish' OR p.post_status = 'inherit') AND ( (m.meta_key = '_project' AND CAST(m.meta_value AS CHAR) = '$project_id') )";

            $discussions = $wpdb->get_results( sprintf( $sql, 'message', $project_id ) );
            $todolists = $wpdb->get_results( sprintf( $sql, 'task_list', $project_id ) );
            $milestones = $wpdb->get_results( sprintf( $sql, 'milestone', $project_id ) );
            $todos = $todolists ? $wpdb->get_results( sprintf( $sql, 'task', implode(', ', wp_list_pluck( $todolists, 'ID') ) ) ) : array();
            $files = $wpdb->get_var( $sql_files );

            $discussion_comment = wp_list_pluck( $discussions, 'comment_count' );
            $todolist_comment = wp_list_pluck( $todolists, 'comment_count' );
            $todo_comment = $todolists ? wp_list_pluck( $todos, 'comment_count' ) : array();
            $milestone = wp_list_pluck( $milestones, 'ID' );

            $total_comment = array_sum( $discussion_comment ) + array_sum( $todolist_comment ) + array_sum( $todo_comment );

            $ret = new stdClass();
            $ret->discussion = count( $discussions );
            $ret->todolist = count( $todolists );
            $ret->todos = count( $todos );
            $ret->comments = $total_comment;
            $ret->files = (int) $files;
            $ret->milestone = count( $milestone );

            wp_cache_set( 'cpm_project_info_' . $project_id, $ret );
        }

        return $ret;
    }

    /**
     * Flush a project info cache
     *
     * Some number of queries runs when creating project information.
     * Clears the project information cache when a new activity happens.
     *
     * @since 0.3.1
     * @param int $project_id
     */
    function flush_cache( $project_id ) {
        wp_cache_delete( 'cpm_project_info_' . $project_id );
    }

    /**
     * Get all the users of this project
     *
     * @param int $project_id
     * @param bool $exclude_client
     * @return array user emails with id as index
     */
    function get_users( $project ) {
        global $wpdb;

        if ( is_object( $project ) ) {
            $project_id = $project->ID;
        } else {
            $project_id = $project;
        }

        $project_coworkers_table = $wpdb->prefix . 'project_coworkers';
        $sql = "SELECT user_id FROM $project_coworkers_table";
        $sql .= " WHERE `project_id` = '%d'";

        $user_ids = $wpdb->get_col( sprintf( $sql, $project_id ) );
        $mail = array();

        //insert the mail addresses in array, user id as key
        if ( $user_ids ) {
            foreach ($user_ids as $id) {
                $user = get_user_by( 'id', $id );

                if ( !is_wp_error( $user ) && $user ) {
                    $mail[$id] = array(
                        'id' => $user->ID,
                        'email' => $user->user_email,
                        'name' => $user->display_name
                    );
                }
            }
        }

        return $mail;
    }

    /**
     * Add co worker(s)
     *
     * @since 0.3.1
     * @param array $co_worker
     * @param int $project_id
     */
    function add_co_worker( $co_worker, $project_id ) {
        global $wpdb;
        $project_coworkers_table = $wpdb->prefix . 'project_coworkers';
        $sql = "INSERT INTO $project_coworkers_table ( project_id, user_id ) VALUES ( %d, %d ) ";

        // Always add Admin if it's not present
        if ( !in_array( 1, $co_worker ) ) {
            $co_worker[] = 1;
        }

        foreach ( $co_worker as $user_id ) {
            $wpdb->query( $wpdb->prepare( $sql, $project_id, $user_id ) );
        }
    }

    /**
     * Remove co worker(s)
     *
     * @since 0.3.1
     * @param array $co_worker
     * @param int $project_id
     */
    function remove_co_worker( $co_worker, $project_id ) {
        global $wpdb;
        $project_coworkers_table = $wpdb->prefix . 'project_coworkers';
        $sql = "DELETE FROM $project_coworkers_table WHERE `project_id` = '%d' AND `user_id` = '%d'";

        // Prevent Admin from being removed
        if ( in_array( 1, $co_worker ) ) {
            $co_worker = array_diff( $co_worker, array( 1 ) );
        }

        foreach ( $co_worker as $user_id ) {
            $wpdb->query( $wpdb->prepare( $sql, $project_id, $user_id ) );
        }
    }

    /**
     * Generates navigational menu for a project
     *
     * @param int $project_id
     * @return array
     */
    function nav_links( $project_id ) {
        $links = array(
            cpm_url_project_details( $project_id ) => __( 'Activity', 'cpm' ),
            cpm_url_message_index( $project_id ) => __( 'Messages', 'cpm' ),
            cpm_url_tasklist_index( $project_id ) => __( 'To-do List', 'cpm' ),
            cpm_url_milestone_index( $project_id ) => __( 'Milestones', 'cpm' ),
            cpm_url_file_index( $project_id ) => __( 'Files', 'cpm' ),
        );

        return apply_filters( 'cpm_project_nav_links', $links );
    }

    /**
     * Prints navigation menu for a project
     *
     * @param int $project_id
     * @param string $active
     * @return string
     */
    function nav_menu( $project_id, $active = '' ) {
        $links = $this->nav_links( $project_id );

        $menu = array();
        foreach ($links as $url => $label) {
            if ( $active == $label ) {
                $menu[] = sprintf( '<a href="%1$s" class="nav-tab nav-tab-active" title="%2$s">%2$s</a>', $url, $label );
            } else {
                $menu[] = sprintf( '<a href="%1$s" class="nav-tab" title="%2$s">%2$s</a>', $url, $label );
            }
        }

        return implode( "\n", $menu );
    }

    /**
     * Generates status navigation menu for projects
     *
     * @param int $project_id
     * @return array
     */
    function status_nav_links() {
        $links = array(
            cpm_url_projects_with_status( 'publish' ) => __( 'Published', 'cpm' ),
            cpm_url_projects_with_status( 'complete' ) => __( 'Completed', 'cpm' ),
            cpm_url_projects_with_status( 'draft' ) => __( 'Drafts', 'cpm' ),
            cpm_url_projects_with_status( 'pending' ) => __( 'Pending', 'cpm' ),
            cpm_url_projects_with_status( 'archive' ) => __( 'Archived', 'cpm' ),
            cpm_url_projects_with_status( 'trash' ) => __( 'Trash', 'cpm' ),
        );

        return apply_filters( 'cpm_project_status_nav_links', $links );
    }

    /**
     * Prints status navigation menu for projects
     *
     * @param int $project_id
     * @param string $active
     * @return string
     */
    function status_nav_menu( $active = '' ) {
        $links = $this->status_nav_links();

        $menu = array();
        foreach ($links as $url => $label) {
            if ( $active == $label ) {
                $menu[] = sprintf( '<a href="%1$s" class="nav-tab nav-tab-active" title="%2$s">%2$s</a>', $url, $label );
            } else {
                $menu[] = sprintf( '<a href="%1$s" class="nav-tab" title="%2$s">%2$s</a>', $url, $label );
            }
        }

        return implode( "\n", $menu );
    }

    /**
     * Checks against admin rights
     *
     * editor and above level has admin rights by default
     *
     * @return bool
     */
    function has_admin_rights() {
        $admin_right = apply_filters( 'cpm_admin_right', 'delete_pages' );

        if ( current_user_can( $admin_right ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user has permission on a project
     *
     * Admins and editors can access all projects.
     *
     * @param object $project
     * @return bool
     */
    function has_permission( $project ) {
        if ( $this->has_admin_rights() ) {
            return true;
        }

        //user id found in the users array
        if ( array_key_exists( get_current_user_id(), $project->users ) ) {
            return true;
        }

        return false;
    }

}
