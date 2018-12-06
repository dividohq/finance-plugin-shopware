{extends file="parent:frontend/custom/index.tpl"}

{block name="frontend_index_content"}
{$smarty.block.parent}
{if $apiKey}
<script>
    var dividoKey = "{$apiKey}";
</script>
<style>
#calcWidget{
    display:none;
}
</style>
<script src="http://cdn.divido.com/calculator/v2.1/production/js/template.divido.js"></script>

<div id="calcWidget"
              data-divido-widget
              data-divido-amount="2000"
              data-divido-plans
              data-divido-logo
              data-divido-mode
              >
</div>
{literal}
<script>
var mainCalcWidget = document.getElementById('calcWidget');
var inputs = document.getElementsByClassName('finance-calculator');
for(let k = 0; k < inputs.length; k++){
    let input = inputs[k];
    var calcWidget = mainCalcWidget.cloneNode(true);
    calcWidget.setAttribute('id','financeCalc'+k);
    calcWidget.style.display = 'block';
    input.parentNode.insertBefore(calcWidget, input.nextSibling);
    input.value = calcWidget.getAttribute('data-divido-amount');
    if(input.classList.contains('finance-popup')){
        calcWidget.setAttribute('data-divido-mode','popup');
        calcWidget.style.marginLeft = '50px';
    }
    input
        .addEventListener("keyup",function(event){
            let input = event.target.value;
            if(input >= 250 && input<=25000){
                calcWidget.setAttribute('data-divido-amount',input);
                calcWidget.style.display = 'block';
            }else {
                calcWidget.style.display = 'none';
            }
        });
}
</script>
{/literal}
{/if}
{/block}