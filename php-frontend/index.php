<!DOCTYPE html>
<head>
	<title>robomap te escucha...</title>

	<script src="http://cdnjs.cloudflare.com/ajax/libs/gsap/latest/TweenMax.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
	<script src="http://d3js.org/d3.v3.min.js"></script>
	<script type="text/javascript">
function ArgenmapLiveGraph(opciones)
{
	/*
	necesito una manera de inicializar con opcion de long polling
	para los af que no implementan streaming/eventsource
	*/
	this.longPolling = false;
	this.pollData = [];
	this.w = 400;
	this.h = 200;
	this.leftPadd = 20;
	this.rightPadd = 100;
	this.padding = 20;
	this.MAX_VALUE_Y = 10;
	this.MAX_POLLS = 50;
	this.selector = "#graph";
	this.lineColor = 'blue';
	this.lapCounter = 0;
	this.POLL_INTERVAL = 2000;
	this.sourceUrl = 'argenmap_live.php';
	this.SESSION_SUFFIX = 'polling=true'

	this.svg; //elemento d3 svg
	this.container; //container general (g)
	this.lineContainer; //container de la linea (g)
	this.realTimeBoard; //container de la cantidad
	this.labelRequestsNuevos;
	this.eventSource;
	this.linearScaleY;
	this.linearScaleX;
	this.lineFunction;
	this.yAxis;
	$.extend(this,opciones);
	this.initialize();
}
ArgenmapLiveGraph.prototype.initialize = function()
{
	this.buildScales();

	this.lineFunction = d3.svg.line()
		.x( $.proxy(function(d,i) { return this.linearScaleX(i); }),this)
		.y( $.proxy(function(d) { return this.linearScaleY(d.requestsNuevos); }),this)
		.interpolate("monotone");
	
	this.svg = d3.select(this.selector).append('svg')
		.attr('width',this.w)
		.attr('height',this.h)
		.style('background-color','black');

	this.container = this.svg.append('g')
		.attr('class','graph-container');

	this.axisContainer = this.svg.append('g')
		.attr('class','axis')
		.attr('opacity','0.3')
		.attr('transform','translate('+ (this.w) +','+ (this.h - this.padding) +')');

	this.buildAxis();

	this.lineContainer = this.svg.append('g')
		.attr('transform','translate(0,'+ (this.h - this.padding) +')')
		.attr('class','line-container');

	this.realTimeBoard = this.svg.append('g')
		.attr('transform','translate('+(this.w - this.rightPadd)+','+this.h / 2+')')
		.attr('class','label-container');
	this.labelRequestsNuevos = this.realTimeBoard.append('text')
		.attr('fill','white')
		.attr('font-size',80)
		.attr('font-family','Arial, Helvetica, sans-serif')
		.attr('style','opacity:0.6')
		.text('0');

	this.connect();
	
}
ArgenmapLiveGraph.prototype.getData = function()
{
	var live = this;
	var q = this.pollData.length ? 
		'?sid='+this.pollData[this.pollData.length - 1].processData.sessionId + '&' + this.SESSION_SUFFIX:
		'?' + this.SESSION_SUFFIX;
	$.ajax({
		url: this.sourceUrl + q,
		complete: function(xhr,status) {
			//este kludge es horrible, pero es lo unico que tengo
			var a = xhr.responseText.substr(xhr.responseText.indexOf('data:') + 5);
			
			live.updateDataHandler({data:a});
			if(live.longPolling) {
				setTimeout($.proxy(live.getData,live),live.POLL_INTERVAL);
			}
		}
	});
}
ArgenmapLiveGraph.prototype.connect = function()
{
	if(this.longPolling)
	{
		this.getData();
	}else{
		this.eventSource = new EventSource(this.sourceUrl);
		this.eventSource.addEventListener('ping', $.proxy(this.updateDataHandler,this), false);
	}
}
ArgenmapLiveGraph.prototype.buildScales = function()
{
	this.linearScaleY = d3.scale.linear().domain([0,this.MAX_VALUE_Y]).range([0,-this.h + this.padding * 2]);
	this.linearScaleX = d3.scale.linear().domain([0,this.MAX_POLLS]).range([this.leftPadd,this.w - this.rightPadd]);
}
ArgenmapLiveGraph.prototype.buildAxis = function()
{
	this.axisContainer.selectAll('g').remove();
	this.axisContainer.selectAll('path').remove();

	this.yAxis = d3.svg.axis()
		.scale(this.linearScaleY)
		.orient("left")
		.ticks(6)
		.tickSize(this.w - this.leftPadd);


	this.axisContainer.call(this.yAxis);

	this.axisContainer.selectAll('path')
		.style('fill', 'none')
		.style('stroke', '#EEE')
		.style('shape-rendering', 'crispEdges')
		.style('stroke-opacity', '0.5');
	this.axisContainer.selectAll('line')
		.style('fill', 'none')
		.style('stroke', '#EEE')
		.style('shape-rendering', 'crispEdges')
		.style('stroke-opacity', '0.5');
	this.axisContainer.selectAll('text')
		.style('font-family','sans-serif')
	    .style('font-size', '11px')
	    .style('fill','white');

}
ArgenmapLiveGraph.prototype.updateDataHandler = function(datos)
{
	// console.log(datos);
	var jsonData = $.parseJSON(datos.data);
	// console.log(jsonData);
	this.lapCounter = ++this.lapCounter % this.MAX_POLLS;
	this.pollData.push(jsonData);
	if(this.pollData.length > this.MAX_POLLS) this.pollData.shift();
	if(jsonData.requestsNuevos > this.MAX_VALUE_Y) {
		this.MAX_VALUE_Y = jsonData.requestsNuevos;
		this.buildScales();
		this.buildAxis();
	}else{
		//cada MAX_POLLS revisa el valor maximo, si es menor q 30
		//reset de scalas y axis
		if(this.lapCounter === 0) {
			if(d3.max(this.pollData,function(d){return d.requestsNuevos}) < 10) {
				this.MAX_VALUE_Y = 10;
				this.buildScales();
				this.buildAxis();
			}
		}
	}
	this.updateScore(jsonData.requestsNuevos);
	this.updateGraph();
}
ArgenmapLiveGraph.prototype.updateScore = function(v)
{
	var a = v || '0';
	var score = {value:parseInt(this.labelRequestsNuevos.text())};
	var label = this.labelRequestsNuevos;
	if(parseInt(a) > 0 || score.value != a) {
		var updateValue = function(){
			label.text(String(parseInt(score.value)));
		}
		TweenMax.killTweensOf(this.labelRequestsNuevos,true);
		TweenMax.set(this.labelRequestsNuevos,{opacity:0.6});
		TweenMax.from(this.labelRequestsNuevos,0.8,{opacity:1,ease:Linear.easeNone});
		TweenMax.to(score,0.75,{value:parseInt(a),onUpdate:updateValue});
	}
	this.labelRequestsNuevos.text(a);
}
ArgenmapLiveGraph.prototype.updateGraph = function()
{
	this.lineContainer.select('path').remove();
	this.lineContainer.append("path")
		.attr("d", this.lineFunction(this.pollData))
		.attr("stroke", this.lineColor)
		.attr("stroke-width", 1.5)
		.attr("fill", "none");
}
var liveGraph;
$(document).ready(function(){
	liveGraph = new ArgenmapLiveGraph({longPolling:true});
});
	</script>
<style>

body {
	font-family: 'Courier New',Courier, sans-serif;
	margin: auto;
	position: relative;
	font-size: 12px;
	background-color: black;
}
svg text {
    shape-rendering: crispEdges;
}
#graph {
	width: 400px;
	margin: 0 auto;
}

</style>
</head>
<body>
	<div id="graph"></div>
</body>
</html>
