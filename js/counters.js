(function(){
  "use strict";
  document.addEventListener("DOMContentLoaded", function(){
    var nums = document.querySelectorAll(".stat-num .num");
    if(!nums.length) return;

    function animate(el){
      var target = parseFloat(el.getAttribute("data-target")) || 0;
      var dur = 1400, start = null;
      function step(ts){
        if(!start) start = ts;
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased);
        if(p < 1) requestAnimationFrame(step);
        else el.textContent = target;
      }
      requestAnimationFrame(step);
    }

    if("IntersectionObserver" in window){
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
          if(e.isIntersecting){ animate(e.target); io.unobserve(e.target); }
        });
      }, {threshold:0.4});
      nums.forEach(function(n){ io.observe(n); });
    } else {
      nums.forEach(animate);
    }
  });
})();
