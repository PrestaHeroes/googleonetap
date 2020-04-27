<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    PrestaHeroes <info@prestaheroes.com>
 *  @copyright 2020 Heroic Business Solutions LLC
 *  @license   LICENSE.txt
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'phadvancedcheckout` (
    `id_phadvancedcheckout` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY  (`id_phadvancedcheckout`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
