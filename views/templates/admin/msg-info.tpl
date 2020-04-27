{*
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
*}

{if isset($message) && $message eq 1}
    <div class="conf confirm alert alert-success">{l s='Saved successful' mod='phautosignin'}</div>
{/if}

{*
{if $message eq 2}
    <div class="conf confirm alert alert-danger">{l s='Error: ip address is empty' mod='phautosignin'}</div>
{/if}*}
