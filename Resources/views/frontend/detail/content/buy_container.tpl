{extends file="parent:frontend/detail/content/buy_container.tpl"}

{block name="frontend_detail_index_buybox"}
{$smarty.block.parent}
{if $show_widget }
  <div
    data-calculator-widget
    data-plans="{$plans}"
    data-amount="{$sArticle.price|replace:',':''|replace:'.':''}"
    {$widget_mode}
    {$widget_footnote}
  >
  </div>

  <script>
    __widgetConfig = {
      apiKey: '{$apiKey}'
    }
  </script>

  <script src="https://cdn.divido.com/widget/dist/{$env}.calculator.js"></script>
{/if}
{/block}