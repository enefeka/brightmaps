$(document).ready(function(){
    $("#result").hide();
  $('#log-submit').click(function() {
     var email = $('#email').val();
     var password = $('#password').val();
         $.ajax({
           type: "POST",
           url: "http://localhost:8888/brightmaps/public/api/login",
           data: {
            email: email,
            password: password,
           },
           cache: false,
           
           success: function(response){

            if (response.code == 400) {
              $("#result").show();
              $("#result").html(response.message);

            }
            if (response.code == 401) {
              $("#result").show();
              $("#result").html(response.message);

            }
            if (response.data.user.role_id != 1) {
              $("#result").show();
              $("#result").html("No estás autorizado.");
            }
              console.log(response.data.token);
              console.log(response.data.user.role_id);
             if (response.data.user.role_id == 1) {
                 sessionStorage.setItem("token",response.data.token);
                 console.log("ok");
                  window.location.href='welcome.html'
             }

          },
           error: function(response){
              $("#result").show();
              $("#result").html(response.message);
           }

        });

   return false;
   });
});


 