
var $house = $(".imag");
var textareaMouseDown = 0;
var textareaMouseUp = 0;
// get the offset position of the kinetic container
var $stageContainer = $("#container");
var stageOffset = $stageContainer.offset();
var offsetX = stageOffset.left;
var offsetY = stageOffset.top;

// create the Kinetic.Stage and layer
var stage = new Kinetic.Stage({ 
    container: 'container', 
    width: 1104, 
    height: 856
});

// grid data
var CELL_SIZE = 40, w = 15, h = 15, W = w * CELL_SIZE, H = h * CELL_SIZE;

// function of drawing the grid in the container

/*
var make_grid = function(layer) {
    var r = new Kinetic.Rect({
        x: 0,
        y: 0,
        width: W,
        height: H,
        fill: "white"
    });
    layer.add(r);
    
    for (i = 0; i < w + 1; i++) {
        var I = i * CELL_SIZE;
        var l = new Kinetic.Line({
            stroke: "white",
            strokeWidth: 0.5,
            points: [I, 0, I, H]
        });
        layer.add(l);
    }

    for (j = 0; j < h + 1; j++) {
        var J = j * CELL_SIZE;
        var l2 = new Kinetic.Line({
            stroke: "white",
            strokeWidth: 0.5,
            points: [0, J, W, J]
        });
        layer.add(l2);
    }
    return r;
};
*/

var layer = new Kinetic.Layer();

/*
var rect = new Kinetic.Rect({
    x: 0,
    y: 0,
    width: CELL_SIZE,
    height: CELL_SIZE,
    fill: "white",
    stroke: "white",
    strokeWidth: 0.5
});
*/
//var gr = make_grid(layer);
// add the shape to the layer
//layer.add(rect);

// add the layer to the stage
stage.add(layer);

/************************************/

function Picture (x, y, name, url, eqpt_id){
    this.x = x;
    this.y = y;
    this.name = name;
    this.url = url;
    this.id = "#house_"+eqpt_id;
}

Pictures = [{"x": Picture.x,
    "y": Picture.y,
    "name": Picture.name,
    "url": Picture.url,
    "eqpt_id": Picture.eqpt_id
}];


var image1 = new Picture(null, null, "television","tv.png",1);
var xi = image1.x;
/************************************/


//create a group
var group = new Kinetic.Group({ draggable: true });
var rec = new Kinetic.Rect({ x: 10, y: 330, width: 602, height: 402 });

group.add(rec);

group.on('mouseover', function() {
    document.body.style.cursor = 'pointer';
});

group.on('mouseout', function() {
    document.body.style.cursor = 'default';
});

layer.add(group);
stage.add(layer);


// make the images draggable
$house.draggable({
    helper: 'clone',
    cursor: 'pointer',
    revert: 'invalid',
});

$(".room").draggable({
    helper: 'clone',
    cursor: 'pointer',
    revert: 'invalid'
});


// make the Kinetic Container a dropzone
$stageContainer.droppable({
    drop: dragDrop
});

// handle a drop into the Kinetic container
function dragDrop(e, ui) {
	
    // get the drop point
    var x = parseInt(ui.offset.left - $('#container').offset().left+2, 10);
    var y = parseInt(ui.offset.top - $('#container').offset().top +80, 10);
    // get the drop payload (here the payload is the image)
    var element = ui.draggable;
    var data = element.data("url");
    var theImage = element.data("image");
    var $this = this;
    // create a new Kinetic.Image at the drop point
    // be sure to adjust for any border width (here border==1)
    var image = new Kinetic.Image({
        name: data,
        x: x,
        y: y,
        image: theImage,
        draggable: true
    });    
    
    image.on('dblclick', function() {
        image.remove();
        layer.draw();
    });

    var $clone = ui.helper.clone();
    if($clone.data('type'))
    {
        var width = $($clone.context).width() + 2;
        var height = $($clone.context).height() + 2;

        var type = $clone.data('type');
        $clone[0] = document.createElement('div');

        $($clone[0]).addClass('imag ui-draggable ui-draggable-dragging ' + type);
        $($clone[0]).append('<div class="imag-head">Move Here</div>');
        $($clone[0]).append('<' + type + '>' + '</' + type + '>');

    }
    // all clones are draggable
    // if clone is shape then draggable + resizable
    if (!$clone.is('.inside-droppable')) {
        $(this).append($clone.addClass('inside-droppable').draggable({
            containment: $stageContainer,
			tolerance: 'fit',
            cursor: 'pointer',
			position: 'relative',    
		}));
        
        if ($clone.is(".imag") === false) {
            $clone.resizable({
				containment: $stageContainer
            });          
        }
        
        $clone.on('dblclick', function () {
            $clone.remove();
            layer.draw();  
            /*
            var imagesStr = '';
            html2canvas($("#container"), {
                useCORS: true,
                onrendered: function(canvas) {
                    $this.setMapScreen(canvas);
                }
            });*/
        });
        
        if($clone.find('textarea').length)
        {
            
            $clone.find('textarea').on('change', function(){
                $(this).text($(this)[0].value);
            });

            $clone.find('textarea').on('blur', function(){
                $(this).text($(this)[0].value);
            });

            $clone.find('textarea').on('mouseout', function(){
                $(this).text($(this)[0].value);
            });
            
            $clone.on('mouseup', function(){
                textareaMouseDown = $(this).width();
            });

            $clone.on('mousedown', function(){textareaMouseDown = $(this).width();});
        }
        $clone.css({top: y, left: x, position:'absolute'});
    }
    group.add(image);
    layer.add(group);
    stage.add(layer);

    /*
    var imagesStr = '';
    html2canvas($("#container"), {
        useCORS: true,
        onrendered: function(canvas) {
            $this.setMapScreen(canvas);
        }
    });*/

    this.setMapScreen = function(canvas){
        var myImage = canvas.toDataURL("image/png");
        img = $("#container .imag");

        if(!img)
            myImage = '';
        
        imgHtml = '';
        $.each(img, function(i, v){ imgHtml += $.trim(v.outerHTML); });

        data = { 'link':EstimateScheme.map_screen_link, 'html':imgHtml };

        document.getElementById('estimate_picture').value = JSON.stringify(data);
        document.getElementById('estimate_scheme').value = myImage;
    }
}