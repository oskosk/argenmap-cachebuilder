<!DOCTYPE html>
<head>
        <title>robomap te escucha...</title>

        <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.js"></script>

        <script type="text/javascript">
$(document).ready(function(){
        var source = new EventSource('argenmap_live.php');

        source.addEventListener('message', function(e) {
                //console.log(e.data);
                $('#pusher').text(e.data);
        }, false);

        source.addEventListener('open', function(e) {
                // Connection was opened.
                //console.log("new connection");
        }, false);

        source.addEventListener('error', function(e) {
                //console.log("error (probable closed)");
                if (e.readyState == EventSource.CLOSED) {
                // Connection was closed.
                }
        }, false);

});
        </script>
</head>
<body>
        <div id="pusher" style="font-family:Arial,sans;font-size:68px;"></div>
</body>
</html>