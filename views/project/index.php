<?php
$project_obj = CPM_Project::getInstance();
$post_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'publish';
$projects = $project_obj->get_projects( -1, $post_status );
?>

<div class="icon32" id="icon-themes"><br></div>
<h2><?php _e( 'Projects', 'cpm' ); ?> 
    <?php if ( $project_obj->has_admin_rights() ) { ?> 
        <a href="#" id="cpm-create-project" class="button-primary"><?php _e( 'Add Project', 'cpm' ); ?></a> 
    <?php } ?> 
</h2>

<div class="cpm-cols">
    <div class="cpm-current-tasks-wrapper">
        <?php
            echo cpm_priority_tasks_metabox( $_COOKIE['cpm_priority_tasks_metabox_user'] ? $_COOKIE['cpm_priority_tasks_metabox_user'] : get_current_user_id() );
            echo cpm_current_tasks_metabox( $_COOKIE['cpm_current_tasks_metabox_user'] ? $_COOKIE['cpm_current_tasks_metabox_user'] : get_current_user_id() );
        ?>
    </div>
    
    <div class="cpm-projects">

        <?php cpm_get_status_nav_menu( __( cpm_map_status( $post_status ), 'cpm' ) ); ?>

        <div id="">

            <table class="wp-list-table widefat fixed posts" cellspacing="0">
                <thead>
                    <tr>
                        <th scope='col' id='cb' class='manage-column column-cb check-column'>
                            <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                            <input id="cb-select-all-1" type="checkbox" />
                        </th>
                        <th scope='col' id='comments' class='manage-column column-comments num sortable desc' style="width: 25px !important;">
                            <span class="cpm-comment-count-icon"></span>
                        </th>
                        <th scope='col' id='title' class='manage-column column-title sortable desc' style="width: 30% !important;">
                            <a href="#order-by-title-toggle">
                                <span>Title</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope='col' id='contributors' class='manage-column column-author' style="width: 15% !important;">Contributors</th>
                        <th scope='col' id='date-stated' class='manage-column column-date sortable asc' style="width: 15% !important;">
                            <a href="sort-by-date-toggle">
                                <span>Date Started</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope='col' id='date-stated' class='manage-column column-date sortable asc' style="width: 15% !important;">
                            <a href="sort-by-date-toggle">
                                <span>Date Due</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                    </tr>
                </thead>

                <tbody id="the-list">

                    <?php
                    foreach ($projects as $project) {
                        if ( !$project_obj->has_permission( $project ) ) {
                            continue;
                        }
                        ?>

                        <tr id="post-<?php echo $project->ID; ?>" class="post-<?php echo $project->ID; ?> type-project project-item" valign="top">
                            <th scope="row" class="check-column">
                                <label class="screen-reader-text" for="cb-select-<?php echo $project->ID; ?>"><?php echo get_the_title( $project->ID ); ?></label>
                                <input id="cb-select-<?php echo $project->ID; ?>" type="checkbox" name="post[]" value="<?php echo $project->ID; ?>" />
                            </th>
                            <td class="author column-author cpm-comment-count-container">
                                <span class="cpm-comment-count-icon"></span>
                                <span class="cpm-comment-count-number" style="font-size: 11px !important;"><?php echo $project->comment_count; ?></span></td>
                            
                            <td class="project-title column-title">
                                <strong>
                                    <a class="row-title" href="<?php echo cpm_url_project_details( $project->ID ); ?>" title="Details of &#8220;<?php echo get_the_title( $project->ID ); ?>&#8221;">
                                        <?php echo "<span class='task-id'>" . $project->ID . "</span>"; ?>
                                        <?php echo get_the_title( $project->ID ); ?>
                                    </a>
                                </strong>
                                                            
                                <div class="row-actions">
                                    <span class='quick-edit'><a a class='cpm-project-quick-edit-link' href="#link-for-quick-edit" title="Edit this item">Quick Edit</a></span>
                                    <?php if ( isset( $post_status) && $post_status != 'publish' ): ?>
                                    <span class='publish'> | <a class='cpm-project-publish-link' title='Publish this project' data-current-tab="<?php echo $post_status; ?>" data-status="publish" data-id="<?php echo $project->ID ?>" href='#publish-action'><?php _e( 'Publish', 'cpm' ); ?></a></span>
                                    <?php endif; ?>

                                    <?php if ( $post_status != 'complete' &&  $post_status != 'draft' && $post_status != 'archive' ): ?>
                                    <span class='complete'> | <a class='cpm-project-complete-link' title='Complete this project' data-current-tab="<?php echo $post_status; ?>" data-status="complete" data-id="<?php echo $project->ID ?>" href='#complete-action'><?php _e( 'Complete', 'cpm' ); ?></a></span>
                                    <?php endif; ?>

                                    <?php if ( $post_status != 'draft' && $post_status != 'complete' && $post_status != 'archive' && $post_status != 'pending' ): ?>
                                    <span class='draft'> | <a class='cpm-project-draft-link' title='Set this project as Draft' data-current-tab="<?php echo $post_status; ?>" data-status="draft" data-id="<?php echo $project->ID ?>" href='#draft-action'><?php _e( 'Draft', 'cpm' ); ?></a></span>
                                    <?php endif; ?>

                                    <?php if ( $post_status != 'pending' && $post_status != 'complete' && $post_status != 'archive' && $post_status != 'draft' ): ?>
                                    <span class='pending'> | <a class='cpm-project-pending-link' title='Set this project as Pending' data-current-tab="<?php echo $post_status; ?>" data-status="pending" data-id="<?php echo $project->ID ?>" href='#pending-action'><?php _e( 'Pending', 'cpm' ); ?></a></span>
                                    <?php endif; ?>

                                    <?php if ( $post_status != 'archive' &&  $post_status != 'draft' && $post_status != 'pending' ): ?>
                                    <span class='archive'> | <a class='cpm-project-archive-link' title='Move this project to Archive' data-current-tab="<?php echo $post_status; ?>" data-status="archive" data-id="<?php echo $project->ID ?>" href='#archive-action'><?php _e( 'Archive', 'cpm' ); ?></a></span>
                                    <?php endif; ?>

                                    <?php if ( !isset( $post_status) || $post_status != 'trash' ): ?>
                                    <span class='trash'> | <a class='cpm-project-trash-link' title='Move this project to the Trash' data-current-tab="<?php echo $post_status; ?>" data-status="trash" data-id="<?php echo $project->ID ?>" href='#move-to-trash-action'><?php _e( 'Trash', 'cpm' ); ?></a></span>
                                    <?php endif; ?>

                                    <?php if ( isset( $post_status) && $post_status == 'trash' ): ?>
                                    <span class='delete'> | <a class='cpm-project-delete-link submitdelete' title='Delete this project permanently' data-current-tab="<?php echo $post_status; ?>" data-id="<?php echo $project->ID ?>" href='#delete-action'><?php _e( 'Delete Permanently', 'cpm' ); ?></a></span>
                                    <?php endif; ?>
                                </div>
                            </td>           
                            <td class="author column-author"><?php cpm_project_users_shortened( $project->ID ); ?>
                            </td>
                            <td class="author column-author"><span class="cpm-assigned-user "><?php echo cpm_get_date($project->post_date); ?></span></td>   
                            <td class="author column-author"><span class="cpm-assigned-user "></span></td>   
                        </tr>
                    <?php } ?>

                </tbody>
                <tfoot>
                    <tr>
                        <th scope='col'  class='manage-column column-cb check-column'>
                            <label class="screen-reader-text" for="cb-select-all-2">Select All</label>
                            <input id="cb-select-all-2" type="checkbox" />
                        </th>
                        <th scope='col'  class='manage-column column-title sortable desc'>
                            <a href="#order-by-title-toggle">
                                <span>Title</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope='col'  class='manage-column column-author'>Contributors</th>
                        <th scope='col'  class='manage-column column-comments num sortable desc'>
                            <span class="cpm-comment-count-icon"></span>
                        </th>
                        <th scope='col'  class='manage-column column-date sortable asc'>
                            <a href="order-by-date-toggle">
                                <span>Date</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                    </tr>
                </tfoot>
            </table>

        </div>
    </div>
    <div class="cpm-clear-absolute"></div>
</div>
<div id="cpm-project-dialog" title="<?php _e( 'Start a new project', 'cpm' ); ?>">
    <?php if ( $project_obj->has_admin_rights() ) { ?>
        <?php cpm_project_form(); ?>
    <?php } ?>
</div>

<script type="text/javascript">
    jQuery(function($) {
        $( "#cpm-project-dialog" ).dialog({
            autoOpen: false,
            modal: true,
            dialogClass: 'cpm-ui-dialog',
            width: 485,
            height: 330,
            position:['middle', 100]
        });
    })
</script>
