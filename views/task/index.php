<?php
$task_obj = CPM_Task::getInstance();
$lists = $task_obj->get_task_lists( $project_id );

cpm_get_header( __( 'Task List', 'cpm' ), $project_id );
?>

<div class="cpm-nav-title">
    
    <ul class="cpm-todolists">
        <?php
        if ( $lists ) {
            foreach ($lists as $list) {
                ?>
    
                <li id="cpm-list-<?php echo $list->ID; ?>"><?php echo cpm_task_list_html( $list, $project_id ); ?></li>
    
                <?php
            }
        }
        ?>
    </ul>

</div><!-- end of cpm-nav-title -->

<script>
    jQuery('.cpm-todo-text:contains("PENDING")').addClass('pending-task');
</script>

<?php
if ( !$lists ) {
    cpm_show_message( __( 'Oh dear, no Task List found!', 'cpm' ) );
}