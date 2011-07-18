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


class IsotopeOrder extends IsotopeProductCollection
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_orders';

	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_order_items';

	/**
	 * This current order's unique ID with eventual prefix.
	 */
	protected $strOrderId = '';

	/**
	 * Lock products from apply rule prices
	 * @var bool
	 */
	protected $blnLocked = true;


	public function __get($strKey)
	{
		switch($strKey)
		{
			case 'order_id':
				return $this->strOrderId;

			case 'surcharges':
				return $this->arrData['surcharges'] ? deserialize($this->arrData['surcharges']) : array();

			case 'billingAddress':
				return deserialize($this->arrData['billing_address'], true);

			case 'shippingAddress':
				return deserialize($this->arrData['shipping_address'], true);

			default:
				return parent::__get($strKey);
		}
	}


	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			// Order ID cannot be changed, it is created through IsotopeOrder::generateOrderId on checkout
			case 'order_id':
				throw new Exception('IsotopeOrder order_id cannot be changed trough __set().');
				break;

			default:
				parent::__set($strKey, $varValue);
		}
	}


	/**
	 * Add downloads to this order
	 */
	public function transferFromCollection(IsotopeProductCollection $objCollection, $blnDuplicate=true)
	{
		$arrIds = parent::transferFromCollection($objCollection, $blnDuplicate);

		foreach( $arrIds as $id )
		{
			$objDownloads = $this->Database->execute("SELECT *, (SELECT product_quantity FROM {$this->ctable} WHERE id=$id) AS product_quantity FROM tl_iso_downloads WHERE pid=(SELECT product_id FROM {$this->ctable} WHERE id=$id)");

			while( $objDownloads->next() )
			{
				$arrSet = array
				(
					'pid'					=> $id,
					'tstamp'				=> time(),
					'download_id'			=> $objDownloads->id,
					'downloads_remaining'	=> ($objDownloads->downloads_allowed > 0 ? ($objDownloads->downloads_allowed * $objDownloads->product_quantity) : ''),
				);

				$this->Database->prepare("INSERT INTO tl_iso_order_downloads %s")->set($arrSet)->executeUncached();
			}
		}

		return $arrIds;
	}


	/**
	 * Find a record by its reference field and return true if it has been found
	 * @param  int
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		if (parent::findBy($strRefField, $varRefId))
		{
			$this->Shipping = null;
			$this->Payment = null;

			$objPayment = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE id=" . $this->payment_id);

			if ($objPayment->numRows)
			{
				$strClass = $GLOBALS['ISO_PAY'][$objPayment->type];

				try
				{
					$this->Payment = new $strClass($objPayment->row());
				}
				catch (Exception $e) {}
			}

			if ($this->shipping_id > 0)
			{
				$objShipping = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id=" . $this->shipping_id);

				if ($objShipping->numRows)
				{
					$strClass = $GLOBALS['ISO_SHIP'][$objShipping->type];

					try
					{
						$this->Shipping = new $strClass($objShipping->row());
					}
					catch (Exception $e) {}
				}
			}

			// The order_id must not be stored in arrData, or it would overwrite the database on save().
			$this->strOrderId = $this->arrData['order_id'];
			unset($this->arrData['order_id']);

			return true;
		}

		return false;
	}


	/**
	 * Remove downloads when removing a product
	 */
	public function deleteProduct(IsotopeProduct $objProduct)
	{
		if (parent::deleteProduct($objProduct))
		{
			$this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid={$objProduct->cart_id}");
		}

		return false;
	}


	/**
	 * Also delete downloads when deleting this order.
	 */
	public function delete()
	{
		$this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid IN (SELECT id FROM {$this->ctable} WHERE pid={$this->id})");

		return parent::delete();
	}


	public function getSurcharges()
	{
		$arrSurcharges = deserialize($this->surcharges);
		return is_array($arrSurcharges) ? $arrSurcharges : array();
	}


	public function checkout($objCart=null)
	{
		if ($this->checkout_complete)
		{
			return true;
		}

		$this->import('Isotope');

		// This is the case when not using ModuleIsotopeCheckout
		if (!is_object($objCart))
		{
			$objCart = new IsotopeCart();
			if (!$objCart->findBy('id', $this->cart_id))
			{
				$this->log('Cound not find Cart ID '.$this->cart_id.' for Order ID '.$this->id, __METHOD__, TL_ERROR);
				return false;
			}

			// Set the current system to the language when the user placed the order.
			// This will result in correct e-mails and payment description.
			$GLOBALS['TL_LANGUAGE'] = $this->language;
			$this->loadLanguageFile('default');

			// Initialize system
			$this->Isotope->overrideConfig($this->config_id);
			$this->Isotope->Cart = $objCart;
		}

		// HOOK: process checkout
		if (isset($GLOBALS['ISO_HOOKS']['preCheckout']) && is_array($GLOBALS['ISO_HOOKS']['preCheckout']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['preCheckout'] as $callback)
			{
				$this->import($callback[0]);

				if ($this->$callback[0]->$callback[1]($this, $objCart) === false)
				{
					$this->log('Callback "'.$callback[0].':'.$callback[1].'" cancelled checkout for Order ID '.$this->id, __METHOD__, TL_ERROR);
					return false;
				}
			}
		}

		$arrItemIds = $this->transferFromCollection($objCart);
		$objCart->delete();

		$this->checkout_complete = true;
		$this->status = $this->new_order_status;
		$arrData = $this->email_data;
		$arrData['order_id'] = $this->generateOrderId();

		$this->log('New order ID ' . $this->id . ' has been placed', 'IsotopeOrder checkout()', TL_ACCESS);

		if ($this->iso_mail_admin && $this->iso_sales_email != '')
		{
			$this->Isotope->sendMail($this->iso_mail_admin, $this->iso_sales_email, $this->language, $arrData, $this->iso_customer_email, $this);
		}

		if ($this->iso_mail_customer && $this->iso_customer_email != '')
		{
			$this->Isotope->sendMail($this->iso_mail_customer, $this->iso_customer_email, $this->language, $arrData, '', $this);
		}
		else
		{
			$this->log('Unable to send customer confirmation for order ID '.$this->id, 'IsotopeOrder checkout()', TL_ERROR);
		}

		// Store address in address book
		if ($this->iso_addToAddressbook && $this->pid > 0)
		{
			$time = time();

			foreach( array('billing', 'shipping') as $address )
			{
				$arrData = deserialize($this->arrData[$address.'_address'], true);

				if ($arrData['id'] == 0)
				{
					$arrAddress = array_intersect_key($arrData, array_flip($this->Isotope->Config->{$address.'_fields_raw'}));
					$arrAddress['pid'] = $this->pid;
					$arrAddress['tstamp'] = $time;
					$arrAddress['store_id'] = $this->Isotope->Config->store_id;

					$this->Database->prepare("INSERT INTO tl_iso_addresses %s")->set($arrAddress)->execute();
				}
			}
		}

		// HOOK: process checkout
		if (isset($GLOBALS['ISO_HOOKS']['postCheckout']) && is_array($GLOBALS['ISO_HOOKS']['postCheckout']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['postCheckout'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this, $arrItemIds);
			}
		}

		$this->save();

		return true;
	}


	/**
	 * Generate the next higher Order-ID based on config prefix, order number digits and existing records
	 */
	private function generateOrderId()
	{
		if ($this->strOrderId != '')
			return $this->strOrderId;

		$strPrefix = $this->Isotope->Config->orderPrefix;
		$arrConfigIds = $this->Database->execute("SELECT id FROM tl_iso_config WHERE store_id=" . $this->Isotope->Config->store_id)->fetchEach('id');

		// Lock tables so no other order can get the same ID
		$this->Database->lockTables(array('tl_iso_orders'));

		// Retrieve the highest available order ID
		$objMax = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE order_id LIKE '$strPrefix%' AND config_id IN (" . implode(',', $arrConfigIds) . ") ORDER BY order_id DESC")->limit(1)->executeUncached();
		$intMax = (int)substr($objMax->order_id, strlen($strPrefix));
		$this->strOrderId = $strPrefix . str_pad($intMax+1, $this->Isotope->Config->orderDigits, '0', STR_PAD_LEFT);

		$this->Database->query("UPDATE tl_iso_orders SET order_id='{$this->strOrderId}' WHERE id={$this->id}");
		$this->Database->unlockTables();

		return $this->strOrderId;
	}
}

