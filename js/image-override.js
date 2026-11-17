/* ==========================================================================
   INTER PACK — applies CMS-uploaded image overrides on top of the static
   defaults. Overrides live under data/uploads/ (outside git), so they
   survive a code deploy that resets the working tree back to the files
   committed in the repo. Falls back silently to the static defaults
   already in the HTML/CSS if api/images.php is unreachable.
   ========================================================================== */

(function(){
  "use strict";

  window.ipImageOverridesMap = {};

  function applyOverrides(map){
    map = map || {};
    window.ipImageOverridesMap = map;

    var bgTargets = {
      home_hero: document.querySelector(".home-hero"),
      achievements_bg: document.getElementById("achievements"),
      contacts_bg: document.getElementById("contacts")
    };
    Object.keys(bgTargets).forEach(function(key){
      var el = bgTargets[key];
      if(el && map[key]) el.style.backgroundImage = "url('" + map[key] + "')";
    });

    document.querySelectorAll("[data-img-slot]").forEach(function(img){
      var slot = img.getAttribute("data-img-slot");
      if(map[slot]) img.src = map[slot];
    });

    document.dispatchEvent(new CustomEvent("ip:imagesready"));
  }

  var p = fetch("api/images.php", {cache:"no-store"})
    .then(function(res){ return res.json(); })
    .catch(function(){ return {}; });

  window.ipImageOverrides = p;

  document.addEventListener("DOMContentLoaded", function(){
    p.then(applyOverrides);
  });
})();
