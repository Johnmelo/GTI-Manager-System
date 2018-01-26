/**
* Converts :hover CSS to :active CSS on mobile devices.
* Otherwise, when tapping a button on a mobile device, the button stays in
* the :hover state until the button is pressed.
*
* Inspired by: https://gist.github.com/rcmachado/7303143
* @author  Michael Vartan <michael@mvartan.com>
* @version 1.0
* @date    2014-12-20
*/
// Check if the device supports touch events
if('ontouchstart' in document.documentElement) {
    // Loop through each stylesheet
    for(var sheetI = document.styleSheets.length - 1; sheetI >= 0; sheetI--) {
        var sheet = document.styleSheets[sheetI];
        // Verify if cssRules exists in sheet
        if(sheet.cssRules) {
            // Loop through each rule in sheet
            for(var ruleI = sheet.cssRules.length - 1; ruleI >= 0; ruleI--) {
                var rule = sheet.cssRules[ruleI];
                // Verify rule has selector text
                if(rule.selectorText) {
                    // Replace hover psuedo-class with active psuedo-class

                    // To include Firefox, it's necessary to rebuild the entire rule.
                    // Setting of selectorText is unsupported on Firefox.
                    var old_rule = rule.cssText;
                    sheet.deleteRule(ruleI);
                    old_rule = old_rule.replace(/:hover/g, ":active");
                    sheet.insertRule(old_rule, ruleI);
                }
            }
        }
    }
}
