<?php
$project_obj = CPM_Project::getInstance();
$post_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'publish';
$projects = $project_obj->get_projects( -1, $post_status );
?>

<div class="icon32" id="icon-themes"><br></div>
<h2><?php _e( 'Projects', 'cpm' ); ?> 
    <?php if ( $project_obj->has_admin_rights() ) { ?> 
        <a href="#" id="cpm-create-project" class="add-new-h2"><?php _e( 'Add New', 'cpm' ); ?></a> 
    <?php } ?> 
</h2>

<?php cpm_current_tasks_metabox( get_current_user_id() ); ?>

<div class="cpm-projects">

    <?php cpm_get_status_nav_menu( __( cpm_map_status( $post_status ), 'cpm' ) ); ?>

    <div id="">

        <table class="wp-list-table widefat fixed posts" cellspacing="0">
            <thead>
            <tr>
                <th scope='col' id='cb' class='manage-column column-cb check-column'  style=""><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox" /></th><th scope='col' id='title' class='manage-column column-title sortable desc'  style=""><a href="#order-by-title-toggle"><span>Title</span><span class="sorting-indicator"></span></a></th><th scope='col' id='author' class='manage-column column-author'  style="">Author</th><th scope='col' id='comments' class='manage-column column-comments num sortable desc'  style=""><a href="#order-by-postcount-toggle"><span><span class="vers"><div title="Comments" class="comment-grey-bubble"></div></span></span><span class="sorting-indicator"></span></a></th><th scope='col' id='date' class='manage-column column-date sortable asc'  style=""><a href="sort-by-date-toggle"><span>Date</span><span class="sorting-indicator"></span></a></th>   </tr>
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
                        
                        <td class="project-title column-title"><strong><a class="row-title" href="<?php echo cpm_url_project_details( $project->ID ); ?>" title="Details of &#8220;<?php echo get_the_title( $project->ID ); ?>&#8221;"><?php echo get_the_title( $project->ID ); ?></a></strong>
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
                        <td class="author column-author"><a href="#author-filter"><?php echo $project->users[0]; ?></a></td>       
                        <td class="comments column-comments">
                            <div class="post-com-count-wrapper"><a href='#link-to-comments' title='Post Count' class='post-com-count'><span class='comment-count'>[count]</span></a></div>
                        </td>
                        <td class="date column-date"><abbr title="date">[created]</abbr><br />[published]</td>    
                    </tr>
                    <tr class="inline-edit-row inline-edit-row-project inline-edit-project quick-edit-row quick-edit-row-project inline-edit-project alternate inline-editor">
                        <td colspan="5" class="colspanchange cpm-quick-edit-project">
                            <div class="cpm-quick-edit-project">
                                <?php cpm_project_form( $project ); ?>
                            </div>
                        </td>  
                    </tr>
                <?php } ?>

            </tbody>
            <tfoot>
            <tr>
                <th scope='col'  class='manage-column column-cb check-column'  style=""><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox" /></th><th scope='col'  class='manage-column column-title sortable desc'  style=""><a href="#order-by-title-toggle"><span>Title</span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-author'  style="">Author</th><th scope='col'  class='manage-column column-comments num sortable desc'  style=""><a href="#order-by-comments-count-toggle"><span><span class="vers"><div title="Comments" class="comment-grey-bubble"></div></span></span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-date sortable asc'  style=""><a href="order-by-date-toggle"><span>Date</span><span class="sorting-indicator"></span></a></th> </tr>
            </tfoot>
        </table>

    </div>
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