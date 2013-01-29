<?php
$project_obj = CPM_Project::getInstance();
$projects = isset($_GET['post_status']) ? $project_obj->get_projects('', $_GET['post_status']) : $project_obj->get_projects();
?>

<div class="icon32" id="icon-themes"><br></div>
<h2><?php _e( 'Projects', 'cpm' ); ?> 
    <?php if ( $project_obj->has_admin_rights() ) { ?> 
        <a href="#" id="cpm-create-project" class="add-new-h2"><?php _e( 'Add New', 'cpm' ); ?></a> 
    <?php } ?> 
</h2>
<ul class="subsubsub">
    <li><a href="<?php echo cpm_url_projects(); ?>"><?php _e( 'Incomplete', 'cpm' ); ?></a> |</li>
    <li><a href="<?php echo cpm_url_completed_projects(); ?>"><?php _e( 'Completed', 'cpm' ); ?></a> |</li>
    <li><a href="<?php echo cpm_url_trashed_projects(); ?>"><?php _e( 'Trash', 'cpm' ); ?></a></li>
</ul>
<div class="cpm-projects">

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

                <tr id="post-<?php echo $project->ID; ?>" class="post-<?php echo $project->ID; ?> type-project" valign="top">
                    <th scope="row" class="check-column">
                        <label class="screen-reader-text" for="cb-select-<?php echo $project->ID; ?>"><?php echo get_the_title( $project->ID ); ?></label>
                        <input id="cb-select-<?php echo $project->ID; ?>" type="checkbox" name="post[]" value="<?php echo $project->ID; ?>" />
                    </th>
                    
                    <td class="post-title page-title column-title"><strong><a class="row-title" href="<?php echo cpm_url_project_details( $project->ID ); ?>" title="Details of &#8220;<?php echo get_the_title( $project->ID ); ?>&#8221;"><?php echo get_the_title( $project->ID ); ?></a></strong>
                        <div class="row-actions">
                            <span class='edit'><a href="#link-for-edit" title="Edit this item">Edit</a> | </span>
                            <span class='complete'><a class='cpm-project-complete-link' title='Complete this project' data-id="<?php echo $project->ID ?>" href='#complete-action'>Complete</a> | </span>
                            <span class='trash'><a class='cpm-project-trash-link' title='Move this project to the Trash' data-id="<?php echo $project->ID ?>" href='#move-to-trash-action'>Trash</a> | </span>
                            <span class='delete'><a class='cpm-project-delete-link submitdelete' title='Delete this project permanently' data-id="<?php echo $project->ID ?>" href='#delete-action'>Delete Permanently</a></span>
                        </div>
                    
                    </td>           
                    <td class="author column-author"><a href="#author-filter"><?php echo $project->users[0]; ?></a></td>       
                    <td class="comments column-comments">
                        <div class="post-com-count-wrapper"><a href='#link-to-comments' title='Post Count' class='post-com-count'><span class='comment-count'>[count]</span></a></div>
                    </td>
                    <td class="date column-date"><abbr title="date">[created]</abbr><br />[published]</td>      
                </tr>

            <?php } ?>

        </tbody>
        <tfoot>
        <tr>
            <th scope='col'  class='manage-column column-cb check-column'  style=""><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox" /></th><th scope='col'  class='manage-column column-title sortable desc'  style=""><a href="#order-by-title-toggle"><span>Title</span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-author'  style="">Author</th><th scope='col'  class='manage-column column-comments num sortable desc'  style=""><a href="#order-by-comments-count-toggle"><span><span class="vers"><div title="Comments" class="comment-grey-bubble"></div></span></span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-date sortable asc'  style=""><a href="order-by-date-toggle"><span>Date</span><span class="sorting-indicator"></span></a></th> </tr>
        </tfoot>
    </table>

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