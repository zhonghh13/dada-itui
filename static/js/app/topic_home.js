$(function()
{
	$('#bp_more').click(function()
	{

		var _this = this;

		switch (window.location.hash)
		{
			default:

				var request_url = G_BASE_URL + '/topic/ajax/get_topics_list/page-' + $(this).attr('data-page');
			break;

		}

		$(this).addClass('loading');

		$.get(request_url, function (response)
		{
			if (response.length)
			{
				if ($(_this).attr('data-page') == 0)
				{
					$('#main_contents').html(response);
				}
				else
				{
					$('#main_contents').append(response);
				}


				$(_this).attr('data-page', parseInt($(_this).attr('data-page')) + 1);
			}
			else
			{
				if ($(_this).attr('data-page') == 0)
				{
					$('#main_contents').html('<p style="padding: 15px 0" align="center">' + _t('没有内容') + '</p>');
				}

				$(_this).addClass('disabled');

				$(_this).find('span').html(_t('没有更多了'));
			}

			$(_this).removeClass('loading');
		})

		return false;
	});

	$('#bp_more').attr('data-page', 0).click();
})