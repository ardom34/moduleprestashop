<?php
/**
 * Created by PhpStorm.
 * User: adomalain
 * Date: 25/09/2017
 * Time: 14:39
 */

/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
if (!defined('_PS_VERSION_'))
    exit;

class DomalainArpa3 extends Module
{
    public function __construct()
    {
        $this->name = 'domalainarpa3';
        $this->tab = 'front_office_features';
        $this->version = '1.2';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = $this->l('Domalain Prestashop');
        $this->description = $this->l('Add possibility to notify stock');
    }

    public function getEmail()
    {
        return '
		<form action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '" method="post">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Settings') . '</legend>
				<label>' . $this->l('Ajax cart') . '</label>
				<div class="margin-form">
					<input type="radio" name="cart_ajax" id="ajax_on" value="1" ' . (Tools::getValue('cart_ajax', Configuration::get('PS_BLOCK_CART_AJAX')) ? 'checked="checked" ' : '') . '/>
					<label class="t" for="ajax_on"> <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" /></label>
					<input type="radio" name="cart_ajax" id="ajax_off" value="0" ' . (!Tools::getValue('cart_ajax', Configuration::get('PS_BLOCK_CART_AJAX')) ? 'checked="checked" ' : '') . '/>
					<label class="t" for="ajax_off"> <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" /></label>
					<p class="clear">' . $this->l('Activate AJAX mode for cart (compatible with the default theme)') . '</p>
				</div>
				<center><input type="submit" name="submitBlockCart" value="' . $this->l('Save') . '" class="button" /></center>
			</fieldset>
		</form>';
    }

    public function install()
    {
        if (
            parent::install() == false
            || $this->registerHook('updateQuantity') == false
        )
            return false;
        return true;
    }

    public function hookUpdateQuantity($params)
    {
        global $cookie;
        $qty = (int)$params['product']['quantity'];
        if ($qty <= (int)(Configuration::get('MA_LAST_QTIES')) AND !(!$this->_merchant_oos OR empty($this->_merchant_mails)) AND Configuration::get('PS_STOCK_MANAGEMENT')) {
        }

        $mailParams = array(
            '{qty}' => $qty,
            '{last_qty}' => (int)(Configuration::get('MA_LAST_QTIES')),
            '{product}' => strval($params['product']['name']) . (isset($params['product']['attributes_small']) ? ' ' . $params['product']['attributes_small'] : ''));
        $id_lang = (is_object($cookie) AND isset($cookie->id_lang)) ? (int)$cookie->id_lang : (int)Configuration::get('PS_LANG_DEFAULT');
        $lang = Language::getIsoById((int)$id_lang);

        if ($params['product']['active'] == 1) {
            if (file_exists(dirname(__FILE__) . '/mails/' . $lang . '/productoutofstock.txt') AND file_exists(dirname(__FILE__) . '/mails/' . $lang . '/productoutofstock.html'))
                Mail::Send((int)Configuration::get('PS_LANG_DEFAULT'), 'productoutofstock', Mail::l('Product out of stock', (int)Configuration::get('PS_LANG_DEFAULT')), $mailParams, explode(self::__MA_MAIL_DELIMITOR__, $this->_merchant_mails), NULL, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__) . '/mails/');
        }
    }
}
