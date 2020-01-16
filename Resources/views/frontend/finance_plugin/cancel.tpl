{extends file='frontend/index/index.tpl'}

{* Main content *}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_content'}
    {if $errors}
    <h1>{s namespace="global" name="error_title_short"}Ooops{/s}</h1>
    <ul style='list-style:none'>
    {foreach item=error key=$key from=$errors}
        <li>{$error|snippet:$key:'frontend/checkout/error'}</li>
    {/foreach}
    </ul>
    {/if}
    <div class="example-content content custom-page--content">
        <div class="example-content--actions">
            <a class="btn"
                href="{url controller=checkout action=cart}"
                title="{'change cart'|snippet:'back_to_cart_label':'frontend/finance_plugin/finance'|lower}">
                {"change cart"|snippet:'back_to_cart_label':'frontend/finance_plugin/finance'|lower}
            </a>
            <a class="btn right"
                href="{url controller=checkout action=shippingPayment sTarget=checkout}"
                title="{'change payment method'|snippet:'alt_payment_methods_label':'frontend/finance_plugin/finance'}">
                {"change payment method"|snippet:'alt_payment_methods_label':'frontend/finance_plugin/finance'}
            </a>
        </div>
    </div>
{/block}

{block name='frontend_index_actions'}{/block}
