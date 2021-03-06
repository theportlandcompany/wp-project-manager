<?php
$task_obj = CPM_Task::getInstance();
$list = $task_obj->get_task_list( $tasklist_id );

cpm_get_header( __( 'Task List', 'cpm' ), $project_id );
?>
<div class="cpm-nav-title">
    <h3><?php _e( 'Task List', 'cpm' ) ?> : <?php echo get_the_title( $list->ID ); ?></h3>
    
    <ul class="cpm-todolists">
        <?php if ( $list ) { ?>
            <li id="cpm-list-<?php echo $list->ID; ?>"><?php echo cpm_task_list_html( $list, $project_id ); ?></li>
        <?php } ?>
    </ul>
    
    <h3 class="cpm-comment-title"><?php _e( 'Discuss this task list', 'cpm' ); ?></h3>
    
    <ul class="cpm-comment-wrap">
        <?php
        $comments = $task_obj->get_comments( $tasklist_id );
        if ( $comments ) {
    
            $count = 0;
            foreach ($comments as $comment) {
                $class = ( $count % 2 == 0 ) ? 'even' : 'odd';
                echo cpm_show_comment( $comment, $project_id, $class );
    
                $count++;
            }
        }
        ?>
    </ul>
    <?php echo cpm_comment_form( $project_id, $tasklist_id ); ?>
</div>