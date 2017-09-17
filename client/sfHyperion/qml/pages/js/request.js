function postToBackend(command)
{
    var serverURI = "http://192.168.178.56:8888/index.php";
    var apiKey = "dbba40f2c93fd4dd17afd47709932535";

    var xmlHttp = new XMLHttpRequest();


    var data = {
        'apiKey': apiKey,
        'command': command
    };


    xmlHttp.onload = function(e) {
        console.log(xmlHttp);
        if (xmlHttp.readyState === 4 && (xmlHttp.status === 200 || xmlHttp.status === 201)) {

            console.log(xmlHttp.responseText);
        }
    };


    if (typeof data === "object") {
        var dataObject = data;
        data           = "";
        for (var key in dataObject) {
            data = data + "&" + key + "=" + encodeURIComponent(dataObject[key]);
        }
    }

    console.log(serverURI);
    console.log(data);

    xmlHttp.open("POST", serverURI, true);
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    if (typeof headers === "object") {
        for (var i in headers) {
            xmlHttp.setRequestHeader(i, headers[i]);
        }
    }
    xmlHttp.send(data);
    return false;
}
