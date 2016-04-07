define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $form = $("#message-search-form"),
            $modal = $form.parents('.modal'),
            $table = $("#classroom-table");

        $form.submit(function(e) {
            e.preventDefault();
            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });

        $table.on('click', '.choose-course', function(e){
            var classroomId = $(this).data('target');
            var classroomName = $(this).data('name');
            var html = '<a href="/classroom/'+classroomId+'" target="_blank"><strong>'+classroomName+'</strong></a>';
            $('#choose-classroom-input').val(classroomId);
            $('#course-display .well').html(html);       
            $('.js-rechoose-classroom').show();
            $('.js-rechoose-course').hide();
            $('.js-rechoose-vip').hide();  
            $('#course-display').show();
            $modal.modal('hide');
            Notify.success('指定班级成功');
        });

        $modal.on('hidden.bs.modal', function (e) {
            if (!$('#choose-course-input').val()) {
                $('.radio').button('reset');
            };
        })
    };
})