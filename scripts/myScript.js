jQuery(function($){

    $('.vote-submit').on('click', function(){
        var id = $(this).attr('name');
        var count = parseInt($('.like-app-count-'+id)[0].innerHTML);
        console.log(count);



        if (document.getElementById('fa-' + id).classList.length == 2) {
            $.ajax({
                type: 'POST',
                url: like_app.ajaxurl,
                data: {
                    action: 'like-app-main',
                    post_id: id,
                    x: 'update',
                },
                success: function(data){
                    $('.like-app-count-'+id)[0].innerHTML = count+1;
                    document.getElementById('fa-'+id).classList.add('active');
                    document.getElementsByName(id).className = "vote-submit-active";
                },

            });
        }else if (document.getElementById('fa-' + id).classList.length == 3) {
            $.ajax({
                type: 'POST',
                url: like_app.ajaxurl,
                data: {
                    action: 'like-app-main',
                    post_id: id,
                    x: 'dis_update',
                },
                success: function(data){
                    $('.like-app-count-'+id)[0].innerHTML = count-1;
                    document.getElementById('fa-'+id).classList.remove('active');
                    document.getElementsByName(id).className = "vote-submit-";
                },

            });
        }


        return;

    });

});
