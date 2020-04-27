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

<div class="panel">
    <h3><i class="icon icon-tags"></i> {l s='Documentation' mod='phautosignin'}</h3>

    <h2>{l s='Get your Google API client ID' mod='phautosignin'}</h2>
    <p>
        {l s='To use Google\'s streamlined sign-in and sign-up flows, you first need to set up your Google API client ID.' mod='phautosignin'}
    </p>
    <ol>
        <li>
            {l s='Open the "Credentials" page of the ' mod='phautosignin'}
            <a target="_blank" href="https://console.developers.google.com/apis">{l s=' Google APIs console' mod='phautosignin'}</a>
        </li>
        <li>
            {l s='Create or select a Google APIs project. If you already have a Google Sign-In button,
            use the existing project and the web client ID.'
            mod='phautosignin'}
            {l s='If your project doesn\'t have a Web application type client ID, click Create credentials > OAuth client ID to create one.
             Be sure to include your site\'s domain in the Authorized JavaScript origins field. When you perform tests,
            both http://localhost and http://localhost:<port_number> must be added to the Authorized JavaScript origins field.'
            mod='phautosignin'}
        </li>
    </ol>

    <h2>{l s='Configure your OAuth Consent Screen' mod='phautosignin'}</h2>
    <p>
        {l s='Both Google Sign-in and One Tap authentication include a consent screen which tells users the application requesting access to their data,
         what kind of data they are asked for and the terms that apply'
        mod='phautosignin'}
    </p>

    <ol>
        <li>
            {l s='Open the' mod='phautosignin'}
            <a target="_blank" href="https://console.developers.google.com/apis/credentials/consent">{l s='OAuth consent screen' mod='phautosignin'} </a>
            {l s='page of the Google APIs console.' mod='phautosignin'}
        </li>
        <li>
            {l s='If prompted, select the project you just created.' mod='phautosignin'}
        </li>
        <li>
            {l s='On the "OAuth consent screen" page, fill out the form and click the “Save” button.' mod='phautosignin'}
        </li>
    </ol>
</div>
