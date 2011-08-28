/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


var Isotope =
{

	/**
	 * Media Manager
	 * @param object
	 * @param string
	 * @param string
	 */
	mediaManager: function(el, command, id)
	{
		var table = $(id).getFirst('table');
		var tbody = table.getFirst('tbody');
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'up':
				parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
				break;

			case 'down':
				parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
				break;

			case 'delete':
				parent.destroy();
				break;
		}

		rows = tbody.getChildren();

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'hidden' || first.type == 'text' || first.type == 'textarea')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
			}
		}
	},

	/**
	 * Attribute wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	attributeWizard: function(el, command, id)
	{
		var container = $(id);
		var parent = $(el).getParent();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'up':
				if (!parent.getPrevious() || parent.getPrevious().hasClass('fixed'))
				{
					parent.injectInside(container);
				}
				else
				{
					parent.injectBefore(parent.getPrevious());
				}
				break;

			case 'down':
				if (parent.getNext())
				{
					parent.injectAfter(parent.getNext());
				}
				else
				{
					var fel = container.getFirst();

					if (fel.hasClass('fixed'))
					{
						fel = fel.getNext();
					}

					parent.injectBefore(fel);
				}
				break;

		}
	},

	/**
	 * Surcharge wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	surchargeWizard: function(el, command, id)
	{
		var table = $(id);
		var tbody = table.getFirst().getNext();
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'copy':
				var tr = new Element('tr');
				var childs = parent.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true).injectInside(tr);
					next.getFirst().value = childs[i].getFirst().value;
				}

				tr.injectAfter(parent);
				break;

			case 'up':
				parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
				break;

			case 'down':
				parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
				break;

			case 'delete':
				(rows.length > 1) ? parent.destroy() : null;
				break;
		}

		rows = tbody.getChildren();

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'select-one' || first.type == 'text' || first.type == 'checkbox')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
			}
		}
	},


	/**
	 * Field wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	fieldWizard: function(el, command, id)
	{
		var table = $(id);
		var tbody = table.getFirst().getNext();
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'copy':
				var tr = new Element('tr');
				var childs = parent.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true).injectInside(tr);
					next.getFirst().value = childs[i].getFirst().value;

					if (next.getFirst().type == 'checkbox')
					{
						next.getFirst().checked = childs[i].getFirst().checked ? 'checked' : '';
						if (Browser.Engine.trident && Browser.Engine.version < 5) next.innerHTML = next.innerHTML.replace(/CHECKED/ig, 'checked="checked"');
					}
				}

				tr.injectAfter(parent);
				break;

			case 'up':
				parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
				break;

			case 'down':
				parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
				break;

			case 'delete':
				(rows.length > 1) ? parent.destroy() : null;
				break;
		}

		rows = tbody.getChildren();
		var fieldnames = new Array('value', 'label', 'default');

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'text' || first.type == 'checkbox' || first.type == 'hidden')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']')
				}
			}
		}
	},


	/**
	 * Image watermark wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	imageWatermarkWizard: function(el, command, id)
	{
		var table = $(id);
		var tbody = table.getFirst().getNext();
		var parent = $(el).getParent('tr');
		var rows = tbody.getChildren();

		Backend.getScrollOffset();

		switch (command)
		{
			case 'copy':
				var tr = new Element('tr');
				var childs = parent.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true).injectInside(tr);
					next.getFirst().value = childs[i].getFirst().value;
				}

				tr.injectAfter(parent);
				break;

			case 'delete':
				(rows.length > 1) ? parent.destroy() : null;
				break;
		}

		rows = tbody.getChildren();

		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();

			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();

				if (first.type == 'select-one')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
				}
				else if (first.type == 'text' || first.type == 'checkbox')
				{
					first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']')
				}
			}
		}
	},


	/**
	 * Toggle checkbox group
	 * @param object
	 * @param string
	 */
	toggleCheckboxGroup: function(el, id)
	{
		var cls = $(el).className;
		var status = $(el).checked ? 'checked' : '';

		if (cls == 'tl_checkbox')
		{
			$$('#' + id + ' .tl_checkbox').each(function(checkbox)
			{
				if (!checkbox.disabled)
					checkbox.checked = status;
			});
		}
		else if (cls == 'tl_tree_checkbox')
		{
			$$('#' + id + ' .parent .tl_tree_checkbox').each(function(checkbox)
			{
				if (!checkbox.disabled)
					checkbox.checked = status;
			});
		}

		Backend.getScrollOffset();
	},

	/**
	 * Toggle the product tree (input field)
	 * @param object
	 * @param string
	 * @param string
	 * @param string
	 * @param integer
	 * @return boolean
	 */
	toggleProductTree: function (el, id, field, name, level)
	{
		el.blur();
		var item = $(id);
		var image = $(el).getFirst();

		if (item)
		{
			if (item.getStyle('display') == 'none')
			{
				item.setStyle('display', 'inline');
				image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
				$(el).title = CONTAO_COLLAPSE;
				new Request.Contao().post({'action':'toggleProductTree', 'id':id, 'state':1, 'REQUEST_TOKEN':REQUEST_TOKEN});
			}
			else
			{
				item.setStyle('display', 'none');
				image.src = image.src.replace('folMinus.gif', 'folPlus.gif');
				$(el).title = CONTAO_EXPAND;
				new Request.Contao().post({'action':'toggleProductTree', 'id':id, 'state':0, 'REQUEST_TOKEN':REQUEST_TOKEN});
			}

			return false;
		}

		new Request.Contao(
		{
			onRequest: AjaxRequest.displayBox('Loading data …'),
			onSuccess: function(txt, json)
			{
				var ul = new Element('ul');

				ul.addClass('level_' + level);
				ul.set('html', txt);

				item = new Element('li');

				item.addClass('parent');
				item.setProperty('id', id);
				item.setStyle('display', 'inline');

				ul.injectInside(item);
				item.injectAfter($(el).getParent('li'));

				$(el).title = CONTAO_COLLAPSE;
				image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
				AjaxRequest.hideBox();

				// HOOK
				window.fireEvent('ajax_change');
   			}
		}).post({'action':'loadProductTree', 'id':id, 'level':level, 'field':field, 'name':name, 'state':1, 'REQUEST_TOKEN':REQUEST_TOKEN});

		return false;
	},

	/**
	 * Add the interactive help
	 */
	addInteractiveHelp: function()
	{
		$$('a.tl_tip').each(function(el)
		{
			if (el.retrieve('complete'))
			{
				return;
			}

			el.addEvent('mouseover', function()
			{
				el.timo = setTimeout(function()
				{
					var box = $('tl_helpBox');

					if (!box)
					{
						box = new Element('div').setProperty('id', 'tl_helpBox').injectInside($(document.body));
					}

					var scroll = el.getTop();

					box.set('html', el.get('longdesc'));
					box.setStyle('display', 'block');
					box.setStyle('top', (scroll + 18) + 'px');
				}, 1000);
			});

			el.addEvent('mouseout', function()
			{
				var box = $('tl_helpBox');

				if (box)
				{
					box.setStyle('display', 'none');
				}

				clearTimeout(el.timo);
			});

			el.store('complete', true);
		});
	},


	inheritFields: function(fields, label)
	{
		var injectError = false;

		fields.each(function(name, i)
		{
			var el = $(('ctrl_'+name));

			if (el)
			{
				var parent = el.getParent('div').getFirst('h3');

				if (!parent && el.match('.tl_checkbox_single_container'))
				{
					parent = el;
				}

				if (!parent)
				{
					injectError = true;
					return;
				}

				parent.addClass('inherit');

				var check = $('ctrl_inherit').getFirst(('input[value='+name+']'));

				check.setStyle('float', 'right').inject(parent);
				$('ctrl_inherit').getFirst(('label[for='+check.get('id')+']')).setStyles({'float':'right','padding-right':'5px', 'font-weight':'normal'}).set('text', label).inject(parent);

				check.addEvent('change', function(event) {
					var element = $(('ctrl_'+event.target.get('value')));

					if (element.match('.tl_checkbox_single_container'))
					{
						element.getFirst('input').disabled = event.target.checked;
					}
					else
					{
						element.setStyle('display', (event.target.checked ? 'none' : 'block'));
					}
				});

				if (el.match('.tl_checkbox_single_container'))
				{
					el.getFirst('input').readonly = check.checked;
				}
				else
				{
					el.setStyle('display', (check.checked ? 'none' : 'block'));
				}
			}
		});

		if (!injectError)
		{
			$('ctrl_inherit').getParent('div').setStyle('display', 'none');
		}
	},

	initializeToolsMenu: function()
	{
		if ($$('#tl_buttons .isotope-tools').length < 1)
			return;

		$$('#tl_buttons .header_isotope_tools').setStyle('display', 'inline');

		var tools = $$('#tl_buttons .isotope-tools').clone();

		$$('#tl_buttons .isotope-tools').each(function(node) {
			//node.previousSibling.deleteData(0, node.previousSibling.nodeValue.length);
			node.previousSibling.nodeValue = '';
			node.destroy();
		});

		var div = new Element('div',
		{
			'id': 'isotopetoolsmenu',
			'styles': {
				'top': ($$('a.header_isotope_tools')[0].getPosition().y + 22)
			}
		}).adopt(tools);

		div.inject($(document.body));
		div.setStyle('left', $$('a.header_isotope_tools')[0].getPosition().x - 7);

		// Add trigger to tools buttons
		$$('a.header_isotope_tools').addEvent('click', function(e)
		{
			$('isotopetoolsmenu').setStyle('display', 'block');
			return false;
		});

		// Hide context menu
		$(document.body).addEvent('click', function()
		{
			$('isotopetoolsmenu').setStyle('display', 'none');
		});
	},

	initializeToolsButton: function()
	{
		// Hide the tool buttons
		$$('#tl_listing .isotope-tools').each(function(el)
		{
			el.addClass('invisible');
		});

		// Add trigger to edit buttons
		$$('a.isotope-contextmenu').each(function(el)
		{
			if (el.getNext('a.isotope-tools'))
			{
				el.removeClass('invisible').addEvent('click', function(e)
				{
					if ($defined($('isotope-contextmenu')))
					{
						$('isotope-contextmenu').destroy();
					}

					var div = new Element('div',
					{
						'id': 'isotope-contextmenu',
						'styles': {
							'top': (el.getPosition().y + 22),
							'display': 'block'
						}
					});

					el.getAllNext('a.isotope-tools').each( function(el2)
					{
						var im2 = el2.getFirst('img');
						div.set('html', (div.get('html')+'<a href="'+ el2.href +'" title="'+ el2.title +'">'+ el2.get('html') +' '+ im2.alt +'</a>'));
					});

					div.inject($(document.body));
					div.setStyle('left', el.getPosition().x - (div.getSize().x / 2));

					return false;
				});
			}
		});

		// Hide context menu
		$(document.body).addEvent('click', function()
		{
			if ($defined($('isotope-contextmenu')))
			{
				$('isotope-contextmenu').destroy();
			}
		});
	},

	/**
	 * Make parent view items sortable
	 * @param object
	 */
	makePageViewSortable: function(ul)
	{
		var list = new Sortables(ul,
		{
			contstrain: true,
			opacity: 0.6
		});

		list.active = false;

		list.addEvent('start', function()
		{
			list.active = true;
		});

		list.addEvent('complete', function(el)
		{
	    	if (!list.active)
	    	{
    			return;
    		}

    		if (el.getPrevious())
    		{
    			var id = el.get('id').replace(/li_/, '');
    			var pid = el.getPrevious().get('id').replace(/li_/, '');
    			var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=1&page_id=' + pid;
    			new Request({url: window.location.href, method: 'get', data: req}).send();
    		}
    		else if (el.getParent())
    		{
    			var id = el.get('id').replace(/li_/, '');
    			var pid = el.getParent().get('id').replace(/ul_/, '');
    			var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=2&page_id=' + pid;
				new Request({url: window.location.href, method: 'get', data: req}).send();
    		}
    	});
	}
};

window.addEvent('domready', function()
{
	Isotope.addInteractiveHelp();
	Isotope.initializeToolsMenu();
	Isotope.initializeToolsButton();
}).addEvent('structure', function()
{
	Isotope.initializeToolsButton();
});

