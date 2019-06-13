{extends file="parent:frontend/checkout/finish.tpl"}

{block name="frontend_checkout_finish_teaser_title"}
    <h2 class="panel--title teaser--title is--align-center">
    {s namespace="frontend/finance_plugin/404" name="404Title"}Sorry!{/s}
    </h2>
{/block}
{block name="frontend_checkout_finish_teaser_content"}
    <p class="teaser--text is--align-center">
        {s namespace="frontend/content/detail" name="ContentInfoNotFound"}Content could not be found{/s}
    </p>
    <script>
        document.body.classList.add("is--ctl-checkout");
        document.body.classList.add("is--act-finish");
        document.body.classList.add("is--minimal-header");
    </script>
{/block}
{block name="frontend_checkout_finish_information_wrapper"}{/block}
{block name='frontend_checkout_finish_items'}{/block}