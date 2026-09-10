(function(){
  "use strict";
  document.addEventListener("DOMContentLoaded", function(){
    var slider = document.querySelector(".hero-slider");
    if(!slider) return;
    var slides = Array.prototype.slice.call(slider.querySelectorAll(".hero-slide"));
    var dotsWrap = slider.querySelector(".hero-dots");
    var prevBtn = slider.querySelector(".hero-arrow.prev");
    var nextBtn = slider.querySelector(".hero-arrow.next");
    var idx = 0, timer;

    slides.forEach(function(_, i){
      var dot = document.createElement("button");
      dot.type = "button";
      dot.setAttribute("aria-label", "Slide " + (i + 1));
      dot.addEventListener("click", function(){ go(i); reset(); });
      dotsWrap.appendChild(dot);
    });
    var dots = Array.prototype.slice.call(dotsWrap.children);

    function render(){
      slides.forEach(function(s, i){ s.classList.toggle("is-active", i === idx); });
      dots.forEach(function(d, i){ d.classList.toggle("is-active", i === idx); });
    }
    function go(i){ idx = (i + slides.length) % slides.length; render(); }
    function next(){ go(idx + 1); }
    function prev(){ go(idx - 1); }
    function reset(){ clearInterval(timer); timer = setInterval(next, 5500); }

    if(prevBtn) prevBtn.addEventListener("click", function(){ prev(); reset(); });
    if(nextBtn) nextBtn.addEventListener("click", function(){ next(); reset(); });

    document.addEventListener("keydown", function(e){
      if(e.key === "ArrowLeft"){ prev(); reset(); }
      if(e.key === "ArrowRight"){ next(); reset(); }
    });

    render();
    reset();
  });
})();
