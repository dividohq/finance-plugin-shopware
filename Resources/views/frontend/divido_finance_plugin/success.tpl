{extends file="frontend/checkout/finish.tpl"}

{block name="frontend_checkout_finish_teaser_content"}
    {$smarty.block.parent}
    <script>
        document.body.classList.add("is--ctl-checkout");
        document.body.classList.add("is--act-finish");
        document.body.classList.add("is--minimal-header");
    </script>
{/block}