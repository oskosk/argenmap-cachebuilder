<!DOCTYPE html>
<head>
	<title>robomap te escucha...</title>

	<script src="http://cdnjs.cloudflare.com/ajax/libs/gsap/latest/TweenMax.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
	<script src="http://d3js.org/d3.v3.min.js"></script>
	<script src="http://livegraph.hp.af.cm"></script>
	<script type="text/javascript">
var liveGraph;
$(document).ready(function(){
	liveGraph = new ArgenmapLiveGraph({
		sourceUrl: 'live/poll',
		longPolling:true
	});
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
