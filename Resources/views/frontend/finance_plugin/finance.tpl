{extends file="frontend/checkout/confirm.tpl"}

{block name="frontend_index_content"}
    <div id="payment content confirm-content">
      {if $display_form}
      <h2>{$title}</h2>
      <p>{$description}</p>
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
          title="{s namespace='global' name='continue_label'}Continue{/s}"
          class="finance-action btn is--primary"
          data-product-compare-add="true">
          {s namespace='global' name='continue_label'}Continue{/s}
        </button>
      </form>
      { else }
      <h3>{s namespace="frontend/checkout/error" name="error_title_short"}Error{/s}</h3>
      <ul style='margin-left: 20px'>
        {if $minCartWarning}
          <li style='list-style:none'>
              {s namespace="frontend/checkout/error" name="minimum_price_error_msg"}
              Cart does not meet minimum amount required for this payment method
              {/s}
          </li>
        {/if}
        {if $maxCartWarning}
          <li style='list-style:none'>
              {s namespace="frontend/checkout/error" name="maximum_price_error_msg"}
              The cart total exceeds the amount that can be purchased with this payment method
              {/s}
          </li>
        {/if}
        {if $addressWarning}
          <li style='list-style:none'>
              {s namespace="frontend/checkout/error" name="address_mismatch_error_msg"}
              Your shipping and billing addresses must match for this payment method
              {/s}
          </li>
        {/if}
        {if $emptyPlansWarning}
          <li style='list-style:none'>
              {s namespace="frontend/checkout/error" name="empty_plans_error_msg"}
              We cannot find any finance plans available for your checkout.
              Please complete the order with another payment option.
              {/s}
          </li>
        {/if}
        {if $genericWarning}
          <li style='list-style:none'>
              {s namespace="frontend/checkout/error" name="default_api_error_msg"}
              We are unable to process this order with the chosen payment method.
              Please choose another method
              {/s}
          </li>
        {/if}
      </ul>
      {/if}
      <br />
      <br />
      <a class="btn"
          href="{url controller=checkout action=cart}"
          title='{s namespace="frontend/finance_plugin/finance" name="back_to_cart_label"}Back to cart{/s}'>
          {s namespace="frontend/finance_plugin/finance" name="back_to_cart_label"}Back to cart{/s}
      </a>
      <a class="btn right"
          href="{url controller=checkout action=shippingPayment sTarget=checkout}"
          title='{s namespace="frontend/finance_plugin/finance" name="alt_payment_methods_label"}change payment method{/s}>
          {s namespace="frontend/finance_plugin/finance" name="alt_payment_methods_label"}change payment method{/s}
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
