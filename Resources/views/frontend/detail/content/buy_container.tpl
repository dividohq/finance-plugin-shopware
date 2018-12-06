{extends file="parent:frontend/detail/content/buy_container.tpl"}

{block name="frontend_detail_index_buybox"}
{$smarty.block.parent}
{if $show_widget }
  <script> 
    var dividoKey = "{$apiKey}";
  </script>
  <style>
    #divido-widget{
        padding-bottom:5px;
    }
  </style>

  <script src="https://cdn.divido.com/calculator/v2.1/production/js/template.divido.js"></script>
  <div
    id="divido-widget"
    data-divido-widget
    data-divido-mode="popup"
    data-divido-plans="{$plans}"
    data-divido-prefix="{$prefix}"
    data-divido-suffix="{$suffix}"
    data-divido-amount="{$sArticle.price|replace:',':'.'}"
    data-divido-apply="true"
    data-divido-apply-label="Apply Now"
  >
  </div>
{/if}
{/block}


