<?php

/**
 * Task list manager class
 *
 * @author Tareq Hasan
 */
class CPM_Task {

    private static $_instance;

    public function __construct() {
        add_filter( 'init', array($this, 'register_post_type') );
    }

    public static function getInstance() {
        if ( !self::$_instance ) {
            self::$_instance = new CPM_Task();
        }

        return self::$_instance;
    }

    function register_post_type() {
        register_post_type( 'task_list', array(
            'label' => __( 'Task List', 'cpm' ),
            'description' => __( 'Task List', 'cpm' ),
            'public' => false,
            'show_in_admin_bar' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_admin_bar' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => array('slug' => 'task-list'),
            'query_var' => true,
            'supports' => array('title', 'editor'),
            'labels' => array(
                'name' => __( 'Task List', 'cpm' ),
                'singular_name' => __( 'Task List', 'cpm' ),
                'menu_name' => __( 'Task List', 'cpm' ),
                'add_new' => __( 'Add Task List', 'cpm' ),
                'add_new_item' => __( 'Add New Task List', 'cpm' ),
                'edit' => __( 'Edit', 'cpm' ),
                'edit_item' => __( 'Edit Task List', 'cpm' ),
                'new_item' => __( 'New Task List', 'cpm' ),
                'view' => __( 'View Task List', 'cpm' ),
                'view_item' => __( 'View Task List', 'cpm' ),
                'search_items' => __( 'Search Task List', 'cpm' ),
                'not_found' => __( 'No Task List Found', 'cpm' ),
                'not_found_in_trash' => __( 'No Task List Found in Trash', 'cpm' ),
                'parent' => __( 'Parent Task List', 'cpm' ),
            ),
        ) );

        register_post_type( 'task', array(
            'label' => __( 'Task', 'cpm' ),
            'description' => __( 'Tasks', 'cpm' ),
            'public' => false,
            'show_in_admin_bar' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_admin_bar' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => array('slug' => 'task'),
            'query_var' => true,
            'supports' => array('title', 'editor'),
            'labels' => array(
                'name' => __( 'Tasks', 'cpm' ),
                'singular_name' => __( 'Task', 'cpm' ),
                'menu_name' => __( 'Task', 'cpm' ),
                'add_new' => __( 'Add Task', 'cpm' ),
                'add_new_item' => __( 'Add New Task', 'cpm' ),
                'edit' => __( 'Edit', 'cpm' ),
                'edit_item' => __( 'Edit Task', 'cpm' ),
                'new_item' => __( 'New Task', 'cpm' ),
                'view' => __( 'View Task', 'cpm' ),
                'view_item' => __( 'View Task', 'cpm' ),
                'search_items' => __( 'Search Task', 'cpm' ),
                'not_found' => __( 'No Task Found', 'cpm' ),
                'not_found_in_trash' => __( 'No Task Found in Trash', 'cpm' ),
                'parent' => __( 'Parent Task', 'cpm' ),
            ),
        ) );
    }

    /**
     * Add a new task list
     *
     * @param int $project_id project id
     * @param int $list_id list id for update purpose
     * @return int task list id
     */
    function add_list( $project_id, $list_id = 0 ) {
        $postdata = $_POST;
        $is_update = $list_id ? true : false;

        $data = array(
            'post_parent' => $project_id,
            'post_title' => $postdata['tasklist_name'],
            'post_content' => $postdata['tasklist_detail'],
            'post_type' => 'task_list',
            'post_status' => 'publish'
        );

        if ( $list_id ) {
            $data['ID'] = $list_id;
            $list_id = wp_update_post( $data );
        } else {
            $list_id = wp_insert_post( $data );
        }

        if ( $list_id ) {
            update_post_meta( $list_id, '_milestone', $postdata['tasklist_milestone'] );

            if ( $is_update ) {
                do_action( 'cpm_tasklist_update', $list_id, $project_id, $data );
            } else {
                do_action( 'cpm_tasklist_new', $list_id, $project_id, $data );
            }
        }

        return $list_id;
    }

    /**
     * Update a task list
     *
     * @param int $project_id
     * @param int $list_id
     * @return int list id
     */
    function update_list( $project_id, $list_id ) {
        return $this->add_list( $project_id, $list_id );
    }

    /**
     * Add a single task
     *
     * @param int $list_id task list id
     * @return int $task_id task id for update purpose
     */
    function add_task( $list_id, $task_id = 0 ) {
        $postdata = $_POST;
        $files = isset( $postdata['cpm_attachment'] ) ? $postdata['cpm_attachment'] : array();
        $is_update = $task_id ? true : false;

        $content = trim( $postdata['task_text'] );
        $assigned = $postdata['task_assign'];
        $due = empty( $postdata['task_due'] ) ? '' : cpm_date2mysql( $postdata['task_due'] );

        $data = array(
            'post_parent' => $list_id,
            'post_title' => trim( substr( $content, 0, 40 ) ), //first 40 character
            'post_content' => $content,
            'post_type' => 'task',
            'post_status' => 'publish'
        );

        $data = apply_filters( 'cpm_task_params', $data );

        if ( $task_id ) {
            $data['ID'] = $task_id;
            $task_id = wp_update_post( $data );
        } else {
            $task_id = wp_insert_post( $data );
        }

        if ( $task_id ) {
            update_post_meta( $task_id, '_assigned', $assigned );
            update_post_meta( $task_id, '_due', $due );

            //initially mark as uncomplete
            if ( !$is_update ) {
                update_post_meta( $task_id, '_completed', 0 );
            }

            //if there is any file, update the object reference
            if ( count( $files ) > 0 ) {
                $comment_obj = CPM_Comment::getInstance();

                foreach ($files as $file_id) {
                    $comment_obj->associate_file( $file_id, $task_id );
                }
            }

            if ( $is_update ) {
                do_action( 'cpm_task_update', $list_id, $task_id, $data );
            } else {
                do_action( 'cpm_task_new', $list_id, $task_id, $data );
            }
        }

        return $task_id;
    }

    /**
     * Update a single task
     *
     * @param int $list_id
     * @param int $task_id
     * @return int task id
     */
    function update_task( $list_id, $task_id ) {
        return $this->add_task( $list_id, $task_id );
    }

    /**
     * Update a tasks priority
     *
     * @param array $cur_tasks
     * * @param int $user_id
     * @param string $direction
     * @return boolean true/false
     */
    function update_tasks_priority( $cur_tasks, $user_id, $direction = '' ) {
        $flipped = array_flip( $cur_tasks );

        $old_tasks = $this->get_tasks_by_priority( $user_id );
        $old_task_ids = array();

        foreach( $old_tasks as $task ) {
            $old_task_ids[] = $task->ID;
        }

        if ( $direction == 'to' ) { // dragging to priority metabox
            foreach( $cur_tasks as $prio => $id ) {
                if ( !in_array($id, array_values($old_task_ids)) ) {
                    update_post_meta( $id, '_priority', $prio );
                }
            }
        } elseif ( $direction == 'from' ) { // dragging from priority metabox
            foreach( $old_task_ids as $old_task_id ) {
                if ( !in_array($old_task_id, array_values($cur_tasks)) )
                    delete_post_meta( $old_task_id, '_priority' );
            }
        } else { // dragging from and to itself -- just sorting
            foreach( $cur_tasks as $prio => $id ) {
                update_post_meta( $id, '_priority', $prio );
            }
        }

        return true;
    }

    /**
     * Get all task list by project
     *
     * @param int $project_id
     * @return object object array of the result set
     */
    function get_task_lists( $project_id ) {
        $args = array(
            'post_type' => 'task_list',
            'numberposts' => -1,
            'order' => 'ASC',
            'post_parent' => $project_id
        );

        $lists = get_posts( $args );
        foreach ($lists as $list) {
            $this->set_list_meta( $list );
        }

        return $lists;
    }

    /**
     * Get a single task list by a task list id
     *
     * @param int $list_id
     * @return object object array of the result set
     */
    function get_task_list( $list_id ) {
        $task_list = get_post( $list_id );
        $this->set_list_meta( $task_list );

        return $task_list;
    }

    /**
     * Get tasks based on user
     *
     * @since 0.3.1.tpc-0.1
     * @param int $user_id
     * @return object object array of the result set
     */
    function get_tasks_by_user( $user_id ) {
        global $wpdb;

        $sql = "SELECT `ID`, `post_content` FROM $wpdb->posts";
        $sql .= " INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id";
        $sql .= " WHERE $wpdb->posts.post_type = 'task'";
        $sql .= " AND $wpdb->posts.post_parent IN ( SELECT `ID` FROM $wpdb->posts WHERE `post_type` = 'task_list' AND `post_parent` IN ( SELECT `ID` FROM $wpdb->posts WHERE `post_status` = 'publish' AND `post_type` = 'project' ) )";
        $sql .= " AND $wpdb->postmeta.post_id NOT IN ( SELECT `post_id` FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '_priority' )";
        $sql .= " AND $wpdb->postmeta.post_id IN ( SELECT `post_id` FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '_completed' AND $wpdb->postmeta.meta_value = '0' )";
        $sql .= " AND $wpdb->postmeta.post_id IN ( SELECT `post_id` FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '_assigned' AND $wpdb->postmeta.meta_value = '%s' )";
        $sql .= " AND $wpdb->postmeta.meta_key = '_due'";
        $sql .= " GROUP BY $wpdb->posts.ID";
        $sql .= " ORDER BY CASE WHEN CAST( $wpdb->postmeta.meta_value AS DATE ) IS NULL THEN 1 ELSE 0 END, CAST( $wpdb->postmeta.meta_value AS DATE ) ASC, $wpdb->posts.ID ASC";
        

        $tasks = $wpdb->get_results( sprintf( $sql, $user_id ) );

        return $tasks;
    }

    /**
     * Get tasks based on priority
     *
     * @since 0.3.1.tpc-0.1
     * @param int $user_id
     * @return object object array of the result set
     */
    function get_tasks_by_priority( $user_id ) {
        global $wpdb;

        $sql = "SELECT `ID`, `post_content` FROM $wpdb->posts";
        $sql .= " INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id";
        $sql .= " WHERE $wpdb->posts.post_type = 'task'";
        $sql .= " AND $wpdb->posts.post_parent IN ( SELECT `ID` FROM $wpdb->posts WHERE `post_type` = 'task_list' AND `post_parent` IN ( SELECT `ID` FROM $wpdb->posts WHERE `post_status` = 'publish' AND `post_type` = 'project' ) )";
        $sql .= " AND $wpdb->postmeta.post_id IN ( SELECT `post_id` FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '_completed' AND $wpdb->postmeta.meta_value = '0' )";
        $sql .= " AND $wpdb->postmeta.post_id IN ( SELECT `post_id` FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '_assigned' AND $wpdb->postmeta.meta_value = '%s' )";
        $sql .= " AND $wpdb->postmeta.meta_key = '_priority'";
        $sql .= " GROUP BY $wpdb->posts.ID";
        $sql .= " ORDER BY CAST( $wpdb->postmeta.meta_value AS UNSIGNED ) ASC";

        $tasks = $wpdb->get_results( sprintf( $sql, $user_id ) );

        return $tasks;
    }

    /**
     * Set meta info for a task list
     *
     * @param object $task_list
     */
    function set_list_meta( &$task_list ) {
        $task_list->due_date = get_post_meta( $task_list->ID, '_due', true );
        $task_list->milestone = get_post_meta( $task_list->ID, '_milestone', true );
        $task_list->privacy = get_post_meta( $task_list->ID, '_privacy', true );
        $task_list->priority = get_post_meta( $task_list->ID, '_priority', true );
    }

    /**
     * Get all tasks based on a task list
     *
     * @param int $list_id
     * @param int $user_id
     * @return object object array of the result set
     */
    function get_tasks( $list_id, $user_id = 0 ) {
        global $wpdb;

        $sql = "SELECT * FROM $wpdb->posts";
        $sql .= " INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id";
        $sql .= " WHERE $wpdb->posts.post_type = 'task'";
        $sql .= " AND $wpdb->posts.post_parent IN ( SELECT `ID` FROM $wpdb->posts WHERE `post_type` = 'task_list' AND `ID` = '%s' )";
        if ( !current_user_can( 'activate_plugins' ) && $user_id ) {
            $sql .= " AND $wpdb->postmeta.post_id IN ( SELECT `post_id` FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key = '_assigned' AND $wpdb->postmeta.meta_value = '%s' )";
        }
        $sql .= " GROUP BY $wpdb->posts.ID";

        if ( !current_user_can( 'activate_plugins' ) ) {
            $tasks = $wpdb->get_results( sprintf( $sql, $list_id, $user_id ) );
        } else {
            $tasks = $wpdb->get_results( sprintf( $sql, $list_id ) );
        }

        foreach ($tasks as $key => $task) {
            $this->set_task_meta( $task );
        }

        return $tasks;
    }

    /**
     * Set all the meta values to a single task
     *
     * @param object $task
     */
    function set_task_meta( &$task ) {
        $task->completed = get_post_meta( $task->ID, '_completed', true );
        $task->completed_by = get_post_meta( $task->ID, '_completed_by', true );
        $task->completed_on = get_post_meta( $task->ID, '_completed_on', true );
        $task->assigned_to = get_post_meta( $task->ID, '_assigned', true );
        $task->due_date = get_post_meta( $task->ID, '_due', true );
    }

    /**
     * Get all tasks based on a milestone
     *
     * @param int $list_id
     * @return object object array of the result set
     */
    function get_tasklist_by_milestone( $milestone_id ) {
        $args = array(
            'post_type' => 'task_list',
            'meta_key' => '_milestone',
            'meta_value' => $milestone_id
        );

        $tasklists = get_posts( $args );
        foreach ($tasklists as $list) {
            $this->set_list_meta( $list );
        }

        return $tasklists;
    }

    /**
     * Get a single task detail
     *
     * @param int $task_id
     * @return object object array of the result set
     */
    function get_task( $task_id ) {
        $task = get_post( $task_id );
        $this->set_task_meta( $task );

        return $task;
    }

    /**
     * Get all comment on a task list
     *
     * @param int $list_id
     * @return object object array of the result set
     */
    function get_comments( $list_id ) {
        return CPM_Comment::getInstance()->get_comments( $list_id );
    }

    /**
     * Get all comments on a single task
     *
     * @param int $task_id
     * @return object object array of the result set
     */
    function get_task_comments( $task_id ) {
        return CPM_Comment::getInstance()->get_comments( $task_id );
    }

    /**
     * Mark a task as complete
     *
     * @param int $task_id task id
     */
    function mark_complete( $task_id ) {
        update_post_meta( $task_id, '_completed', 1 );
        update_post_meta( $task_id, '_completed_by', get_current_user_id() );
        update_post_meta( $task_id, '_completed_on', current_time( 'mysql' ) );

        do_action( 'cpm_task_complete', $task_id );
    }

    /**
     * Mark a task as uncomplete/open
     *
     * @param int $task_id task id
     */
    function mark_open( $task_id ) {
        update_post_meta( $task_id, '_completed', 0 );
        update_post_meta( $task_id, '_completed_on', current_time( 'mysql' ) );

        do_action( 'cpm_task_open', $task_id );
    }

    /**
     * Delete a single task
     *
     * @param int $task_id
     * @param bool $force
     */
    function delete_task( $task_id, $force = false ) {
        do_action( 'cpm_task_delete', $task_id, $force );

        wp_delete_post( $task_id, $force );
    }

    /**
     * Delete a task list
     *
     * @param int $list_id
     * @param bool $force
     */
    function delete_list( $list_id, $force = false ) {
        do_action( 'cpm_tasklist_delete', $list_id, $force );

        wp_delete_post( $list_id, $force );
    }

    /**
     * Get the overall completeness for a task list
     *
     * @param int $list_id
     * @return array
     */
    function get_completeness( $list_id ) {
        $tasks = $this->get_tasks( $list_id );

        return array(
            'total' => count( $tasks ),
            'completed' => array_sum( wp_list_pluck( $tasks, 'completed' ) )
        );
    }

}
