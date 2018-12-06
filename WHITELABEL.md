# Whitelabel Finance Plugin

## Instructions

To fully whitelabel the plugin, the following steps must be carried out:

1. Open the plugin.xml file in the root of the plugin directory
    1.1. Change lines 5 and 6 to the company's preferred plugin name
    1.2. Change line 9 and 10 to the name of the company

2. Open FinancePlugin.php file in the root of the plugin directory
    2.1. Change the description assigned in line 43 to the company's preferred plugin name
    2.2. Change the description assigned in line 49 if necessary

3. Open finance.tpl file in Resources/views/frontend/finance_plugin
    3.1. Replace "divido" with the unique company key in lines 8, 15, 16, 17, 18, 19, 20, 21 & 22

4. Open the buy_container.tpl file in Resources/views/frontend/detail/content
    4.1. Replace "divido" with the unique company key in lines 7, 10, 17, 18, 19, 20, 21, 22, 23, 24 & 25

5. Open the index.tpl file in Resources/views/frontend/custom
    5.1. Replace "divido" with the unique company key in lines 7, 17, 18, 19, 20, 21, 34, 36 & 43

6. Optionally add a plugin.png file with the company logo to the root of the plugin directory