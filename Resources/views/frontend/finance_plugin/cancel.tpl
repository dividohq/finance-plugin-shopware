{extends file='frontend/index/index.tpl'}

{* Main content *}

{block name='frontend_index_content_left'}{/block}

{block name='frontend_index_content'}
    {if $errors}
    <h1>{s namespace="frontend/checkout/error" name="order_creation_error_msg "}Ooops{/s}</h1>
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
                title="{s namespace="frontend/checkout/cart" name="CartTitle"}change cart{/s}">
                {s namespace="frontend/checkout/cart" name="CartTitle"}change cart{/s}
            </a>
            <a class="btn right"
                href="{url controller=checkout action=shippingPayment sTarget=checkout}"
                title="{s namespace="frontend/checkout/shipping_payment" name="ChangePaymentTitle"}{/s}">
                {s namespace="frontend/checkout/shipping_payment" name="ChangePaymentTitle"}change payment method{/s}
            </a>
        </div>
    </div>
{/block}

{block name='frontend_index_actions'}{/block}
