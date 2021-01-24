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

$(document).ready(function() {
    // Add class to sign out links that prevenrt autosign in after
    if (!$('a[href*="mylogout"]').hasClass('g_id_signout')) {
    	$('a[href*="mylogout"]').addClass('g_id_signout');
    }

    $(document).on('click', 'a[href*="mylogout"]', function() {
        google.accounts.id.disableAutoSelect();
    });
});