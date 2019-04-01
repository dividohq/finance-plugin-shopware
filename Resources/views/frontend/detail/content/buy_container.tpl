{extends file="parent:frontend/detail/content/buy_container.tpl"}

{block name="frontend_detail_index_buybox"}
{$smarty.block.parent}
<h1>{$env}</h1>
{if $show_widget }
  <script>
    var {$env}Key = "{$apiKey}";
  </script>
  <style>
    #{$env}-widget{
        padding-bottom:5px;
    }
  </style>

  <script src="https://cdn.divido.com/calculator/v2.1/production/js/template.{$env}.js"></script>
  <div
    id="{$env}-widget"
    data-{$env}-widget
    data-{$env}-mode="popup"
    data-{$env}-plans="{$plans}"
    data-{$env}-prefix="{$prefix}"
    data-{$env}-suffix="{$suffix}"
    data-{$env}-amount="{$sArticle.price|replace:',':'.'}"
    data-{$env}-apply="true"
    data-{$env}-apply-label="Apply Now"
  >
  </div>
{/if}
{/block}