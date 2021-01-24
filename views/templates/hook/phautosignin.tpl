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

{if $phCustomerIsLogged == 0}
    {if $clientId !== 0 && $isGoogleAutoSignInAvailable}
        <div id="g_id_onload"
             data-client_id="{$clientId}"
             data-auto_select="{$phAutoSigninSilently}"
             data-callback="handleCredentialResponse"
             data-cancel_on_tap_outside="{$cancelOnTapOutside}"
             data-prompt_parent_id="g_id_onload"
             style="position: fixed;  z-index: 1001;
             {if $position eq 4}
               bottom: 5px; left: 5px;
            {elseif $position eq 3}
               top: 5px; left: 5px;
            {elseif $position eq 2}
               top: 5px; right: 5px;
            {else}
               bottom: 5px; right: 5px;
            {/if}
             "
             data-context="signin"
             data-skip_prompt_cookie="SID">
        </div>
        <script>
            function handleCredentialResponse(response) {
                // console.log("Credentials is : ", response)
                $.ajax({
                    type: 'POST',
                    url: '{$phSocialLoginUrl}',
                    async: false,
                    cache: false,
                    data: {
                        ajax: 1,
                        credential: response.credential,
                        action: 'ph-auto-signin'
                    },
                    success: function (result) {
                        var objectResult = $.parseJSON(result);

                        // Reload the page
                        if (objectResult.reloadPage){
                            location.reload();
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("TECHNICAL ERROR: \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                    }
                });
            }
        </script>
        <script src="https://accounts.google.com/gsi/client" async></script>

    {/if}
{/if}