# Finance Plugin

# Installation

For manual installation of the most recent version of the plugin follow all steps.

Login to your shopware backend, and open the plugin manager.

1. Click upload plugin
2. Select the provided Zip File
3. Click Upload Plugin
4. You should now see Finance Plugin in the Uninstalled plugin list
5. Click the green install plugin button
6. Hit install
7. Enter your configuration details
8. Click Save
9. Click Activate
10. Click Configuration > Payment Methods
11. Click into the Finance Plugin method
12. Click Active to selected
13. Hit Save - The plugin is now installed configured and active you may also need to clear your caches for the plugin to be visible 
14. To clear the cache go to Configuration > Cache/Performance 

## Custom Finance Calculator

You also have the ability to incorporate Divido widgets into your custom pages. By following the instructions below you can generate one of two types of finance calculator, which will update automatically based on the figure entered into a text box.

1. Enter the Backend of your online shop
2. Go to the "Shop pages" subsection of the "Content" section
3. Select the directory and the page you would like to edit / add a new page to the shop
4. Click on the HTML Source Editor button in the text editor
5. Insert the html for an input field, including the class name ‘finance-calculator’ (ie. <input type='number' class='finance-calculator' />). This will generate the block version of the widget (fig.9)
6. If you wish to use a pop-up version of the widget (fig.10), include the class ‘finance-popup’ also (ie. <input type='number' class='finance-calculator finance-popup' />)
7. Click on the Update button in the HTML Source Editor window
8. Click on the Save button on the Shop pages page
9. The block payment calculator widget will generate directly below the input box
10. If you have chosen the popup option (as outlined in point 6), a small area of text will be generated underneath the input box which can be clicked on to obtain the full list of payment options available to the customer

Please be aware that you may experience technical issues if you try to create more than one finance calculator on a page.

