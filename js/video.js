(function(){
  "use strict";
  document.addEventListener("DOMContentLoaded", function(){
    var playBtn = document.getElementById("playBtn");
    var video = document.getElementById("companyVideo");
    var poster = document.getElementById("videoPoster");
    if(!playBtn || !video) return;

    video.addEventListener("error", function(){
      playBtn.style.opacity = "0.45";
      playBtn.style.pointerEvents = "none";
    }, true);

    playBtn.addEventListener("click", function(){
      video.style.display = "block";
      video.muted = false;
      video.controls = true;
      video.play().catch(function(){});
      if(poster){
        poster.style.opacity = "0";
        poster.style.pointerEvents = "none";
      }
    });
  });
})();
