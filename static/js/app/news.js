//20150514王金有
$(function(){
$('#load_more').click(function(event) {
    var _this = this;
   var request_url = G_BASE_URL + '/news/ajax/news_actions/page-' + (parseInt($(this).attr('data-page')) + 1)+ '__filter-'; 
     $(this).addClass('loading');
console.log(request_url);

$.get(request_url, function (response)
        {
            if (response.length)
            {
                // if ($(_this).attr('data-page') == 0)
                // {
                //     $('.aw-article-list').html(response);
                // }
                // else
                // {
                    $('.aw-article-list').append(response);
                // }

                $(_this).attr('data-page', parseInt($(_this).attr('data-page')) + 1);
            }
            else
            {
                // if ($(_this).attr('data-page') == 0)
                // {
                //     $('.aw-article-list').html('<p style="padding: 15px 0" align="center">' + _t('没有内容') + '</p>');
                // }

                $(_this).addClass('disabled');

                $(_this).html(_t('没有更多了'));
            }

            $(_this).removeClass('loading');
        });
        return false;
    });
});


//end20150514王金有