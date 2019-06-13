{extends file="parent:frontend/checkout/finish.tpl"}

{block name="frontend_checkout_finish_teaser_title"}<h2 class="panel--title teaser--title is--align-center">
    {s namespace="frontend/error/index" name="ErrorIndexTitle"}Sorry!{/s}
</h2>{/block}
{block name="frontend_checkout_finish_teaser_content"}
    <p class="teaser--text is--align-center">
        {$error|snippet:$snippetKey:'frontend/finance_plugin/error'}
    </p>
    <script>
        document.body.classList.add("is--ctl-checkout");
        document.body.classList.add("is--act-finish");
        document.body.classList.add("is--minimal-header");
    </script>
{/block}
{block name="frontend_checkout_finish_information_wrapper"}{/block}
{block name='frontend_checkout_finish_items'}{/block}