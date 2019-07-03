{extends file="frontend/checkout/confirm.tpl"}

{block name="frontend_index_content"}
    <div id="payment content confirm-content">
      <h2>{$title}</h2>
      <p>{$description}</p>
      <script>
        var {$env}Key = "{$apiKey}";
      </script>
      <script src="https://cdn.divido.com/calculator/v2.1/production/js/template.{$env}.js"></script>

      {if $displayForm}
      <form id="dividoFinancePluginForm" action="{url controller='dividoFinancePlugin' action='direct'}" method="post" >
        <div
          data-{$env}-widget
          data-{$env}-prefix="{$prefix}"
          data-{$env}-suffix="{$suffix}"
          data-{$env}-title-logo
          data-{$env}-amount="{$amount}"
          data-{$env}-apply="true"
          data-{$env}-apply-label="Apply Now"
          data-{$env}-plans="{$basket_plans}"
          >
        </div>
        <button id="finance-plugin-submit-button" type="submit"
          title="finance"
          class="finance-action btn is--primary"
          data-product-compare-add="true">
          {s namespace='frontend/checkout/shipping_payment' name='NextButton'}Continue{/s}
        </button>
      </form>
      {/if}
      {if $displayWarning}
      <h3>Sorry.</h3>
      <ul style='margin-left: 20px'>
        {foreach item=warning from=$displayWarning}
        <li style='list-style:none'>{$warning}</li>
        {/foreach}
      </ul>
      {/if}
      <br />
      <br />
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
    <script>
      document.body.classList.add("is--ctl-checkout");
      document.body.classList.add("is--act-confirm");
      document.body.classList.add("is--minimal-header");
      document.addEventListener("DOMContentLoaded", function(

      ){
        var button = document.querySelectorAll("#finance-plugin-submit-button")[0];
        button.addEventListener("click", function(){
          this.setAttribute("disabled", true);
          document.getElementById("dividoFinancePluginForm").submit();
        })
      })
    </script>
{/block}
