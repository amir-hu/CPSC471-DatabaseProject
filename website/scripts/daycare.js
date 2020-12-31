// JavaScript source code

// Create a request variable and assign a new XMLHttpRequest object to it.
var request = new XMLHttpRequest();

// Open a new connection, using the GET request on the URL endpoint
request.open('GET', 'http://localhost/CPSC471-DatabaseProject/api/daycare/get.php', true)

request.onload = function () {
    // Begin accessing JSON data here
    var data = JSON.parse(this.response)

    data.forEach((daycare) => {
        // Log each daycare's title
        console.log(daycare.DaycareName)

    })

}

function myFunction() {
    document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown menu if the user clicks outside of it
window.onclick = function (event) {
    if (!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
} 
// Send request
request.send()
/*const userAction = async () => {
    const response = await fetch('http://localhost/CPSC471-DatabaseProject/api/daycare/get.php');
    const myJson = await response.json(); //extract JSON from the http response
    // do something with myJson
}*/