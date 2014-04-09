;(function($) {

    var CPM_Task = {

        init: function () {
            $('ul.cpm-todolists').on('click', 'a.add-task', this.showNewTodoForm);
            $('ul.cpm-todolists').on('click', '.cpm-todos-new a.todo-cancel', this.hideNewTodoForm);
            $('ul.cpm-todolists').on('submit', '.cpm-todo-form form', this.submitNewTodo);

            //edit todo
            $('ul.cpm-todolists').on('click', '.cpm-todo-action a.cpm-todo-edit', this.toggleEditTodo);
            $('ul.cpm-todolists').on('click', '.cpm-task-edit-form a.todo-cancel', this.toggleEditTodo);
            $('ul.cpm-todolists').on('submit', '.cpm-task-edit-form form', this.updateTodo);

            //single todo
            $('.cpm-single-task').on('click', '.cpm-todo-action a.cpm-todo-edit', this.toggleEditTodo);
            $('.cpm-single-task').on('click', '.cpm-task-edit-form a.todo-cancel', this.toggleEditTodo);
            $('.cpm-single-task').on('submit', '.cpm-task-edit-form form', this.updateTodo);
            $('.cpm-single-task').on('click', '.cpm-task-uncomplete input[type=checkbox]', this.markDone);
            $('.cpm-single-task').on('click', '.cpm-task-complete input[type=checkbox]', this.markUnDone);
            $('.cpm-single-task').on('click', 'a.cpm-todo-delete', this.deleteTodo);

            // Make it sortable
            $('#current-tasks, #priority-tasks')
                .sortable({ connectWith: '.connected', forcePlaceholderSize: true })
                .bind('sortUpdate', this.sortUpdate)
                .bind('transferUpdate', this.transferUpdate);
            //current tasks metabox
            $('.cpm-current-tasks').on('change', 'select.users-dropdown', this.currentTasks);
            //priority tasks metabox
            $('.cpm-priority-tasks').on('change', 'select.users-dropdown', this.priorityTasks);
            // complete
            $('.cpm-current-tasks, .cpm-priority-tasks').on('click', 'input[type=checkbox]', this.markDoneMetaboxTask);
            // Toggle metabox
            $('.cpm-current-tasks-wrapper').on('click', 'header', this.toggleMetabox);

            //task done, undone, delete
            $('ul.cpm-todolists').on('click', '.cpm-todos input[type=checkbox]', this.markDone);
            $('ul.cpm-todolists').on('click', '.cpm-todo-completed input[type=checkbox]', this.markUnDone);
            $('ul.cpm-todolists').on('click', 'a.cpm-todo-delete', this.deleteTodo);

            //todolist
            $('.cpm-new-todolist-form').on('submit', 'form', this.addList);
            $('ul.cpm-todolists').on('submit', '.cpm-list-edit-form form', this.updateList);
            $('.cpm-new-todolist-form').on('click', 'a.list-cancel', this.toggleNewTaskListForm);
            $('a#cpm-add-tasklist').on('click', this.toggleNewTaskListFormLink);

            //tasklist edit, delete links toggle
            $('ul.cpm-todolists').on('click', 'a.cpm-list-delete', this.deleteList);
            $('ul.cpm-todolists').on('click', 'a.cpm-list-edit', this.toggleEditList);
            $('ul.cpm-todolists').on('click', 'a.list-cancel', this.toggleEditList);
            $('ul.cpm-todo-completed').on('click', '.cpm-todo-complete-toggle', this.toggleCompletedTasks);
            this.truncateTasks();
        },

        showNewTodoForm: function (e) {
            e.preventDefault();

            var self = $(this),
                next = self.parent().next();

            self.closest('li').addClass('cpm-hide');
            next.removeClass('cpm-hide');

            $('.todo_content').autosize({append: "\n"});
        },

        hideNewTodoForm: function (e) {
            e.preventDefault();

            var self = $(this),
                list = self.closest('li');

            list.addClass('cpm-hide');
            list.prev().removeClass('cpm-hide');
        },

        markDone: function () {

            var self = $(this),
                list = self.closest('li'),
                taskListEl = self.closest('article.cpm-todolist'),
                singleWrap = self.closest('.cpm-single-task'),
                data = {
                    task_id: self.val(),
                    project_id: self.data('project'),
                    list_id: self.data('list'),
                    single: self.data('single'),
                    action: 'cpm_task_complete',
                    '_wpnonce': CPM_Vars.nonce
                };

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true ) {

                    if(list.length) {
                        var completeList = list.parent().siblings('.cpm-todo-completed');
                        completeList.append('<li>' + res.content + '</li>');

                        list.remove();

                        //update progress
                        taskListEl.find('h3 .cpm-right').html(res.progress);

                    } else if(singleWrap.length) {
                        singleWrap.html(res.content);
                    }
                }
            });
        },

        markUnDone: function () {

            var self = $(this),
                list = self.closest('li'),
                taskListEl = self.closest('article.cpm-todolist'),
                singleWrap = self.closest('.cpm-single-task'),
                data = {
                    task_id: self.val(),
                    project_id: self.data('project'),
                    list_id: self.data('list'),
                    single: self.data('single'),
                    action: 'cpm_task_open',
                    '_wpnonce': CPM_Vars.nonce
                };


            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true ) {

                    if(list.length) {
                        var currentList = list.parent().siblings('.cpm-todos');

                        currentList.append('<li>' + res.content + '</li>');
                        list.remove();

                        //update progress
                        taskListEl.find('h3 .cpm-right').html(res.progress);

                    } else if(singleWrap.length) {
                        singleWrap.html(res.content);
                    }
                }
            });
        },

        markDoneMetaboxTask: function () {

            var self = $(this),
                list = self.closest('li'),
                data = {
                    task_id: self.val(),
                    project_id: self.data('project'),
                    list_id: self.data('list'),
                    action: 'cpm_task_complete',
                    '_wpnonce': CPM_Vars.nonce
                };

            $(this).siblings('span.complete-task-loading').show();

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true ) {
                    list.remove();
                }

                $(this).siblings('span.complete-task-loading').hide();
            });
        },

        submitNewTodo: function (e) {
            e.preventDefault();

            var self = $(this),
                data = self.serialize(),
                taskListEl = self.closest('article.cpm-todolist'),
                content = $.trim(self.find('.todo_content').val());

            if(content !== '') {
                $.post(CPM_Vars.ajaxurl, data, function (res) {
                    res = JSON.parse(res);

                    if(res.success === true) {
                        var currentList = self.closest('ul.cpm-todos-new').siblings('.cpm-todos');
                        currentList.append( '<li>' + res.content + '</li>' );

                        //clear the form
                        self.find('textarea, input[type=text], select').val('');

                        //update progress
                        taskListEl.find('h3 .cpm-right').html(res.progress);

                    } else {
                        alert('something went wrong!');
                    }
                });
            } else {
                alert('type something');
            }
        },

        deleteTodo: function (e) {
            e.preventDefault();

            var self = $(this),
                list = self.closest('li'),
                taskListEl = self.closest('article.cpm-todolist'),
                confirmMsg = self.data('confirm'),
                single = self.data('single'),
                data = {
                    list_id: self.data('list_id'),
                    project_id: self.data('project_id'),
                    task_id: self.data('task_id'),
                    action: 'cpm_task_delete',
                    '_wpnonce': CPM_Vars.nonce
                };

            if( confirm(confirmMsg) ) {
                $.post(CPM_Vars.ajaxurl, data, function (res) {
                    res = JSON.parse(res);

                    if(res.success) {
                        if(single !== '') {
                            location.href = res.list_url;
                        } else {
                            list.fadeOut(function() {
                                $(this).remove();
                            });

                            //update progress
                            taskListEl.find('h3 .cpm-right').html(res.progress);
                        }
                    }
                });
            }
        },

        toggleEditTodo: function (e) {
            e.preventDefault();

            var wrap = $(this).closest('.cpm-todo-wrap');

            wrap.find('.cpm-todo-content').toggle();
            wrap.find('.cpm-task-edit-form').slideToggle();
        },

        updateTodo: function (e) {
            e.preventDefault();

            var self = $(this),
                data = self.serialize(),
                list = self.closest('li'),
                singleWrap = self.closest('.cpm-single-task'),
                content = $.trim(self.find('.todo_content').val());

            if(content !== '') {
                $.post(CPM_Vars.ajaxurl, data, function (res) {
                    res = JSON.parse(res);

                    if(res.success === true) {
                        if(list.length) {
                            list.html(res.content); //update in task list
                        } else if(singleWrap.length) {
                            singleWrap.html(res.content); //update in single task
                        }

                    } else {
                        alert('something went wrong!');
                    }
                });
            } else {
                alert('type something');
            }
        },

        //toggle new task list form from top link
        toggleNewTaskListFormLink: function (e) {
            e.preventDefault();

            $('.cpm-new-todolist-form').slideToggle();
            $('.tasklist_detail').autosize({append: "\n"});
        },

        toggleNewTaskListForm: function (e) {
            e.preventDefault();

            $(this).closest('form').parent().slideToggle();
        },

        toggleEditList: function (e) {
            e.preventDefault();

            var article = $(this).closest('article.cpm-todolist');
                article.find('header').slideToggle();
                article.find('.cpm-list-edit-form').slideToggle();
        },

        addList: function (e) {
            e.preventDefault();

            var self = $(this),
                data = self.serialize();

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true) {

                    $('ul.cpm-todolists').append('<li id="cpm-list-' + res.id + '">' + res.content + '</li>');

                    var list = $('#cpm-list-' + res.id);

                    $('.cpm-new-todolist-form').slideToggle();
                    $('body, html').animate({
                        scrollTop: list.offset().top
                    });

                    list.find('a.add-task').click();
                    list.find('textarea.todo_content').focus();

                    self.find('textarea, input[type=text], select').val('');
                    $('.datepicker').datepicker();
                }
            });
        },

        updateList: function (e) {
            e.preventDefault();

            var self = $(this),
                data = self.serialize();

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true) {
                    self.closest('li').html(res.content);
                    $('.datepicker').datepicker();
                }
            });
        },

        deleteList: function (e) {
            e.preventDefault();

            var self = $(this),
                list = self.closest('li'),
                confirmMsg = self.data('confirm'),
                data = {
                    list_id: self.data('list_id'),
                    action: 'cpm_tasklist_delete',
                    '_wpnonce': CPM_Vars.nonce
                };

            if( confirm(confirmMsg) ) {
                $.post(CPM_Vars.ajaxurl, data, function (res) {
                    res = JSON.parse(res);

                    if(res.success) {
                        list.fadeOut(function() {
                            $(this).remove();
                        });
                    }
                });
            }
        },

        currentTasks: function(e) {
            e.preventDefault();

            var self = $(this),
                taskListCon = self.closest('header').siblings('.cpm-todos'),
                data = {
                    user_id: self.val(),
                    action: 'cpm_tasks_by_user',
                    '_wpnonce': CPM_Vars.nonce
                };

            $('.cpm-current-tasks span.tasks-loading').show();

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true) {
                    taskListCon.html(res.content);
                    // Make it sortable
                    $('#current-tasks, #priority-tasks')
                        .sortable({ connectWith: '.connected', forcePlaceholderSize: true })
                        .bind('sortUpdate', this.sortUpdate)
                        .bind('transferUpdate', this.transferUpdate);

                    $.cookie('cpm_current_tasks_metabox_user', self.val(), { path: '/' });
                }

                $('.cpm-current-tasks span.tasks-loading').hide();
            });
        },

        priorityTasks: function(e) {
            e.preventDefault();

            var self = $(this),
                taskListCon = self.closest('header').siblings('.cpm-todos'),
                data = {
                    user_id: self.val(),
                    action: 'cpm_tasks_by_priority',
                    '_wpnonce': CPM_Vars.nonce
                };

            $('.cpm-priority-tasks span.tasks-loading').show();

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true) {
                    taskListCon.html(res.content);
                    // Make it sortable
                    $('#current-tasks, #priority-tasks')
                        .sortable({ connectWith: '.connected', forcePlaceholderSize: true })
                        .bind('sortUpdate', this.sortUpdate)
                        .bind('transferUpdate', this.transferUpdate);

                    $.cookie('cpm_priority_tasks_metabox_user', self.val(), { path: '/' });
                }

                $('.cpm-priority-tasks span.tasks-loading').hide();
            });
        },

        toggleMetabox: function(e) {
            if ( $(e.target).prop('tagName') != 'HEADER' )
                return;

            var self = $(this);

            self.siblings('.cpm-todos').toggle(0);
        },

        truncateTasks: function(e) {
            var lis = $('.cpm-todo-completed li');

            if (!$('.cpm-todo-complete-toggle').length)
                lis.eq(10).after('<div class="cpm-todo-complete-toggle button-secondary collapsed">Click to show all completed tasks</div>');

            lis.slice(10).wrapAll('<div class="cpm-todo-completed-wrap" />');
        },

        toggleCompletedTasks: function(e) {
            var lis = $('.cpm-todo-completed li');

            $(this).prev().slideToggle();

            if ($(this).hasClass('collapsed'))
                $(this).removeClass('collapsed').text('Click to collapse completed tasks');
            else
                $(this).addClass('collapsed').text('Click to show all completed tasks');

        },

        sortUpdate: function(e, dragging) {
            if ( $(this)[0] == $('#current-tasks')[0] )
                return;

            var self = $(this)[0] == $('#priority-tasks')[0] ? $(this) : $('#priority-tasks'),
                data = {
                    tasks: [],
                    user_id: self.closest('header').children('select').val(),
                    action: 'cpm_tasks_update_priority',
                    '_wpnonce': CPM_Vars.nonce
                };

            self.children('li').each(function(i, el){
                data.tasks[$(el).index()] = $(el).children('input[type=checkbox]').val();
            });

            $(dragging.item).children('span.complete-task-loading').show();
            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true) {
                    $(dragging.item).children('span.complete-task-loading').hide();
                }
            });
        },

        transferUpdate: function(e, dragging) {
            if ($('.no-tasks').length) {
                $('.no-tasks').prev('li').css({'border-bottom': 'none'});
                $('.no-tasks').hide();
            }

            var self = $(this)[0] == $('#priority-tasks')[0] ? $(this) : $('#priority-tasks'),
                data = {
                    tasks: [],
                    user_id: self.closest('.cpm-priority-tasks').find('header').children('select').val(),
                    direction: $(this)[0] == $('#priority-tasks')[0] ? 'to' : 'from',
                    action: 'cpm_tasks_update_priority',
                    '_wpnonce': CPM_Vars.nonce
                };

            // Donnot allow transfer is user_id is not equal
            if (data.user_id != $('#current-tasks').closest('.cpm-current-tasks').find('header').children('select').val())
                return;

            self.children('li').each(function(i, el){
                data.tasks[$(el).index()] = $(el).children('input[type=checkbox]').val();
            });

            $(dragging.item).children('span.complete-task-loading').show();

            $.post(CPM_Vars.ajaxurl, data, function (res) {
                res = JSON.parse(res);

                if(res.success === true) {
                    // Save sorting after transfer
                    CPM_Task.sortUpdate(e, dragging);
                    $(dragging.item).children('span.complete-task-loading').hide();
                }
            });
        }
    };

    $(function() {
        CPM_Task.init();
    });

})(jQuery);