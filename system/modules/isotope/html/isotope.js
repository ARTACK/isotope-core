/**
 * @copyright  Winans Creative 2009
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
		var table = $(id);
		var tbody = table.getFirst().getNext();
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

				if (first.type == 'hidden' || first.type == 'textarea')
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
	}
};


window.addEvent('domready', function()
{
	Isotope.addInteractiveHelp();
});


/**
 * Class AjaxRequestIsotope
 *
 * Provide methods to handle ajax-related tasks for Isotope back end widgets.
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
var ProductsOptionWizard =
{
		/**
	 * Table wizard
	 * @param object
	 * @param string
	 * @param string
	 */
	tableWizard: function(el, command, id, name)
	{			
		var table = $(id);
		var tbody = table.getFirst();
		var rows = tbody.getChildren();
		var parentTd = $(el).getParent();
		var parentTr = parentTd.getParent();
		var cols = parentTr.getChildren();
		var index = 0;
		
		for (var i=0; i<cols.length; i++)
		{
			if (cols[i] == parentTd)
			{
				
				break;
			}

			index++;
		}

		ProductsOptionWizard.getScrollOffset();

		switch (command)
		{
			case 'rcopy':
				var tr = new Element('tr');
				var childs = parentTr.getChildren();

				for (var i=0; i<childs.length; i++)
				{
					var next = childs[i].clone(true, true).injectInside(tr);	//inject cell and contents
					//var selected = childs[i].getFirst().value;
					next.getFirst().value = childs[i].getFirst().value;
					next.getFirst().value = '-';
					
					/*
					if(current.options[current.selectedIndex].value!='-')
					{
						current.remove(current.selectedIndex);
					}*/
					
					//var next.getFirst()
					var current = next.getChildren()[index];
											
					var haystack = current.id;
					
					//current.name = current.id.replace(/\[[0-9]+\][[0-9]+\]/ig, '[' + (i-1) + '][' + j + ']');
					
					//var next2 = current.getFirst();
					
					//next2.name = next2.name.replace(/\[[0-9]+\][[0-9]+\]/ig, '[' + (i-1) + '][' + j + ']');

					
					if(haystack.indexOf('value') !== -1)
					{
						//destroy the existing value div so we can create a new one.
						current.destroy();						
					}
				
					//var next2 = current.getFirst(); -- how to refer to the next child element, the select box
																												
					
				}

				tr.injectAfter(parentTr);
				break;

			case 'rdelete':
				(rows.length > 2) ? parentTr.destroy() : null;
				break;

			case 'ccopy':
				for (var i=0; i<rows.length; i++)
				{
					var current = rows[i].getChildren()[index];
					var next = current.clone(true, true).injectAfter(current);
										
					next.getFirst().value = current.getFirst().value;
					
					var current = next.getChildren()[index];
														
					if(current.type== 'select-one' && current.id)
					{
						if(current.options[current.selectedIndex].value!='-')
						{
							current.remove(current.selectedIndex);
						}
						
						next = current.getNext();
						
						var haystack = next.id;
						
						if(haystack.indexOf('value') !== -1)
						{
							next.destroy();						
						}
						
					}
				}
				break;

			case 'cdelete':
				if (cols.length > 2)
				{
					/*for (var i=0; i<rows.length; i++)
					{
						var current = rows[i].getChildren()[index];
						var next = current.clone(true, true).injectAfter(current);
										
						next.getFirst().value = current.getFirst().value;
					
						var current = next.getChildren()[index];
														
						if(current.type== 'select-one' && current.id)
						{
							if(current.options[current.selectedIndex].value!='-')
							{
								current.add(current.selectedIndex);
							}
						}
						
						next = current.getNext();
					}*/
					
					for(var i=0; i<rows.length; i++)
					{
						rows[i].getChildren()[index].destroy();
					}
				}
				break;
		}

		rows = tbody.getChildren();
	
		for (var i=0; i<rows.length; i++)
		{
			var childs = rows[i].getChildren();
					
			for (var j=0; j<childs.length; j++)
			{
				var first = childs[j].getFirst();
						
				if (first && first.type == 'select-one')
				{
									
					first.name = first.name.replace(/\[[0-9]+\][[0-9]+\]/ig, '[' + (i-1) + '][' + j + ']');
				}
				
				/*
				if (first && first.id == 'value_div')
				{
					first.name = first.name.replace(/\[[0-9]+\][[0-9]+\]/ig, '[' + (i-1) + '][' + j + ']');				
				}*/
				
			}
		}

		ProductsOptionWizard.tableWizardResize();
	},
	
	getOptionValues: function(el, id, name)
	{
		el.blur();	
		
		var item_value = el.value; 
				
		var re1='.*?';	// Non-greedy match on filler
     	var re2='(\\d+)';	// Integer Number 1
        var re3='.*?';	// Non-greedy match on filler
        var re4='(\\d+)';	// Integer Number 2

        var p = new RegExp(re1+re2+re3+re4,["i"]);
        var m = p.exec(el.name);
      
      	if (m != null)
      	{
          var xcoord=m[1];
          var ycoord=m[2];
   		}
		
				
		new Request(
		{
			url: window.location.href,
			data: 'isAjax=1&action=addPOAttributeValues&aid=' + item_value + '&parent=' + name + '&r=' + xcoord + '&c=' + ycoord,
			onStateChange: ProductsOptionWizard.displayBox('Loading data ...'),			
			onComplete: function(txt, xml)
			{
									
				var currDiv= $('value_div[' + xcoord + '][' + ycoord + ']');
				
				if($defined(currDiv))
				{
					currDiv.destroy();
				}
									
				div = new Element('div');
				div.setProperty('id','value_div[' + xcoord + '][' + ycoord + ']');
				div.setProperty('name',id + '_values[' + xcoord + '][' + ycoord + ']');
				div.set('html',txt);
				
				div.injectAfter(el);
										
				
				ProductsOptionWizard.hideBox();
   			}
		}).send();

		return false;	
	},
	
	/*
	 * Resize table wizard fields on focus
	 */
	tableWizardResize: function()
	{
		$$('.tl_tablewizard textarea').each(function(el)
		{
			el.set('morph', { duration: 200 });

			el.addEvent('focus', function()
			{
				el.setStyle('position', 'absolute');
				el.morph(
				{
					'height': '166px',
					'width': '356px',
					'margin-top': '-50px',
					'margin-left': '-107px'
				});
				el.setStyle('z-index', '1');
			});

			el.addEvent('blur', function()
			{
				el.setStyle('z-index', '0');
				el.morph(
				{
					'height': '66px',
					'width': '142px',
					'margin-top': '1px',
					'margin-left': '0'
				});
				setTimeout(function() { el.setStyle('position', ''); }, 250);
			});
		});
	},


	/**
	 * Display a "loading data" message
	 * @param string
	 */
	displayBox: function(message)
	{
		var box = $('tl_ajaxBox');
		var overlay = $('tl_ajaxOverlay');

		if (!overlay)
		{
			overlay = new Element('div').setProperty('id', 'tl_ajaxOverlay').injectInside($(document.body));
		}

		if (!box)
		{
			box = new Element('div').setProperty('id', 'tl_ajaxBox').injectInside($(document.body));
		}

		var scroll = window.getScrollTop();
		if (Browser.Engine.trident && Browser.Engine.version < 5) { var sel = $$('select'); for (var i=0; i<sel.length; i++) { sel[i].setStyle('visibility', 'hidden'); } }

		overlay.setStyle('display', 'block');
		overlay.setStyle('top', scroll + 'px');

		box.set('html', message);
		box.setStyle('display', 'block');
		box.setStyle('top', (scroll + 100) + 'px');
	},
	
	
	/**
	 * Hide the "loading data" message
	 */
	hideBox: function()
	{
		var box = $('tl_ajaxBox');
		var overlay = $('tl_ajaxOverlay');

		if (overlay)
		{
			overlay.setStyle('display', 'none');
		}

		if (box)
		{
			box.setStyle('display', 'none');
			if (Browser.Engine.trident && Browser.Engine.version < 5) { var sel = $$('select'); for (var i=0; i<sel.length; i++) { sel[i].setStyle('visibility', 'visible'); } }
		}
	},


	/**
	 * Get current scroll offset and store it in a cookie
	 */
	getScrollOffset: function()
	{
		document.cookie = "BE_PAGE_OFFSET=" + window.getScrollTop() + "; path=/";
	}
}

