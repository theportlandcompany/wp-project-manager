<?php
$pro_obj = CPM_Project::getInstance();
$project = $pro_obj->get( $project_id );

if ( !$project ) {
    echo '<h2>' . __( 'Error: Project not found', 'cpm' ) . '</h2>';
    die();
}

if ( !$pro_obj->has_permission( $project ) ) {
    echo '<h2>' . __( 'Error: Permission denied', 'cpm' ) . '</h2>';
    die();
}
?>
<div class="cpm-project-head">
    <div class="cpm-project-detail">
        
        <h2>
            <?php if ( $pro_obj->has_admin_rights() ) { ?>
                <a href="#" class="cpm-icon-edit cpm-project-edit-link"><span><?php _e( 'Edit', 'cpm' ); ?></span></a>
            <?php } ?>
            
            <?php echo get_the_title( $project_id ); ?>
        
            <?php if ( current_user_can( 'delete_others_posts' ) ) { //editor ?>
                <a href="#" class="button-secondary cpm-project-delete-link" title="<?php esc_attr_e( 'Delete project', 'cpm' ); ?>" <?php cpm_data_attr( array('confirm' => 'Please confirm you want to delete this project.', 'project_id' => $project_id) ) ?>>
                    <span><?php _e( 'Delete Project', 'cpm' ); ?></span>
                </a>
            <?php } ?>
        </h2>

        <div class="detail">
        
            <h4>Contributors: <?php cpm_project_users( $project_id, $assigned_to ); ?></h4>
        
            <?php echo cpm_get_content( $project->post_content ); ?>
        </div>
        
    </div><!-- end of cpm-project-detail -->

    <div class="cpm-edit-project">
        <?php
        if ( $pro_obj->has_admin_rights() ) {
            cpm_project_form( $project );
        }
        ?>
    </div>

    <div class="cpm-clear"></div>
</div>

<h2 class="nav-tab-wrapper">
    <?php
    echo $pro_obj->nav_menu( $project_id, $cpm_active_menu );
    
    if ( $_GET['tab'] == 'task' ) {
        
        echo "<a id='cpm-add-tasklist' href='#' class='button-primary'>";
        _e( 'Add Task List', 'cpm' );
        echo "</a>";
    }
    ?>
</h2>