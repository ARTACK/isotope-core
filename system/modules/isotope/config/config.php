<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

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


/**
 * Isotope Version
 */
@define('ISO_VERSION', '1.3');
@define('ISO_BUILD', 'beta1');


/**
 * Backend modules
 */
if (!is_array($GLOBALS['BE_MOD']['isotope']))
{
	array_insert($GLOBALS['BE_MOD'], 1, array('isotope' => array()));
}

array_insert($GLOBALS['BE_MOD']['isotope'], 0, array
(
	'iso_products' => array
	(
		'tables'					=> array('tl_iso_products', 'tl_iso_groups', 'tl_iso_product_categories', 'tl_iso_downloads', 'tl_iso_related_products', 'tl_iso_prices', 'tl_iso_price_tiers'),
		'icon'						=> 'system/modules/isotope/html/store-open.png',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
		'generate'					=> array('tl_iso_products', 'generateVariants'),
		'quick_edit'				=> array('tl_iso_products', 'quickEditVariants'),
		'import'					=> array('tl_iso_products', 'importAssets'),
	),
	'iso_orders' => array
	(
		'tables'					=> array('tl_iso_orders', 'tl_iso_order_items'),
		'icon'						=> 'system/modules/isotope/html/shopping-basket.png',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
		'export_emails'     		=> array('tl_iso_orders', 'exportOrderEmails'),
		'print_order'				=> array('tl_iso_orders', 'printInvoice'),
		'print_invoices'			=> array('tl_iso_orders', 'printInvoices'),
		'payment'					=> array('tl_iso_orders', 'paymentInterface'),
		'shipping'					=> array('tl_iso_orders', 'shippingInterface'),
	),
	'iso_setup' => array
	(
		'callback'					=> 'ModuleIsotopeSetup',
		'tables'					=> array(),
		'icon'						=> 'system/modules/isotope/html/application-monitor.png',
	),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_iso_addresses';

if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/backend.css';
}


/**
 * Isotope Modules
 */
$GLOBALS['ISO_MOD'] = array
(
	'product' => array
	(
		'producttypes' => array
		(
			'tables'					=> array('tl_iso_producttypes'),
			'icon'						=> 'system/modules/isotope/html/drawer.png'
		),
		'attributes' => array
		(
			'tables'					=> array('tl_iso_attributes'),
			'icon'						=> 'system/modules/isotope/html/table-insert-column.png',
		),
		'related_categories' => array
		(
			'tables'					=> array('tl_iso_related_categories'),
			'icon'						=> 'system/modules/isotope/html/category.png',
		),
	),
	'checkout' => array
	(
		'payment' => array
		(
			'tables'					=> array('tl_iso_payment_modules'),
			'icon'						=> 'system/modules/isotope/html/money-coin.png',
		),
		'shipping' => array
		(
				'tables'				=> array('tl_iso_shipping_modules','tl_iso_shipping_options'),
				'icon'					=> 'system/modules/isotope/html/box-label.png',
		),
		'tax_class' => array
		(
			'tables'					=> array('tl_iso_tax_class'),
			'icon'						=> 'system/modules/isotope/html/globe.png',
		),
		'tax_rate' => array
		(
			'tables'					=> array('tl_iso_tax_rate'),
			'icon'						=> 'system/modules/isotope/html/calculator.png',
		),
	),
	'config' => array
	(
		'iso_mail' => array
		(
			'tables'					=> array('tl_iso_mail', 'tl_iso_mail_content'),
			'icon'						=> 'system/modules/isotope/html/inbox-document-text.png',
			'importMail'				=> array('IsotopeBackend', 'importMail'),
			'exportMail'				=> array('IsotopeBackend', 'exportMail'),
		),
		'configs' => array
		(
			'tables'					=> array('tl_iso_config'),
			'icon'						=> 'system/modules/isotope/html/construction.png',
		),
	)
);

// Enable tables in iso_setup
if ($_GET['do'] == 'iso_setup')
{
	foreach ($GLOBALS['ISO_MOD'] as $strGroup=>$arrModules)
	{
		foreach ($arrModules as $strModule => $arrConfig)
		{
			if (is_array($arrConfig['tables']))
			{
				$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] = array_merge($GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'], $arrConfig['tables']);
			}
		}
	}
}


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope'] = array
(
	'iso_productlist'			=> 'ModuleIsotopeProductList',
	'iso_productvariantlist'	=> 'ModuleIsotopeProductVariantList',
	'iso_productreader'			=> 'ModuleIsotopeProductReader',
	'iso_cart'					=> 'ModuleIsotopeCart',
	'iso_checkout'				=> 'ModuleIsotopeCheckout',
	'iso_productfilter'			=> 'ModuleIsotopeProductFilter',
	'iso_orderhistory'			=> 'ModuleIsotopeOrderHistory',
	'iso_orderdetails'			=> 'ModuleIsotopeOrderDetails',
	'iso_configswitcher'		=> 'ModuleIsotopeConfigSwitcher',
	'iso_addressbook'			=> 'ModuleIsotopeAddressBook',
	'iso_relatedproducts'		=> 'ModuleIsotopeRelatedProducts',
);


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager']			= 'MediaManager';
$GLOBALS['BE_FFL']['attributeWizard']		= 'AttributeWizard';
$GLOBALS['BE_FFL']['surchargeWizard']		= 'SurchargeWizard';
$GLOBALS['BE_FFL']['variantWizard']			= 'VariantWizard';
$GLOBALS['BE_FFL']['inheritCheckbox']		= 'InheritCheckBox';
$GLOBALS['BE_FFL']['imageWatermarkWizard']	= 'ImageWatermarkWizard';
$GLOBALS['BE_FFL']['fieldWizard']			= 'FieldWizard';
$GLOBALS['BE_FFL']['productTree']			= 'ProductTree';

// This widget belongs to the core, but if extension "calendar" is disable it wont be available without this
// @todo remove this when dropping support for Contao 2.9
$GLOBALS['BE_FFL']['timePeriod']			= 'TimePeriod';


/**
 * Shipping modules
 */
$GLOBALS['ISO_SHIP']['flat']		 = 'ShippingFlat';
$GLOBALS['ISO_SHIP']['order_total']	 = 'ShippingOrderTotal';
$GLOBALS['ISO_SHIP']['weight_total'] = 'ShippingWeightTotal';
$GLOBALS['ISO_SHIP']['ups']			 = 'ShippingUPS';
$GLOBALS['ISO_SHIP']['usps']		 = 'ShippingUSPS';


/**
 * Payment modules
 */
$GLOBALS['ISO_PAY']['cash']						= 'PaymentCash';
$GLOBALS['ISO_PAY']['paypal']					= 'PaymentPaypal';
$GLOBALS['ISO_PAY']['paypalpro']				= 'PaymentPaypalPro';
$GLOBALS['ISO_PAY']['paypalpayflowpro']			= 'PaymentPaypalPayflowPro';
$GLOBALS['ISO_PAY']['postfinance']				= 'PaymentPostfinance';
$GLOBALS['ISO_PAY']['authorizedotnet']			= 'PaymentAuthorizeDotNet';
$GLOBALS['ISO_PAY']['cybersource']				= 'PaymentCybersource';


/**
 * Galleries
 */
$GLOBALS['ISO_GAL']['default']					= 'IsotopeGallery';
$GLOBALS['ISO_GAL']['inline']					= 'InlineGallery';


/**
 * Product types
 */
$GLOBALS['ISO_PRODUCT'] = array
(
	'regular' => array
	(
		'class'	=> 'IsotopeProduct',
	),
);


/**
 * Order Statuses
 */
$GLOBALS['ISO_ORDER'] = array('pending', 'processing', 'complete', 'on_hold', 'cancelled');


/**
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configs';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_product_types';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_modules';


/**
 * Number formatting
 */
$GLOBALS['ISO_NUM']["10000.00"]		= array(2, '.', "");
$GLOBALS['ISO_NUM']["10,000.00"]	= array(2, '.', ",");
$GLOBALS['ISO_NUM']["10.000,00"]	= array(2, ',', ".");
$GLOBALS['ISO_NUM']["10'000.00"]	= array(2, '.', "'");


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][]			= array('Isotope', 'loadProductsDataContainer');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]			= array('Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]			= array('IsotopeFrontend', 'replaceIsotopeTags');
$GLOBALS['TL_HOOKS']['generatePage'][]				= array('IsotopeFrontend', 'injectMessages');
$GLOBALS['TL_HOOKS']['executePreActions'][]			= array('ProductTree', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][]		= array('ProductTree', 'executePostActions');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][]	= array('IsotopeFrontend', 'translateProductUrls');
$GLOBALS['ISO_HOOKS']['buttons'][]					= array('Isotope', 'defaultButtons');


/**
 * Checkout surcharge calculation callbacks
 */
$GLOBALS['ISO_HOOKS']['checkoutSurcharge'][] = array('IsotopeCart', 'getShippingSurcharge');
$GLOBALS['ISO_HOOKS']['checkoutSurcharge'][] = array('IsotopeCart', 'getPaymentSurcharge');


/**
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = array('IsotopeAutomator', 'deleteOldCarts');


/**
 * Step callbacks for checkout module
 */
$GLOBALS['ISO_CHECKOUT_STEPS'] = array
(
	'address' => array
	(
		array('ModuleIsotopeCheckout', 'getBillingAddressInterface'),
		array('ModuleIsotopeCheckout', 'getShippingAddressInterface'),
	),
	'shipping' => array
	(
		array('ModuleIsotopeCheckout', 'getShippingModulesInterface'),
	),
	'payment' => array
	(
		array('ModuleIsotopeCheckout', 'getPaymentModulesInterface'),
	),
	'review' => array
	(
		array('ModuleIsotopeCheckout', 'getOrderReviewInterface'),
		array('ModuleIsotopeCheckout', 'getOrderConditionsInterface')
	),
);

$GLOBALS['ISO_ATTR'] = array
(
	'text' => array
	(
		'sql'		=> "varchar(255) NOT NULL default ''",
	),
	'textarea' => array
	(
		'sql'		=> "text NULL",
	),
	'select' => array
	(
		'sql'		=> "blob NULL",
	),
	'radio' => array
	(
		'sql'		=> "blob NULL",
	),
	'checkbox' => array
	(
		'sql'		=> "blob NULL",
	),
	'conditionalselect' => array
	(
		'sql'		=> "blob NULL",
		'callback'	=> array(array('Isotope', 'mergeConditionalOptionData')),
	),
	'mediaManager' => array
	(
		'sql'		=> "blob NULL",
	),
);


/**
 * URL Keywords for FolderURL extension
 */
$GLOBALS['URL_KEYWORDS'][] = 'product';
$GLOBALS['URL_KEYWORDS'][] = 'step';


/**
 * Default configuration
 */
$GLOBALS['TL_CONFIG']['iso_cartTimeout'] = 2592000;

