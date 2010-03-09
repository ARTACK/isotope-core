<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ProductCatalog extends Backend
{

	public function __construct()
	{	
		parent::__construct();
		
		$this->import('Isotope');
	}

	protected $sqlDef = array
	(
		'integer'		=> "int(10) NULL default NULL",
		'decimal'		=> "double NULL default NULL",
		'text'			=> "varchar(255) NOT NULL default ''",
		'longtext'		=> "text NULL",
		'datetime'		=> "int(10) unsigned NOT NULL default '0'",
		'select'		=> "varchar(255) NOT NULL default ''",
		'checkbox'		=> "char(1) NOT NULL default ''",
		'options'		=> "text NULL",
		'file'			=> "text NULL",
		'media'			=> "blob NULL",
	);
	
	protected $arrForm = array();
	protected $arrTypes = array('text','password','textarea','select','radio','checkbox','upload', 'hidden');
	protected $arrList = array ('tstamp','pages','new_import'/*,'add_audio_file','add_video_file'*/);	//Basic required fields
	protected $arrData = array();
	protected $arrSelectors = array();
	
	protected $systemColumns = array('id', 'pid', 'sorting', 'tstamp', 'alias', 'published');
	
	protected $renameColumnStatement = "ALTER TABLE tl_product_data CHANGE COLUMN %s %s %s";
	
	protected $modifyColumnStatement = "ALTER TABLE tl_product_data MODIFY %s %s";
	
	protected $createColumnStatement = "ALTER TABLE tl_product_data ADD %s %s";
	
	protected $dropColumnStatement = "ALTER TABLE tl_product_data DROP COLUMN %s";

	/*		    `audio_source` varchar(32) NOT NULL default '',
  			`audio_jumpTo` text NULL,
  			`audio_url` varchar(255) NOT NULL default '',
			`video_source` varchar(32) NOT NULL default '',
  			`video_jumpTo` varchar(255) NOT NULL default '',
  			`video_url` text NULL,
			`add_audio_file` char(1) NOT NULL default '0',
			`add_video_file` char(1) NOT NULL default '0',
			`option_collection` text NULL,
	*/

	
	/**
	 * Initialize the tl_product_data DCA
	 */	
	public function loadProductCatalogDCA($strTable)
	{
		if ($strTable != 'tl_product_data')
			return;
			
		//Check for any missing standard attributes and build a list which can then be added into the table tl_product_data.		
		foreach($GLOBALS['ISO_ATTR'] as $arrSet)
		{
			if(!$this->Database->fieldExists($arrSet['field_name'], 'tl_product_data'))
			{
				$arrDefaultColumns[$arrSet['type']] = $arrSet['field_name'];
			}
								
			$objAttributeExists = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_product_attributes WHERE field_name=?")
													   ->limit(1)
													   ->execute($arrSet['field_name']);
			
			if($objAttributeExists->count < 1)
			{
				$arrAttributesToInsert[] = $arrSet;
			}
		}
			
		if(sizeof($arrDefaultColumns))
		{
			foreach($arrDefaultColumns as $k=>$v)
			{
				$this->addDefaultAttribute($v, $k);
			}
		}
		
		if(sizeof($arrAttributesToInsert))
		{		
			$sorting = $this->getNextSortValue('tl_product_attributes');
			
			foreach($arrAttributesToInsert as $row)
			{			
				$this->insertAttributeRecord($row, $sorting);
			
				$sorting+=128;
			}
		}
		
		// FIXME: should we exclude "globally disabled" fields?
		$arrFields = $this->Database->execute("SELECT * FROM tl_product_attributes")->fetchAllAssoc();
		
		
		// add DCA for form fields
		foreach ($arrFields as $field) 
		{
			$eval = array();
			if ($field['is_required']) $eval['mandatory'] = 'true';
			if ($field['rgxp']) $eval['rgxp'] = $field['rgxp'];
			if ($field['multiple']) $eval['multiple'] = $field['multiple'];
	
			
			// check for options lookup 
			$inputType = '';
			switch ($field['type'])
			{
				case 'integer':
				case 'decimal':
					$inputType = 'text';
					break;
					
				case 'datetime':
					$inputType = 'text';
					$eval['rgxp'] = 'date';
					$eval['datepicker'] = $this->getDatePickerString();
					break;
					
				case 'text':
					$inputType = 'text';
					$eval['tl_class'] = 'long';
					break;
			
				case 'longtext':
					$inputType = 'textarea';
					
					if($field['use_rich_text_editor'])
					{
						$eval['rte'] = 'tinyMCE';
					}
					break;

				case 'file':
				case 'media':
					$inputType = 'mediaManager';
					$eval['cols'] = 4;
					//if($field['show_files']) $eval['files'] = true;
					//$eval['fieldType'] = 'radio';
					break;
					
				case 'options':
					$inputType = 'radio';
					$eval['multiple'] = false;
					if($field['use_alternate_source']==1)
					{
						if(strlen($field['list_source_table']) > 0 && strlen($field['list_source_field']) > 0)
						{
							$strForeignKey = $field['list_source_table'] . '.' . $field['list_source_field'];
						
						}
					}else{
					
						$arrValues = array();
						$arrOptionsList = deserialize($field['option_list']);
						
						
						foreach ($arrOptionsList as $arrOptions)
						{
							/*if ($arrOptions['default'])
							{
								//grab as selected value
							}*/
							
							$arrValues[$arrOptions['value']] = $arrOptions['label'];
						}											
						
					}

					break;
					
				case 'select':
					$inputType = 'select';
					
					//$inputType = 'productOptionsWizard';
					
					if($field['use_alternate_source']==1)
					{
						if(strlen($field['list_source_table']) > 0 && strlen($field['list_source_field']) > 0)
						{
							$strForeignKey = $field['list_source_table'] . '.' . $field['list_source_field'];
						
						}
					}
					else
					{
						$arrValues = array();
						$arrOptionsList = deserialize($field['option_list']);
						
						if(sizeof($arrOptionsList))
						{												
							foreach ($arrOptionsList as $option)
							{
								/*if ($arrOptions['default'])
								{
									grab as selected value;
								}*/
								
								$arrValues[$option['value']] = $option['label'];
							}											
						}
					}	
		
					//optional?
					$eval['includeBlankOption'] = true;
					break;
					
				default:
					$inputType = $field['type'];
					break;
			}
			
			
			if ($field['add_to_product_variants'])
			{
				if($this->Input->get('id') && $this->Input->get('do')=='product_manager')
				{
					$objPid = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
											 ->limit(1)
											 ->execute($this->Input->get('id'));
					
					if($objPid->numRows && $objPid->pid>0)
					{
						$inputType = 'text';
					}
				}
			}
			
			$filter = ($this->arrForm['useFilter'] && $this->arrForm['filterField'] == $field['field_name']);

			$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']] = array
			(
				'label'				=> array($field['name'], $field['description']),
				'inputType'			=> $inputType,
				'search'			=> !$filter,
				'filter'         	=> $filter,
				'eval'				=> $eval,
				'attributes'		=> $field,
				'load_callback'		=> array
				(
					array('ProductCatalog','loadField')
				),
				'save_callback'		=> array
				(
					array('ProductCatalog','saveField')
				)
			);
			
			if (strlen($field['option_list']) && count($arrValues)) 
			{				
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['options'] = array_keys($arrValues);
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['reference'] = $arrValues;
			}
			
			if(strlen($strForeignKey) && $field['type'] == 'select')
			{
				$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['foreignKey'] = $strForeignKey;
				$strForeignKey = "";
			}
						
			
			if (!empty($field['load_callback']))
			{
				$arrCallbackSet = explode(',',$field['load_callback']);

				if(is_array($arrCallbackSet))
				{
					foreach($arrCallbackSet as $callback)
					{
						$arrCallbacks[] = explode(".", $callback);
					}
																		
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['load_callback'] = $arrCallbacks;
					
				}
				else
				{
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['load_callback'] = array(
						explode(".", $field['load_callback'])
					);
				}
			}
			
			if (!empty($field['save_callback']))
			{
				$arrCallbackSet = explode(',',$field['save_callback']);
								
				if(is_array($arrCallbackSet))
				{
					$arrCallbacks = array(); // reset the callback array
					foreach($arrCallbackSet as $callback)
					{
						$arrCallbacks[] = explode(".", $callback);
					}
																		
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['save_callback'] = $arrCallbacks;
					
				}
				else
				{
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$field['field_name']]['save_callback'] = array(
						explode(".", $field['save_callback'])
					);
				}
					
			}
		}
		
		
		// Add palettes
		$arrProductTypePalettes = $this->getProductTypePalettes();

		if(count($arrProductTypePalettes))
		{
			$GLOBALS['TL_DCA']['tl_product_data']['palettes'] = $GLOBALS['TL_DCA']['tl_product_data']['palettes'] + $arrProductTypePalettes;
				
			$arrAdditionalSelectors = $this->arrSelectors;
		
			$GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'] = array_merge($GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'], $arrAdditionalSelectors);
		}
		
		if($this->Input->get('id') && $this->Input->get('do')=='product_manager' && !$this->Input->get('mode') && $this->Input->get('table') != 'tl_product_categories')
		{
			//Check and update to load the palette
			$objProductType = $this->Database->prepare("SELECT d.type AS selfType, (SELECT type FROM tl_product_data p WHERE p.id=d.pid AND d.pid!=0) AS parentType FROM tl_product_data d WHERE d.id=?")
											 ->limit(1)
											 ->execute($this->Input->get('id'));
			
			if(!$objProductType->numRows || $objProductType->selfType==0)
			{
			
				//set the type for the subproduct
				$this->Database->prepare("UPDATE tl_product_data SET type=" . $objProductType->parentType . " WHERE id=?")
							   ->set($arrSet)
							   ->execute($this->Input->get('id'));
	
			}
		}
				
	}
	
	
	protected function getProductType($intProductId)
	{
		$objProductType = $this->Database->prepare("SELECT type FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->execute($intProductId);
		
		if($objProductType->numRows < 1)
		{
			throw new Exception('no product type returned for this product!');	//TODO: Add to language array
		}		
		
		return $objProductType->type;
		
	}

	
	protected function getProductOptionSets()
	{
		$intPid = $this->getProductType($this->Input->get('id'));
	
		$objSets = $this->Database->prepare("SELECT id, title FROM tl_product_option_sets WHERE pid=?")
								  ->execute($intPid);
		
		if($objSets->numRows < 1)
		{
			return array();	
		}
		
		$arrSets = $objSets->fetchAllAssoc();
				
		foreach($arrSets as $row)
		{
			$arrReturn[$row['id']] = $row['title'];
		}
		
		return $arrReturn;
	}
	
	
	public function loadProductOptions($varValue, DataContainer $dc)
	{
		$strOptionSetValue = $this->Input->post('option_set_mode');
						
		switch($strOptionSetValue)
		{
			case 'new_option_set':
				$strOptionSetName = $this->Input->post('option_set_name');
				$arrValues = $this->Input->post('values');
				
				if(!sizeof($arrValues))
				{
					return $varValue;
				}
				else
				{
					foreach($arrValues as $key=>$attribute)
					{
						$arrAttributes[$key] = explode(',', trim($attribute));
					}
				}
				break;
				
			case 'existing_option_set':
				$strOptionSetId = $this->Input->post('option_sets');
				
				$arrSubProducts = $this->loadSubproducts($strOptionSetId);
				
				break;		
		}
			
		

		//$
				
		//** Data structure example **//
		/*
			array(2) {
			  [0]=>			/// ROWS
			  array(2) {	
			    [0]=>
			    string(2) "17"	//the select at 0, 0
			    [1]=>
			    string(2) "18"  //the select at 0, 1
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(2) "17"	//the select at 1, 0
			    [1]=>
			    string(2) "18"  //the select at 1, 1
			  }
			}
			
			array(2) {
			  [0]=>
			  array(2) {
			    [0]=>
			    string(3) "red" 	//the value at 0, 0
			    [1]=>
			    string(5) "small"	//the value at 0, 1
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(3) "red"		//the value at 1, 0
			    [1]=>
			    string(6) "medium"  //the value at 1, 1
			  }
			}
		
			and we will transform this into the following structure...
			
			array(2) { 			// Number of total rows
				
				[0] =>
				array(2) {
					[0] => array(2)
					{
						'attribute'		=> string(2) "17",
						'value'			=> string(3) "red"
					},
					[1] => array(2)
					{
						'attribute'		=> string(2) "18",
						'value'			=> string(5) "small"					
					}
				},
				[1] =>
				array(2) {
					[0] => array(2)
					{
						'attribute'		=> string(2) "17",
						'value'			=> string(3) "red"
					},
					[1] => array(2)
					{
						'attribute'		=> string(2) "18",
						'value'			=> string(6) "medium"					
					}
				
				}
			
			}
		
		
		$arrAttributes = deserialize($varValue);	//because the first thing that happens is this, on save.	
		$arrValues = $this->Input->post($dc->field . '_values'); 
	
		$arrCompositeValues = array();
	
		for($x=0; $x<sizeof($arrAttributes); $x++)
		{
			
			for($y=0; $y<sizeof($arrValues); $y++)
			{	
				
					
					$arrAttributeValuePairs[] = array
					(
						'x'				=> $x,
						'y'				=> $y,
						'attribute'		=> $arrAttributes[$x][$y],
						'value'			=> $arrValues[$x][$y]					
					);						
					
			}		
		
		}
		
		return serialize($arrAttributeValuePairs);*/
		
	}
	
	/*public function loadProductOptions($varValue, DataContainer $dc)
	{
		$arrAttributeValuePairs = deserialize($varValue);
		
		$arrAttributes = array();
		$arrValues = array();
		
		if(sizeof($arrAttributeValuePairs)<1)
		{
			return;
		}
		
		foreach($arrAttributeValuePairs as $row)
		{
			
				$x = (integer)$row['x'];
				$y = (integer)$row['y'];
				
				$arrAttributes[$x][$y] = $row['attribute'];
				
				$arrValues[$x][$y] = $row['value'];*/
				
				/*
				$varValue[$valuePair['x']] = array
				(
					$valuePair['x']			=>	$valuePair[$valuePair['x']][$valuePair['attribute']],
					$valuePair['x']+1		=>	$valuePair[$valuePair['x']+1][$valuePair['attribute']]
				);
				
				$arrValues[$row['x']] = array
				(
					$row['x']		=>	$valuePair[$row['x']]['value'],
					$row['x']+1		=>  $valuePair[$row['x']+1]['value']
				);*/
		
		//}	
			//$_SESSION['FORM_DATA'][$dc->field . '_values'] = $arrValues;
			//$_SESSION['FORM_DATA'][$dc->field] = $arrAttributes;
			
			//serialize($_SESSION['FORM_DATA'][$dc->field.'_values']);
			//
			//$varValue = $arrAttributes;
			//return $arrAttributes;
			
			/*array(2) {
			  [0]=>			/// ROWS
			  array(2) {	
			    [0]=>
			    string(2) "17"	//the select at 0, 0
			    [1]=>
			    string(2) "18"  //the select at 0, 1
			  }
			  [1]=>
			  array(2) {
			    [0]=>
			    string(2) "17"	//the select at 1, 0
			    [1]=>
			    string(2) "18"  //the select at 1, 1
			  }
			}*/
			
	
	
	//}
	
	public function loadField($varValue, DataContainer $dc)
	{
		// HOOK: loadField callback
		if (array_key_exists('loadField', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['loadField']))
		{
			foreach ($GLOBALS['TL_HOOKS']['loadField'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($varValue, $dc);
			}
		}

		return $varValue;
	}
	
	public function saveField($varValue, DataContainer $dc)
	{
		$objAttribute = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE field_name=?")
									   ->limit(1)
									   ->execute($dc->field);
		
		if($objAttribute->numRows < 1)
		{
			throw new Exception('Not a valid record id!');
		}
		
		if($objAttribute->is_filterable)
		{
			$this->saveFilterValuesToCategories($varValue, $dc);
		}
		
		//if($objAttribute->is_order_by_enabled)
				
		//if($objAttribute->is_searchable)
		
		//if($objAttribute->is_used_for_price_rules)
		
			
		// HOOK: loadField callback
		if (array_key_exists('saveField', $GLOBALS['TL_HOOKS']) && is_array($GLOBALS['TL_HOOKS']['saveField']))
		{
			foreach ($GLOBALS['TL_HOOKS']['saveField'] as $callback)
			{
				$this->import($callback[0]);
				$varValue = $this->$callback[0]->$callback[1]($varValue, $dc);
			}
		}
		
		return $varValue;
	}

	
	protected function getProductTypePalettes()
	{
		
		$objProductTypes = $this->Database->prepare("SELECT * FROM tl_product_types")->execute();
		
		if (!$objProductTypes->numRows)
		{
			return array();
		}
		
		while($objProductTypes->next())
		{
			$arrFieldCollection = array();
			$strAttributes = "";
			
			$objPid = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
									 ->limit(1)
									 ->execute($this->Input->get('id'));
			
			if(!$objPid->numRows)
			{
				$strAttributeField = 'attributes';
			}
			else
			{
				if($objPid->pid!=0)
				{
					$strAttributeField = 'variant_attributes';
				}
				else
				{
					$strAttributeField = 'attributes';
				}
			}
						
			$arrFieldCollection = deserialize($objProductTypes->$strAttributeField);
			
			if(is_array($arrFieldCollection) && count($arrFieldCollection) > 0)
			{
				
				$objHiddenAttributes = $this->Database->execute("SELECT field_name FROM tl_product_attributes WHERE is_hidden_on_backend='1'");
						
				if($objHiddenAttributes->numRows > 0)
				{				
					while($objHiddenAttributes->next())
					{
						$i = 0;
						
						foreach($arrFieldCollection as $field)
						{
							if($field==$objHiddenAttributes->field_name && $strAttributeField=='attributes')
							{
								unset($arrFieldCollection[$i]);
							}
							
							$i++;
						}
					}
				}
								
				$strAttributes = $this->buildPaletteString($arrFieldCollection);
				
				$arrPalettes[$objProductTypes->id] = $strAttributes;					
			}
		}

		return $arrPalettes;
	}
	
	
	private function buildPaletteString($arrFields, $strAppendToLegend = '', $arrExtraFields = array())
	{
		if (!is_array($arrFields) || !count($arrFields))
			return '';
		
		$arrPalette = array();
	
		foreach( $arrFields as $field )
		{
			// Field does not exist
			if (!is_array($GLOBALS['TL_DCA']['tl_product_data']['fields'][$field]))
				continue;
				
			$arrAttributes = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$field]['attributes'];
	
			/*if($arrAttributes['legend'] == 'options_legend' && (!is_array($arrPalette[$arrAttributes['legend']]) || !in_array('option_set_source', $arrPalette[$arrAttributes['legend']])))
			{
				$arrPalette[$arrAttributes['legend']][] = 'option_set_source';
			}*/

			if(count($arrExtraFields))
			{
				
				foreach($arrExtraFields as $extrafield)
				{				
					if(is_array($arrPalette[$strAppendToLegend]) && !in_array($extrafield, $arrPalette[$strAppendToLegend]))
						$arrPalette[$strAppendToLegend][] = $extrafield;
				}
			}

			//To do - detemine if product can support variants.  This would be determined by any customer defined attributes being a part of the given palette or not.
			/*if($arrAttributes['legend'] == 'options_legend' && !in_array('options_set_source', $arrPalette[$arrAttributes['legend']]))
			{
				if(!in_array('option_set_source', $this->arrSelectors))
				{
					$this->arrSelectors[] = 'option_set_source';
				}
								
				if(!in_array('option_set_source', $arrPalette[$arrAttributes['legend']]))
				{
					$arrPalette[$arrAttributes['legend']][] = 'option_set_source';
				}
			}*/
						
						
			$arrPalette[$arrAttributes['legend']][] = $field;			

		}
		
		/*		
		if(!in_array('option_set_source', $this->arrSelectors))
		{
			$this->arrSelectors[] = 'option_set_source';
		}
		*/

		//Build
		$arrLegends = array();
		foreach($arrPalette as $legend=>$fields)
		{
			$arrLegends[] = '{' . $legend . '},' . implode(',', $fields);
		}

		return implode(';', $arrLegends);
	}
	
	public function getOptionSets()
	{
		//step 1: get the current product type
		
		
		//step 2: drop in the options relevant to the current palette type.  These options must change values because of the extra palettes generated for the radio widget
		$strCurrentPaletteType = $this->getCurrentPalette($this->Input->get('id'));
		
		$arrChoices[$strCurrentPaletteType . '_existing_option_set'] = &$GLOBALS['TL_LANG']['tl_product_data']['existing_option_set'];
		$arrChoices[$strCurrentPaletteType . '_new_option_set'] = &$GLOBALS['TL_LANG']['tl_product_data']['new_option_set'];		
		
		return $arrChoices;
	}
	
	public function getCurrentPalette($intId)
	{
		$objCurrentPalette = $this->Database->prepare("SELECT type FROM tl_product_data WHERE id=?")
											->limit(1)
											->execute($intId);
		
		if($objCurrentPalette->numRows < 1)
		{
			return '';
		}
		
		return $objCurrentPalette->type;
	}
	
	protected function prepareCategories($varValue, DataContainer $dc)
	{
		if(is_null($varValue) || strlen(trim($varValue)) < 1)
		{
			return '';
		}
		//Potentially the delimiter could be different.  May want to try and figure it out autommatically.
		if(!is_array(deserialize($varValue)))
		{
			if(strpos($varValue, ','))
			{
				$arrPages = explode(',', $varValue);
				if(sizeof($arrPages) < 1 || strlen($arrPages[0])<1)
				{
					return '';
				}
			}
			else
			{
				$arrPages[] = $varValue;	//singular value
			}
			
			$arrPages = serialize($arrPages);
			
			return $arrPages;
		}
		
		return $varValue;
		
	}

	
	public function generateMappingAttributeList()
	{
		$arrOptions = array();
		$arrAttributes = array();
		
		$objAttributes = $this->Database->prepare("SELECT field_name FROM tl_product_attributes WHERE pid=?")
										->execute($set['id']);
								
		if($objAttributes->numRows < 1)
		{
			return false;
		}
				
		$arrAttributes = $objAttributes->fetchAllAssoc();
			
		foreach($arrAttributes as $attribute)
		{						
			$arrOptions[] = array
			(
				'value' => $attribute['field_name'],
				'label' => $attribute['name']
			);
		}
					
		return $arrOptions;
	}
	
	public function changeFieldType(DataContainer $dc)
	{
		
		$objField = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE id=?")
								   ->limit(1)
								   ->execute($dc->id);
		
		if(!$objField->numRows)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['missingAttribute'], $dc->id));
		}
		
		if($objField->type!=$this->Input->post('type'))
		{
			$statement = sprintf($this->modifyColumnStatement, $this->Input->post('field_name'), $this->sqlDef[$this->Input->post('type')]);
		}
		
		if (strlen($statement))
			$this->Database->execute($statement);
	}
	
	public function renameColumn($varValue, DataContainer $dc)
	{
		
		$varValue = strtolower($this->mysqlStandardize($varValue));
		
		if (!preg_match('/^[a-z_][a-z\d_]*$/i', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $varValue));
		}
		if (in_array($varValue, $this->systemColumns))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}
		
		//Get pertinent field data.
		$objField = $this->Database->prepare("SELECT id, type, field_name FROM tl_product_attributes WHERE id=?")
								   ->limit(1)
								   ->execute($dc->id);
			
		// check duplicate form_field name
		$objItems = $this->Database->prepare("SELECT id FROM tl_product_attributes WHERE pid=? AND id<>? AND name=?")
								   ->execute($objField->pid, $objField->id, $varValue);
		
		if ($objItems->numRows)
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['columnExists'], $varValue));
		}
		

		$fieldType = $objField->type ? $objField->type : 'text';
		$fieldName = $objField->field_name;
		
	//	$this->loadDataContainer('tl_product_data');
		
		//see if this field already exists in the core DCA.  if so only create a record in product attributes to extend it.
		/*if(!array_key_exists($varValue, $GLOBALS['TL_DCA']['tl_product_data']['fields']))
		{		
			$this->import('IsotopeDatabase');
			$this->IsotopeDatabase->update($fieldName, $this->sqlDef[$fieldType]);
			
			if ($this->Database->fieldExists($fieldName, 'tl_product_data'))
			{
				if ($objField->field_name != $varValue)
				{
					$statement = sprintf($this->renameColumnStatement, $fieldName, $varValue, $this->sqlDef[$fieldType]);
				}
			}
			else
			{
				$statement = sprintf($this->createColumnStatement, $varValue, $this->sqlDef[$fieldType]);
			}
		}*/
		
		if ($this->Database->fieldExists($fieldName, 'tl_product_data'))
		{
			if ($objField->field_name != $varValue)
			{
				$statement = sprintf($this->renameColumnStatement, $fieldName, $varValue, $this->sqlDef[$fieldType]);
			}
			
		}
		else
		{
			$statement = sprintf($this->createColumnStatement, $varValue, $this->sqlDef[$fieldType]);
		}

				
		if (strlen($statement))
			$this->Database->execute($statement);
		
		//Create the field name for quick reference in code.
//		$this->Database->prepare("UPDATE tl_product_attributes SET field_name='" . $varValue . "' WHERE id=?")
//					   ->execute($dc->id);
		
		return $varValue;
	}
	
	
	/** 
	 * Add a default attribute to the tl_product_data table from a source other than normal editing operations, for example, the ISO_ATTR global array
	 *
	 * @access public
	 * @param variant $varValue
	 * @param object $dc
	 * @param string $fieldType
	 * @return $varValue;
	 */
	public function addDefaultAttribute($varValue, $fieldType)
	{
		if (!preg_match('/^[a-z_][a-z\d_]*$/i', $varValue))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $varValue));
		}
		
		if (in_array($varValue, $this->systemColumns))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}
		
		$statement = sprintf($this->createColumnStatement, $varValue, $this->sqlDef[$fieldType]);
		
		$this->import('IsotopeDatabase');
		$this->IsotopeDatabase->update($fieldName, $this->sqlDef[$fieldType]);
				
		$this->Database->execute($statement);
		
		return $varValue;
	}
	
	/** 
	 * Insert a new attribute record from a source other than normal table operations (for example, from default attributes defined in ISO_ATTR global array
	 *
	 * @access public
	 * @param array $arrSet
	 * @param integer $intSorting
	 * @return void;
	 */
	public function insertAttributeRecord($arrSet)
	{
		$this->Database->prepare("INSERT INTO tl_product_attributes %s")->set($arrSet)->execute();
	}

	
	/** 
	 * Get the next sorting value if it exists for a given table.
	 * 
	 * @access public
	 * @param string $strTable
	 * @return integer;
	 */
	public function getNextSortValue($strTable)
	{
		if($this->Database->fieldExists('sorting', $strTable))
		{
			$objSorting = $this->Database->prepare("SELECT MAX(sorting) as maxSort FROM " . $strTable)
										 ->execute();
			
			return $objSorting->maxSort + 128;
		}
		
		return 0;
	}


	public function changeColumn($varValue, DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT id, type, name FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
						
		if ($objField->numRows == 0)
		{
				return $varValue;
		}
	
		$fieldName = $objField->name;
		$fieldType = $objField->type;
		
		if ($varValue != $fieldType)
		{
			if ($varValue != $fieldType)
			{
				$this->Database->execute(sprintf($this->createColumnStatement, $fieldName, $this->sqlDefColumn[$varValue]));
				
				$this->import('IsotopeDatabase');
				$this->IsotopeDatabase->update($fieldName, $this->sqlDef[$fieldType]);
			}
		}
		
		return $varValue;
	}

	
	/**
	 * Autogenerate an article sku if it has not been set yet
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function generateSKU($varValue, DataContainer $dc, $id=0)
	{
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.
		if($id!=0)
		{
			$intId = $id;
		}else{
			$intId = $dc->id;
		}
		
		$autoAlias = true;

		// Generate alias if there is none
		if (!strlen($varValue))
		{
			$objProductName = $this->Database->prepare("SELECT id, new_import, name, sku FROM tl_product_data WHERE id=?")
									   ->limit(1)
									   ->execute($intId);

			$autoAlias = true;
			
			if($objProductName->new_import!=1)
			{
				if(!strlen($objProductName->sku))
				{
					$varValue = standardize($objProductName->product_name);
				}
			}
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_product_data WHERE id=? OR sku=?")
								   ->execute($intId, $varValue);

		// Check whether the page alias exists
		if ($objAlias->numRows > 1)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '_' . $intId;
		}

		return $varValue;
	}
	
	
	/**
	 * Standardize an attribute title to valid mysql field characters and case
	 *
	 * @param string
	 * @return string
	 */
	public static function mysqlStandardize($strName)
	{
		$varValue = utf8_romanize($strName);
		
		$varValue = preg_replace('/[^a-zA-Z0-9 _-]+/i', '', $varValue);
		$varValue = preg_replace('/ +/i', '_', $varValue);
		
		return $varValue;
	}
	
	
	/**
	 * Wrapper for the Product-Filter Collection associative table logic.  Grabs all necessary values in order to update the PFC table.
	 *
	 * @param string
	 * @param object
	 * @return string
	 */
	public function saveFilterValuesToCategories($varValue, DataContainer $dc, $id=0)
	{		
		if(is_null($varValue) || (is_int($varValue) && $varValue == 0))
		{
			return $varValue;
		}
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.	
		if($id!=0)
		{
			$intId = $id;
		}
		else
		{
			$intId = $dc->id;
		}
						
		//Get the current attribute set		
		$objAttributeID = $this->Database->prepare("SELECT id FROM tl_product_attributes WHERE field_name=?")
										 ->limit(1)
										 ->execute($dc->field);
		
		if($objAttributeID->numRows < 1)
		{
			return $varValue;
		}
		
		$attributeID = $objAttributeID->id;
		
		
		//Gather all records pertaining to the current attribute set in the aggregate table
		$objAllPageInfo = $this->Database->prepare("SELECT pid, value_collection FROM tl_filter_values_to_categories WHERE attribute_id=?")->execute($attributeID);
		
		if($objAllPageInfo->numRows > 0)
		{
			//Contains pid which is the reference to a given page, and attribute_id which is the reference to a given filter.
			$arrAllPageInfo = $objAllPageInfo->fetchAllAssoc();
		}
			
		//Get the value submitted for this particular attribute
		$objRecordValues = $this->Database->prepare("SELECT pages, " . $dc->field . " FROM tl_product_data WHERE id=?")
													->limit(1)
													->execute($dc->id);
		if($objRecordValues->numRows < 1)
		{
			return $varValue;
		}
		
		$arrNewPageList = deserialize($objRecordValues->pages);
		
		if(is_string($arrNewPageList))
		{
			$arrNewPageList = array();
		}
				
		$this->updateFilterValuesToCategories($arrNewPageList, $arrAllPageInfo, $dc, $attributeID, $varValue);
	
		return $varValue;
	}
	
	
	
	/**
	 * updatePFCAggregate - Update our aggregate reference table which is used to build collections of products out of multiple attribute sets. This logic maintains the records by page of associated products and storeTables.
	 *
	 * @param variant
	 * @param object
	 * @param string
	 *
	 */
	private function updateFilterValuesToCategories($arrPageList, $arrAllPageInfo, DataContainer $dc, $attributeID, $varCurrValue)
	{		
		
		if(sizeof($arrPageList) < 1)
		{
			
			$arrPageList[] = 0;
		}
		
		if(empty($varCurrValue) || (is_int($varCurrValue) && $varCurrValue==0))
		{
			
			return;
		}
		
		$arrCurrValues[] = $varCurrValue;
		
		//Check Existing records first to avoid duplicate entries
		$objPFCInfo = $this->Database->prepare("SELECT id, pid, attribute_id, value_collection FROM tl_filter_values_to_categories WHERE pid IN (" . join(",", $arrPageList) . ") AND attribute_id=?")
									->execute($attributeID);
		
		
		if($objPFCInfo->numRows < 1)
		{
			
			// If there is no existing PFC record, then we just insert. Insert into table the association
			foreach($arrPageList as $intPageNum)
			{				
				$arrSet = array();
				
				$arrSet = array(
					'pid'					=> $intPageNum,
					'attribute_id'			=> $attributeID,
					'value_collection'		=> $arrCurrValues,
				);
				
				$this->Database->prepare("INSERT INTO tl_filter_values_to_categories %s")->set($arrSet)->execute();
			}
			
			return;
						
		}
		
		
		$arrPFCInfo = $objPFCInfo->fetchAllAssoc();	//Existing records are stored in an array
		
		$arrProducts = array();
		
		$arrPIDs = array();
		
		foreach($arrPFCInfo as $row)	//PIDs that already exist in the tl_filter_values_to_categories table
		{
			$arrPIDs[] = $row['pid'];
		}
		
		
		// For each existing page that DID in the past have this product ID associated with it, but NOW the submitted list does not include that page id, remove it
		
		foreach($arrAllPageInfo as $page)
		{
			$arrExistingValues = array();
			
			//Get the product ID collection of the current existing page
			$arrExistingValues = deserialize($page['value_collection']);
			
			//If the current existing page id does not exist in the list of pages collected from the form submit, then 
			//remove the product id from the page in question.			
			
			//If the product id exists in the product list for this page, which is not part of the product page list now...  Remove from the product_ids collection and update.
						
				/** TO DO - REWRITE & HANDLE MULITPLE FILTER VALUES IF ATTRIBUTE DOES MULTIPLE **/
					
				if(in_array($varCurrValue, $arrExistingValues))		//Does this need to be more strict - that is, bound to a particular pid when comparing?
				{
									
					$key = array_search($varCurrValue, $arrExistingValues); //get the corresponding key.
										
					//If we find that the product id submitted does, in fact exist in the existing product collection for this page, then we remove it.
				
						//Do any other products in this category share the filter value?  If not then we can safely remove it
						$objProductsAssociatedWithFilterValue = $this->Database->prepare("SELECT id, pages FROM tl_product_data WHERE " . $dc->field . "=?")->execute($varCurrValue);
						
												
						if($objProductsAssociatedWithFilterValue->numRows < 1)	//if there are no occurrences of this filter value in any product, then ok.
						{
							unset($arrExistingValues[$key]);
						}else{
						
							$arrOtherProductsPages = $objProductsAssociatedWithFilterValue->fetchEach('pages');	
														
							$blnPreserveFilterValue = false;		//reset every row.  if we end up false at the end we need to unset.
							
							foreach($arrOtherProductsPages as $pageRow)
							{	
								$rowInfo = deserialize($pageRow);
								
								foreach($arrPageList as $currPage)
								{				
									if(in_array($currPage, $rowInfo))
									{
								
										$blnPreserveFilterValue = true;
										break;
									}
								
								}
							}
							
							if(!$blnPreserveFilterValue) //if this filter value is used by any other product in any of the categories associated
							{	
								//with the given product, then we cannot remove the filter value from the record.							
								unset($arrExistingValues[$key]);
							}
						}						
						
						if(is_array($arrExistingValues) && sizeof($arrExistingValues)>0)
						{
	
							 $this->Database->prepare("UPDATE tl_filter_values_to_categories SET value_collection=? WHERE pid=? AND attribute_id=?")
									   		->execute(serialize($arrExistingValues), $page['pid'], $attributeID);
						}

				}

			
			//For each page record already in the table, we grab the product id list and modify it to include this product ID if it isn't existing in the product ID collection.
			
			foreach($arrPFCInfo as $page)
			{
				//Each page record we start with a fresh products array to update the record.
				$arrExistingValues = array();
				
				$arrExistingPages[] = $page['pid'];
				// Since these are serialized, we have to deserialize them before we can do any work on the record.
				$arrExistingValues = deserialize($page['value_collection']);
									
				foreach($arrPageList as $pageToBeUpdated)
				{
					if((int)$pageToBeUpdated==$page['pid'])	//If this page 
					{
						//If the product ID doesn't not already have an association to the current page, then add it to the list of product IDs for that page.
						if(!in_array($varCurrValue, $arrExistingValues))
						{
							$arrExistingValues[] = $varCurrValue;	//add the product id in.
						}
					}				
									
					// Update existing association
					$this->Database->prepare("UPDATE tl_filter_values_to_categories SET value_collection=? WHERE pid=? AND attribute_id=?")
								   ->execute(serialize($arrExistingValues), $page['pid'], $attributeID);
				}			
			}
		
		
		}
		//New Pages to add that aren't in the current collection
		
		foreach($arrPageList as $intPageNum)
		{	
			if(!in_array((int)$intPageNum, $arrExistingPages))
			{
				
				$arrSet = array();
				$arrValues = array();
				
				$arrValues[] = $varCurrValue;
				
				$arrSet = array(
					'value_collection'		=> serialize($arrValues),
					'pid'					=> $intPageNum,
					'attribute_id'			=> $attributeID
				);
				
				$this->Database->prepare("INSERT INTO tl_filter_values_to_categories %s")->set($arrSet)->execute();
			}
		}			
				
		return;
	}
	
	private function generateTitle($strFormat, $values)
	{
		$fields = $GLOBALS['TL_DCA']['tl_product_data']['list']['label']['fields'];
		preg_match_all('/{{([^}]+)}}/', $strFormat, $matches);
		//$strFormat = '';
		foreach ($matches[1] as $match)
		{
			$params = split('::', $match);
			$fieldConf = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$params[0]];
			if ($fieldConf)
			{	
				$replace = $values[$params[0]];
				if ($params[1])
				{
					switch ($fieldConf['eval']['isotope']['type'])
					{
						case 'file':
								if ($fieldConf['eval']['isotope']['showImage'])
								{ 
									$replace = $this->generateThumbnail($replace, $params[1], $fieldConf['label'][0]);
								}
								break;

						case 'checkbox':
								// only use image if checkbox == true
								$replace = ($replace ? $this->generateThumbnail($replace, $params[1], $fieldConf['label'][0]) : '');
								break;

						default:;

					}					
				}
				$strFormat = str_replace('{{'.$match.'}}', $replace, $strFormat);
			}
		}
		
		return $strFormat;
	}


	private function generateThumbnail($value, $query, $label)
	{
		// parse query parameters if set
		parse_str($query, $params);
		$src = $params['src'] ? $params['src'] :  $value;

		if (strpos($src, '/') === false)
		{
			$src = sprintf('system/themes/%s/images/%s', $this->getTheme(), $src);
		}

		if (!file_exists(TL_ROOT.'/'.$src))
		{
			return '';
		}

		//$size = getimagesize(TL_ROOT.'/'.$src);
		return '<img src="' . $this->getImage($src, $params['w'], $params['h']) . '" alt="'.specialchars($label).'" />';

	}

	
/*
	public function importAlternateSourceToCollection($varValue, DataContainer $dc)
	{
		$objTableField = $this->Database->prepare("SELECT list_source_table FROM tl_product_attributes WHERE id=?")
										->execute($dc->id);
		
		
		
		if($objTableField->numRows < 1)
		{
			return $varValue;
		}
		
		$objAlternateSourceData = $this->Database->prepare("SELECT id, " . $varValue . " FROM " . $objTableField->list_source_table)
												 ->execute();
												 
		if($objAlternateSourceData->numRows < 1)
		{
			return $varValue;
		}
		
		$arrAlternateSourceData = $objAlternateSourceData->fetchAllAssoc();
		
		foreach($arrAlternateSourceData as $row)
		{
			$arrCollection[] = array
			(
				'value'	=> $row['id'],
				'label'	=> $row[$varValue]
			);
		}
				
						
		$this->Database->prepare("UPDATE tl_product_attributes SET option_list=?, use_alternate_source=0 WHERE id=?")->execute(serialize($arrCollection), $dc->id);
	
		return $varValue;
	}
*/
		
}

