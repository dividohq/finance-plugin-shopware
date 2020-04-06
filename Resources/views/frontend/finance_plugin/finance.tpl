{extends file="frontend/checkout/confirm.tpl"}

{block name="frontend_index_content"}
    <div id="payment content confirm-content">
      <h2>{$title}</h2>
      <p>{$description}</p>

      {if $displayForm}
      <form id="financePluginForm" action="{url controller='FinancePlugin' action='direct'}" method="post" >
        <div
            data-calculator-widget
            data-mode="calculator"
            data-amount="{$amount*100}"
            data-plans="{$basket_plans}"
            >
        </div>
        <script>
            __widgetConfig = {
                apiKey: '{$apiKey}'
            }
        </script>
        <script src="https://cdn.divido.com/widget/dist/{$env}.calculator.js"></script>
        <button id="finance-plugin-submit-button" type="submit"
          title="finance"
          class="finance-action btn is--primary"
          data-product-compare-add="true">
          {s namespace='frontend/checkout/shipping_payment' name='NextButton'}Continue{/s}
        </button>
      </form>
      {/if}
      {if $displayWarning}
      <h3>{s namespace="global" name="error_title_short"}Error{/s}:</h3>
      <ul style='margin-left: 20px'>
        {foreach item=warning from=$displayWarning}
            <li>{$warning|snippet:$warning:'frontend/checkout/error'}</li>
        {/foreach}
      </ul>
      {/if}
      <br />
      <br />
      <a class="btn"
          href="{url controller=checkout action=cart}"
          title="{'change cart'|snippet:'back_to_cart_label':'frontend/finance_plugin/finance'}">
          {"change cart"|snippet:'back_to_cart_label':'frontend/finance_plugin/finance'|lower}
      </a>
      <a class="btn right"
          href="{url controller=checkout action=shippingPayment sTarget=checkout}"
          title="{'change payment method'|snippet:'alt_payment_methods_label':'frontend/finance_plugin/finance'}">
          {"change payment method"|snippet:'alt_payment_methods_label':'frontend/finance_plugin/finance'}
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
          document.getElementById("financePluginForm").submit();
        })
      })
    </script>
{/block}
