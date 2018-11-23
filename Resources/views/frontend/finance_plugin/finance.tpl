{extends file="frontend/checkout/confirm.tpl"}

{block name="frontend_index_content"}
    <div id="payment content confirm-content">
      <h2>{$title}</h2>
      <p>{$description}</p>
      <script> 
        var dividoKey = "{$apiKey}";
      </script>
      <script src="https://cdn.divido.com/calculator/v2.1/production/js/template.divido.js"></script>
    
      {if $displayForm}
      <form id="dividoFinanceForm" action="{url controller='FinancePlugin' action='direct'}" method="post" >
        <div
          data-divido-widget
          data-divido-prefix="{$prefix}"
          data-divido-suffix="{$suffix}"
          data-divido-title-logo
          data-divido-amount="{$amount}"
          data-divido-apply="true"
          data-divido-apply-label="Apply Now"
          data-divido-plans="{$basket_plans}"
          >
        </div>
        <button id="divido-finance-submit-button" type="submit"
          title="finance"
          class="finance-action btn is--primary"
          data-product-compare-add="true">
          {s namespace='frontend/checkout/shipping_payment' name='NextButton'}Continue to Finance Application{/s}
        </button>
      </form>
      {/if}
      <br />
      {if $displayWarning}
      <ul>
        {foreach item=warning from=$displayWarning}
        <li>{$warning}</li>
        {/foreach}
      </ul>
      {/if}
      <br>
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
        var button = document.querySelectorAll("#divido-finance-submit-button")[0];
        button.addEventListener("click", function(){
          this.setAttribute("disabled", true);
          console.log('true disbaled');
          document.getElementById("dividoFinanceForm").submit();
        })
        console.log('loaded');
      })
    </script>
{/block}
