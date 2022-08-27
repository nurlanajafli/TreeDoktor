var page = require('webpage').create(),
    system = require('system'),
    address, output, size_w, size_h;
//console.log(system.args);
if (system.args.length < 6) {
    console.log('Usage: page.js URL filename size_w size_h selector');
    phantom.exit(1);
} else {
    address = system.args[1];
    output = system.args[2];
    size_w = system.args[3];
    size_h = system.args[4];
    selector = system.args[5];
    page.viewportSize = { width: size_w, height: size_h };
    page.clearMemoryCache();
    page.open(address, function (status) {
        if (status !== 'success') {
            console.log('Unable to load the address!');
        } else {
            window.setTimeout(function () {
                var clipRect = page.evaluate(function (s) {
                    //map.setZoom(map.getZoom() + 1);
	                var cr = document.querySelector(s).getBoundingClientRect();
	                return cr;
                }, selector);

                page.clipRect = {
	                top:    clipRect.top,
	                left:   clipRect.left,
	                width:  clipRect.width,
	                height: clipRect.height
                };
                page.render(output);
                console.log(output);
                phantom.exit();
            }, 2000);
        }
    });
}
