(function(){
  "use strict";
  document.addEventListener("DOMContentLoaded", function(){
    var form = document.getElementById("contactForm");
    if(!form) return;
    var successBox = document.querySelector(".form-success");
    var submitBtn = form.querySelector("button[type='submit']");
    var hideTimer;

    form.addEventListener("submit", function(e){
      e.preventDefault();
      if(submitBtn) submitBtn.disabled = true;

      fetch(form.action, {
        method: "POST",
        headers: {"Accept": "application/json"},
        body: new FormData(form)
      }).then(function(res){
        if(res.ok){ showSuccess(); }
        else { window.location.href = "mailto:inter.pack@mail.ru"; }
      }).catch(function(){
        window.location.href = "mailto:inter.pack@mail.ru";
      }).finally(function(){
        if(submitBtn) submitBtn.disabled = false;
      });
    });

    function showSuccess(){
      form.reset();
      if(!successBox) return;
      successBox.classList.add("is-visible");
      clearTimeout(hideTimer);
      hideTimer = setTimeout(function(){
        successBox.classList.remove("is-visible");
      }, 6000);
    }
  });
})();
