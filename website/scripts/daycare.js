// JavaScript source code

// Create a request variable and assign a new XMLHttpRequest object to it.
var request = new XMLHttpRequest();

// Open a new connection, using the GET request on the URL endpoint
request.open('GET', 'http://localhost/CPSC471-DatabaseProject/api/daycare/read.php?DaycareName=Hakuna&DaycareAddress=matata', true)

request.onload = function () {
    // Begin accessing JSON data here
    var data = JSON.parse(this.response)

    data.forEach((daycare) => {
        // Log each daycare's title
        console.log(daycare.DaycareName)
    })
}

// Send request
request.send()