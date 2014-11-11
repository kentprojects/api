/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */

var defaults = {
	deletelink: '&nbsp;<a href="#" onclick="$.deleterow(this); return false;">Delete</a>',
	inputparam: '<li><input name="params-keys[]" onfocus="$.addrow(this);" placeholder="Parameter" type="text"/><input name="params-values[]" placeholder="Value" type="text"/></li>'
};

$(document).ready(function ()
{
	/**
	 * Extending jQuery to handling the awesomeness of our form!
	 */
	$.addrow = function (a)
	{
		$(a).parent().append(defaults.deletelink);
		$('ul.pretty-parameters').append(defaults.inputparam);
		$(a).removeAttr('onfocus');
	};
	$.deleterow = function (a)
	{
		$(a).parent().html('');
	};
	$.fn.extend({

		inputreset: function ()
		{
			$(this).html(defaults.inputparam);
			return $(this);
		},

		textreset: function ()
		{
			$(this).html(defaults.textparam);
			return $(this);
		}

	});

	$('ul.pretty-parameters').append(defaults.inputparam);

	$('ul.api-selector a').click(function (e)
	{
		e.preventDefault();
		$('input[name="url"]').val($(this).attr('href'));
		$('select[name="method"]').val('GET');
		$('ul.pretty-parameters').reset().show();
		$('ul.pretty-body').inputreset().hide();
		$('ul.api-selector li.active').removeClass('active');
		$('div.results').slideUp(function ()
		{
			$('div.results').html('');
		});
		$(this).parent().addClass('active');
	});

	$('select.pretty-method').change(function ()
	{
		switch ($(this).val())
		{
			case 'GET':
				$('div.pretty-body').hide();
				break;
			case 'POST':
			case 'PUT':
			case 'DELETE':
				$('div.pretty-body').show();
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
						// window.location.hash = $('form').serialize();
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

var editor = ace.edit("pretty-editor");
editor.setTheme("ace/theme/github");
editor.getSession().setMode("ace/mode/json");
editor.on("change", function (e)
{
	document.getElementById("pretty-field").value = editor.getSession().getValue();
});