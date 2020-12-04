(function($) {
  $(document).ready(function(){

    $('#controle-acesso').submit(function(){
      var dados = $( this ).serialize();
      var email = $("#email").val();
      $.ajax({
        type: "POST",
        url: "valida_email",
        data: dados,
        success: function( data ) {
          if (data.retorno == false) {
            window.location.href = '/form/user?email='+email;
          }else{
            window.location.href = '/node/add';
          }
        }
      });
      return false;
    });

    $('#webform-submission-user-add-form').submit(function(){
      var dados = $( this ).serialize();
        $.ajax({
        type: "POST",
        url: "/configura_sessao",
        data: dados,
        success: function( data ) {
          if (data.retorno != null) {
            window.location.href = '/node/add';
          }
        }
      });
      return false;
    });

  });
}(jQuery));
