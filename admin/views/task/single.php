<?php
$cpm_active_menu = __( 'Task List', 'cpm' );

require_once CPM_PLUGIN_PATH . '/admin/views/project/header.php';

$task_obj = new CPM_Task();
$list = $task_obj->get_task_list( $tasklist_id );

$error = false;
if ( isset( $_POST['cpm_new_comment'] ) ) {
    $posted = $_POST;

    check_admin_referer( 'cpm_new_message' );

    $text = trim( $posted['cpm_message'] );

    if ( empty( $text ) ) {
        $error = new WP_Error( 'empty_message', __( 'Empty message', 'cpm' ) );
    } else {
        $data = array(
            'text' => $text,
            'file' => $task_obj->upload_file()
        );

        $comment_id = $task_obj->new_comment( $data, $tasklist_id );

        if ( $comment_id ) {
            cpm_show_message( __( 'Comment Added.', 'cpm' ) );
        }
    }
}
?>
<h3 class="cpm-nav-title"><?php _e( 'Task List', 'cpm' ) ?> : <?php echo $list->name; ?></h3>

<div class="cpm-task-list">
    <div class="cpm-list-title">
        <h3 class="list-title cpm-left"><?php echo $list->name; ?></h3>

        <div class="cpm-right">
            <?php
            $complete = $task_obj->get_completeness( $list->id );
            cpm_task_completeness( $complete->total, $complete->done );
            ?>
        </div>
    </div>

    <div class="cpm-clear"></div>

    <p>Due date: <?php echo cpm_show_date( $list->due_date ); ?></p>
    <p><?php echo stripslashes( $list->description ); ?></p>

    <ul class="links">
        <li><a href="<?php echo cpm_url_edit_tasklist( $project_id, $list->id ); ?>">Edit</a></li>
        <li><a href="#">Delete</a></li>
        <li><a href="<?php echo cpm_url_add_task( $project_id, $list->id ); ?>">Add Task</a></li>
        <li><a href="<?php echo cpm_url_single_tasklist( $project_id, $list->id ); ?>">Comment (<?php echo $task_obj->get_comment_count( $list->id ); ?>)</a></li>
    </ul>

    <p><a class="button cpm-hide-tasks" href="#">Hide Tasks</a></p>

    <div class="cpm-tasks">
        <?php
        $tasks = $task_obj->get_tasks( $list->id );
        //var_dump( $tasks );
        if ( $tasks ) {
            foreach ($tasks as $task) {
                //var_dump( $task );
                $class = $task->complete == '0' ? 'open' : 'close';
                ?>
                <div class="cpm-task <?php echo $class; ?>">
                    <div class="task-detail">
                        <?php echo stripslashes( $task->text ); ?>
                    </div>
                    <ul class="cpm-links">
                        <li><a href="<?php echo cpm_edit_task_url( $project_id, $list->id, $task->id ); ?>">Edit</a></li>
                        <li><a href="#" class="cpm-mark-task-delete" data-id="<?php echo esc_attr( $task->id ); ?>">Delete</a></li>
                        <li><a href="<?php echo cpm_single_task_url( $project_id, $list->id, $task->id ); ?>">View</a></li>
                        <?php if ( $task->complete == '0' ) { ?>
                            <li><a href="#" class="cpm-mark-task-complete" data-id="<?php echo esc_attr( $task->id ); ?>">Mark Task as Completed</a></li>
                        <?php } else { ?>
                            <li><a href="#" class="cpm-mark-task-open" data-id="<?php echo esc_attr( $task->id ); ?>">Mark Task as Open</a></li>
                        <?php } ?>
                    </ul>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<?php
if ( is_wp_error( $error ) ) {
    $errors = $error->get_error_messages();
    cpm_show_errors( $errors );
}
?>

<h3>Comments:</h3>

<div class="cpm-comment-wrap">
    <?php
    $comments = $task_obj->get_comments( $tasklist_id );
    if ( $comments ) {
        foreach ($comments as $comment) {
            cpm_show_comment( $comment );
        }
    }
    ?>
</div>
<?php cpm_comment_form( $project_id, $tasklist_id, 'TASK_LIST' ); ?>