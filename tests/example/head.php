<html>
<head>
<title>Rserve-php client library</title>
<style><?php echo file_get_contents('rexp.css')?></style>
<style>
body {
 margin: 0;
}
#header {
 margin: 0;
 width: 100%;
 background-color: green;
 color: white;
 padding: 1em;
}

#main {
 margin: 1em;
}

#header h1 {
 margin: 0;
 padding: .2em;
 color: white;
}

h2 {
 border-bottom: 2px solid #DDD;
 margin-top: 1px;
}

.rcmd {
 margin-top: 1em;
 padding: .5em .2em;
 color: #00709F;
 font-weight: bold;
}

.rcmd:before {
 contents: "> ";
}

.vardump {
 border: 1px solid #EEE;
 padding: .3em;
}

#tabs li {
display: inline;
margin: .3em;
padding: .3em;
cursor: pointer;
}

#tabs li.sel {
 background-color: white;
 color: green;
}

</style>

<script src="http://code.jquery.com/jquery-1.6.4.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('.tab').hide();
    $('#tab_0').show();
    $('#tabs li').click(function() {
        var e=$(this);
        var id= e.attr('rel');
        $('#tabs li').removeClass('sel');
        e.addClass('sel');
        $('.tab').hide();
        $('#'+id).show();
    });

});
</script>
</head>
<body>
<div id="header">
<h1>Rserve-php client library</h1>
<ul id="tabs">
    <li rel="tab_0">Home</li>
    <li rel="tab_1">Chi2 example</li>
    <li rel="tab_2">Native parser</li>
    <li rel="tab_3">Wrapped native parser</li>
    <li rel="tab_4">Debug parser</li>
    <li rel="tab_5">REXP parser</li>
    <li rel="tab_6">Data.frame</li>
    <li rel="tab_7">Complex</li>
</ul>
</div>
<div id="main">
