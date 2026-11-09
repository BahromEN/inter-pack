/* ==========================================================================
   INTER PACK — CMS content overlay.
   Fetches admin-edited text (api/content.php) and replaces
   window.TRANSLATIONS[lang] with the merged (defaults + overrides) version
   before main.js applies translations to the DOM. If the PHP backend isn't
   reachable (e.g. the site is served as plain static files), this silently
   does nothing and the static text already baked into js/i18n.js is used.
   ========================================================================== */

(function(){
  "use strict";

  function withTimeout(promise, ms){
    return new Promise(function(resolve){
      var done = false;
      var timer = setTimeout(function(){
        if(!done){ done = true; resolve(); }
      }, ms);
      promise.then(function(){
        if(!done){ done = true; clearTimeout(timer); resolve(); }
      }).catch(function(){
        if(!done){ done = true; clearTimeout(timer); resolve(); }
      });
    });
  }

  function fetchLang(lang){
    return fetch("api/content.php?lang=" + lang, {cache:"no-store"})
      .then(function(res){
        if(!res.ok) throw new Error("bad status");
        return res.json();
      })
      .then(function(data){
        if(data && typeof data === "object" && window.TRANSLATIONS){
          window.TRANSLATIONS[lang] = data;
        }
      });
  }

  var all = Promise.all(["ru", "en", "uz"].map(function(lang){
    return fetchLang(lang).catch(function(){ /* keep static defaults for this language */ });
  }));

  window.ipContentReady = withTimeout(all, 2500);
})();
