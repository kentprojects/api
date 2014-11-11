/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

var defaults = {
	deletelink: '&nbsp;<a href="#" onclick="$.deleterow(this); return false;">Delete</a>',
	param: '<li><input name="params-keys[]" onfocus="$.addrow(this);" placeholder="Parameter" type="text"/><input name="params-values[]" placeholder="Value" type="text"/></li>',
};

$(document).ready(function ()
{

	/**
	 *    Extending jQuery to handling the awesomeness of our form!
	 */
	$.addrow = function (i)
	{
		$(i).parent().append(defaults.deletelink);
		$('ul.pretty-parameters').append(defaults.param);
		$(i).removeAttr('onfocus');
	};
	$.deleterow = function (a)
	{
		$(a).parent().html('');
	};
	$.fn.extend({

		reset: function ()
		{
			$(this).html(defaults.param);
			return $(this);
		}

	});

	$('ul.pretty-parameters').append(defaults.param);

	$('ul.api-selector a').click(function (e)
	{
		e.preventDefault();
		$('input[name="url"]').val($(this).attr('href'));
		$('select[name="method"]').val('GET');
		$('ul.pretty-parameters').reset().show();
		$('ul.api-selector li.active').removeClass('active');
		$('div.results').slideUp(function ()
		{
			$('div.results').html('');
		});
		$(this).parent().addClass('active');
	});

	$('select.pretty-method').change(function ()
	{
		console.log($(this).val());
		switch ($(this).val())
		{

			case 'GET':
			case 'POST':
			case 'PUT':
				$('ul.pretty-parameters').reset().show();
				break;

			case 'DELETE':
				$('ul.pretty-parameters').reset().hide();
				break;

		}
	});

	$('form').submit(function ()
	{
		$.ajax({
			data: $('form').serialize(),
			success: function (html)
			{
				// console.log(html);
				$('div.results').slideUp(function ()
				{
					$('div.results').html(html).slideDown(function ()
					{
						window.location.hash = $('form').serialize();
					});
				});
			},
			type: 'POST',
			url: 'request.php'
		});
		return false;
	});

	if (window.location.hash !== "")
	{
		$.each(window.location.hash.split('&'), function (index, elem)
		{
			var vals = elem.split('=');
			$("[name='" + vals[0] + "']").val(decodeURIComponent(vals[1].replace(/\+/g, ' ')));
		});
		$('form').submit();
	}

});

function keyDownHandler(e)
{
	var key = e.keyCode;
	switch (key)
	{
		case 116:
			e.preventDefault();
			$('form').submit();
			break;
	}
}

document.addEventListener('keydown', keyDownHandler, false);